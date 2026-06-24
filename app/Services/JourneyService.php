<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Models\JourneyModel;
use App\Models\JourneyRequestModel;
use App\Models\TrackModel;
use App\Models\LocationModel;
use App\Models\StageModel;
use App\Models\CityModel;
use App\Models\CarModel;
use App\Models\BookingModel;
use App\Models\NotificationPrefModel;
use App\Models\UserModel;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

use App\Services\RoutingService;
use App\Services\GeocodingService;

use DateTimeImmutable;

/**
 * Service métier lié aux trajets.
 *
 * Regroupe la logique de haut niveau qui ne relève pas directement
 * d'un controller : récupération des données API, persistance d'un
 * trajet complet en transaction, détection des demandes compatibles
 * avec un nouveau trajet et envoi des notifications.
 *
 * Ce service est également responsable des accès BDD liés aux tracés
 * (Track) et aux trajets (Journey) qui combinent chargement et calcul
 * géographique : il délègue ensuite les calculs purs à GeoService.
 */
class JourneyService
{
    protected JourneyModel $journeyModel;
    protected JourneyRequestModel $journeyRequestModel;
    protected TrackModel $trackModel;
    protected LocationModel $locationModel;
    protected StageModel $stageModel;
    protected CityModel $cityModel;
    protected CarModel $carModel;
    protected BookingModel $bookingModel;
    protected GeoService $geoService;

    protected RoutingService $routingService;
    protected GeocodingService $geocodingService;
    protected NotificationPrefModel $notifPrefModel;

    protected MailerExample $mailer;

    private const ABSOLUTE_MAX_SEATS = 9;

    public function __construct()
    {
        $this->journeyModel         = new JourneyModel();
        $this->journeyRequestModel  = new JourneyRequestModel();
        $this->geoService           = new GeoService();
        $this->trackModel           = new TrackModel();
        $this->locationModel        = new LocationModel();
        $this->stageModel           = new StageModel();
        $this->cityModel            = new CityModel();
        $this->carModel             = new CarModel();
        $this->bookingModel         = new BookingModel();
        $this->notifPrefModel       = new NotificationPrefModel();

        $this->routingService       = new RoutingService();
        $this->geocodingService     = new GeocodingService();

        $this->mailer               = new MailerExample();
    }

    /**
     * Calcule l'heure d'arrivée d'un trajet à partir de son tracé et de son heure de départ.
     *
     * Charge le trajet et son tracé en BDD, puis délègue à GeoService le calcul
     * pur de la durée à partir du GeoJSON.
     *
     * @param  int $journeyId Identifiant du trajet
     * @return DateTimeImmutable Heure d'arrivée estimée
     */
    public function getArrivalDateTime(int $journeyId): DateTimeImmutable
    {
        $journey = $this->journeyModel->find($journeyId);
        $track   = $this->trackModel->find($journey['track_id']);

        $duration             = $this->geoService->calculateTrackDuration($track['geojson']);
        $journeyStartDateTime = new DateTimeImmutable($journey['start_datetime']);

        return $journeyStartDateTime->modify('+' . $duration . ' seconds');
    }

    /**
     * Assemble l'ensemble des données nécessaires à l'affichage du détail d'un trajet.
     *
     * Charge le trajet, calcule sa date d'arrivée, récupère ses étapes
     * intermédiaires, ses passagers, le nombre de demandes en attente et la
     * réservation éventuelle de l'utilisateur courant (avec les drapeaux
     * isBooked / isPending dérivés du statut de cette réservation).
     *
     * @param  int $journeyId Identifiant du trajet
     * @param  int $userId    Identifiant de l'utilisateur courant
     * @return array|null     Données du trajet prêtes pour la vue, ou null si
     *                        le trajet n'existe pas
     */
    public function getJourneyDetails(int $journeyId, int $userId): ?array
    {
        $journey = $this->journeyModel->findWithDetails($journeyId);
        if (!$journey) {
            return null;
        }

        $journey['end_datetime'] = $this->getArrivalDateTime($journey['id'])
            ->format('Y-m-d H:i:s');

        $userBooking = $this->bookingModel->findUserBooking($journeyId, $userId);

        return [
            'journey'         => $journey,
            'stages'          => $this->stageModel->findByJourney($journeyId),
            'remainingSeats'  => $this->bookingModel->countRemainingSeats($journeyId, (int) $journey['seats']),
            'passengers'      => $this->bookingModel->findPassengersByJourney($journeyId),
            'pendingBookings' => $this->bookingModel->countPendingBookings($journeyId),
            'userBooking'     => $userBooking,
            'isBooked'        => $userBooking !== null && $userBooking['status'] === 'accepted',
            'isPending'       => $userBooking !== null && $userBooking['status'] === 'pending',
        ];
    }


    /**
     * Recherche les demandes de trajet compatibles avec un trajet nouvellement créé
     * et envoie un mail de notification à chaque demandeur concerné.
     *
     * Une demande est considérée compatible si :
     *  - le tracé du trajet passe à moins de 10 km de son départ ET de son arrivée
     *    (dans le bon ordre départ → arrivée)
     *  - la date souhaitée est à ±30 minutes de l'heure de départ du trajet
     *
     * @param  int    $journeyId    Identifiant du trajet nouvellement créé
     * @param  string $geoJsonTrack Tracé GeoJSON du trajet
     * @return void
     */
    public function notifyMatchingRequests(int $journeyId, string $geoJsonTrack): void
    {
        // ====== Récupération du trajet créé
        $journey = $this->journeyModel->findWithDetails($journeyId);
        if (!$journey) return;

        // ====== Récupération de toutes les demandes en attente (hors conducteur)
        $requests = $this->journeyRequestModel->findPendingExcludingUser((int) $journey['user_id']);

        if (empty($requests)) return;

        // ====== Parse des points du tracé GeoJSON (factorisé dans GeoService)
        $points = $this->geoService->parseTrackPointsFromGeoJson($geoJsonTrack);
        if (empty($points)) return;

        // ====== Matching pour chaque demande
        foreach ($requests as $request) {

            // --- Filtre géographique
            $start = ['lat' => (float) $request['start_lat'], 'lon' => (float) $request['start_lng']];
            $end   = ['lat' => (float) $request['end_lat'],   'lon' => (float) $request['end_lng']];

            if (!$this->geoService->matchesTrackPoints($points, $start, $end, (float) ($request['radius_km'] ?? 10))) {
                continue;
            }

            // --- Filtre temporel (±30 min)
            if (!empty($request['start_datetime'])) {
                $diff = abs(strtotime($journey['start_datetime']) - strtotime($request['start_datetime']));
                if ($diff > 1800) continue;
            }

            // --- Envoi du mail
            $requesterId = (int) $request['user_id'];
            if (!$this->notifPrefModel->wantsNotif($requesterId, 'journey_request')) continue;

            $date = date('d/m/Y', strtotime($journey['start_datetime']))
                  . ' à ' . date('H:i', strtotime($journey['start_datetime']));

            $this->mailer->sendHtml(
                $request['requester_email'],
                'Un trajet correspond à votre demande !',
                view('Emails/journeyRequestMatch', [
                    'firstname'      => $request['requester_firstname'],
                    'cityStart'      => $request['city_start_name'],
                    'cityEnd'        => $request['city_end_name'],
                    'date'           => $date,
                    'journeyUrl'     => site_url('journeys/' . $journeyId),
                    'prefLabel'      => NotificationPrefModel::PREFS['journey_request'],
                    'unsubscribeUrl' => site_url('unsubscribe?uid=' . $requesterId . '&pref=journey_request&token=' . UserModel::unsubscribeToken($requesterId, 'journey_request')),
                    'preferencesUrl' => site_url('profile/notifications'),
                ])
            );
        }
    }

    /**
     * Recherche les trajets existants compatibles avec une demande de trajet
     * (typiquement après modification) et envoie un email au demandeur pour chaque match.
     *
     * Symétrique de notifyMatchingRequests : ici on fixe la demande et on itère
     * sur les trajets actifs pour trouver ceux dont le tracé passe à moins de 10 km
     * du départ et de l'arrivée de la demande, dans la bonne direction, et dans
     * un créneau de ±30 minutes.
     *
     * @param  int $requestId     Identifiant de la demande mise à jour
     * @param  int $requestUserId Identifiant du demandeur (exclu des trajets à matcher)
     * @return void
     */
    public function notifyMatchingJourneys(int $requestId, int $requestUserId): void
    {
        $request = $this->journeyRequestModel->findWithCoordinatesById($requestId);
        if (!$request) return;

        $journeys = array_filter(
            $this->journeyModel->findAllWithFilters([]),
            fn($j) => (int) $j['user_id'] !== $requestUserId
        );

        if (empty($journeys)) return;

        $start = ['lat' => (float) $request['start_lat'], 'lon' => (float) $request['start_lng']];
        $end   = ['lat' => (float) $request['end_lat'],   'lon' => (float) $request['end_lng']];

        foreach ($journeys as $journey) {
            if (empty($journey['track_geojson'])) continue;

            $points = $this->geoService->parseTrackPointsFromGeoJson($journey['track_geojson']);
            if (empty($points)) continue;

            if (!$this->geoService->matchesTrackPoints($points, $start, $end, (float) ($request['radius_km'] ?? 10))) continue;

            if (!empty($request['start_datetime'])) {
                $diff = abs(strtotime($journey['start_datetime']) - strtotime($request['start_datetime']));
                if ($diff > 1800) continue;
            }

            $requesterId = (int) $request['user_id'];
            if (!$this->notifPrefModel->wantsNotif($requesterId, 'journey_request')) continue;

            $date = date('d/m/Y', strtotime($journey['start_datetime']))
                  . ' à ' . date('H:i', strtotime($journey['start_datetime']));

            $this->mailer->sendHtml(
                $request['requester_email'],
                'Un trajet correspond à votre demande !',
                view('Emails/journeyRequestMatch', [
                    'firstname'      => $request['requester_firstname'],
                    'cityStart'      => $request['city_start_name'],
                    'cityEnd'        => $request['city_end_name'],
                    'date'           => $date,
                    'journeyUrl'     => site_url('journeys/' . $journey['id']),
                    'prefLabel'      => NotificationPrefModel::PREFS['journey_request'],
                    'unsubscribeUrl' => site_url('unsubscribe?uid=' . $requesterId . '&pref=journey_request&token=' . UserModel::unsubscribeToken($requesterId, 'journey_request')),
                    'preferencesUrl' => site_url('profile/notifications'),
                ])
            );
        }
    }

    public function getMaxSeatsForCar(int $carId, int $userId): int
    {
        if ($carId <= 0) return self::ABSOLUTE_MAX_SEATS;
        $car = $this->carModel->findOwnedByUser($carId, $userId);
        return $car ? (int) $car['seats'] - 1 : self::ABSOLUTE_MAX_SEATS;
    }

    /**
     * Retourne le trajet existant de l'utilisateur sur la même demi-journée
     * (matin : 00h–12h, après-midi : 12h–24h) que la date/heure fournies.
     *
     * Sert à prévenir la création de doublons par un même conducteur sur
     * une plage horaire incompatible.
     *
     * @param  int    $userId    Identifiant du conducteur
     * @param  string $startDate Date de départ au format Y-m-d
     * @param  string $startTime Heure de départ au format H:i
     * @return array|null Trajet existant sur la demi-journée, ou null si aucun
     */
    private function findExistingJourneyOnHalfDay(int $userId, string $startDate, string $startTime): ?array
    {
        $journeyStartDate = new DateTimeImmutable($startDate);
        $journeyStartTime = new DateTimeImmutable($startTime);

        $startHour        = (int) $journeyStartTime->format('H') < 12 ? 0 : 12;
        $dayStartDateTime = $journeyStartDate->setTime($startHour, 0, 0);
        $dayEndDateTime   = $dayStartDateTime->modify('+12 hours');

        return $this->journeyModel->findByUserInTimeRange(
            $userId,
            $dayStartDateTime->format('Y-m-d H:i:s'),
            $dayEndDateTime->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Notifie par email les passagers acceptés de l'annulation d'un trajet.
     *
     * Récupère toutes les réservations acceptées du trajet et envoie
     * un email à chaque passager concerné.
     *
     * @param  array $journey Données du trajet annulé
     * @return void
     */
    public function notifyCancelledJourney(array $journey): void
    {
        $bookings = $this->bookingModel
            ->select('booking.user_id as passenger_id, user.email, user.firstname')
            ->join('user', 'user.id = booking.user_id')
            ->where('booking.journey_id', $journey['id'])
            ->where('booking.status', 'accepted')
            ->findAll();

        $date = date('d/m/Y', strtotime($journey['start_datetime']))
            . ' à ' . date('H:i', strtotime($journey['start_datetime']));

        foreach ($bookings as $booking) {
            $passengerId = (int) $booking['passenger_id'];
            if (!$this->notifPrefModel->wantsNotif($passengerId, 'journey_cancelled')) continue;

            $this->mailer->sendHtml(
                $booking['email'],
                'Votre trajet a été annulé',
                view('Emails/journeyCancelled', [
                    'firstname'      => $booking['firstname'],
                    'cityStart'      => $journey['city_start_name'],
                    'cityEnd'        => $journey['city_end_name'],
                    'date'           => $date,
                    'prefLabel'      => NotificationPrefModel::PREFS['journey_cancelled'],
                    'unsubscribeUrl' => site_url('unsubscribe?uid=' . $passengerId . '&pref=journey_cancelled&token=' . UserModel::unsubscribeToken($passengerId, 'journey_cancelled')),
                    'preferencesUrl' => site_url('profile/notifications'),
                ])
            );
        }
    }

    public function countRemainingSeats(int $journeyId){

        $nbOfSeats = (int) $this->journeyModel->getNumberOfSeats($journeyId);
        $nbOfAcceptedBook = (int) $this->bookingModel->countByStatus("accepted",$journeyId);

        return $nbOfSeats - $nbOfAcceptedBook;

    }

    /**
     * Trajets actifs (non annulés) d'un conducteur, enrichis des villes de
     * départ/arrivée (nécessaires pour notifier les passagers à l'annulation).
     *
     * @return array<int,array>
     */
    public function getActiveJourneysWithCities(int $userId): array
    {
        return $this->journeyModel->findActiveByUserWithCities($userId);
    }

    /**
     * Prévient (best-effort) les passagers acceptés que des trajets sont annulés.
     * Un échec d'envoi est journalisé mais n'interrompt pas le traitement.
     *
     * @param array<int,array> $journeys
     */
    public function notifyJourneysCancelled(array $journeys): void
    {
        foreach ($journeys as $journey) {
            try {
                $this->notifyCancelledJourney($journey);
            } catch (\Throwable $e) {
                log_message('error', 'Journey cancellation mail failed (journey {id}): {type}', [
                    'id'      => $journey['id'] ?? null,
                    'type' => get_class($e),
                ]);
            }
        }
    }

    /**
     * Annule tous les trajets actifs d'un conducteur (soft cancel via canceled_at).
     */
    public function cancelAllByDriver(int $userId): void
    {
        $this->journeyModel->cancelAllByUser($userId);
    }

    /**
     * Données de notification (passagers acceptés + infos trajet) pour une liste
     * de trajets — à CAPTURER avant tout rejet/annulation.
     *
     * @param int[] $journeyIds
     * @return array<int,array>
     */
    public function getCancellationNotifications(array $journeyIds): array
    {
        return $this->bookingModel->findAcceptedNotificationsForJourneys($journeyIds);
    }

    /**
     * Prévient (best-effort) les passagers de l'annulation, à partir de payloads
     * pré-capturés. Ne refait aucune requête : sûr à appeler APRÈS commit.
     *
     * @param array<int,array> $notifications
     */
    public function notifyPassengersJourneyCancelled(array $notifications): void
    {
        foreach ($notifications as $n) {
            try {
                $passengerId = (int) $n['user_id'];
                if (!$this->notifPrefModel->wantsNotif($passengerId, 'journey_cancelled')) {
                    continue;
                }

                $date = date('d/m/Y', strtotime($n['start_datetime']))
                    . ' à ' . date('H:i', strtotime($n['start_datetime']));

                $this->mailer->sendHtml(
                    $n['email'],
                    'Votre trajet a été annulé',
                    view('Emails/journeyCancelled', [
                        'firstname'      => $n['firstname'],
                        'cityStart'      => $n['city_start_name'],
                        'cityEnd'        => $n['city_end_name'],
                        'date'           => $date,
                        'prefLabel'      => NotificationPrefModel::PREFS['journey_cancelled'],
                        'unsubscribeUrl' => site_url('unsubscribe?uid=' . $passengerId . '&pref=journey_cancelled&token=' . UserModel::unsubscribeToken($passengerId, 'journey_cancelled')),
                        'preferencesUrl' => site_url('profile/notifications'),
                    ])
                );
            } catch (\Throwable $e) {
                log_message('error', 'Journey cancellation mail failed: {type}', [
                    'type'    => get_class($e),
                ]);
            }
        }
    }
}