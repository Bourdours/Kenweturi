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

        $this->routingService       = new RoutingService();
        $this->geocodingService     = new GeocodingService();
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
     * Recherche les trajets correspondant aux filtres fournis.
     *
     * Charge tous les candidats (filtres non géographiques) via le model,
     * enrichit chacun du nombre de demandes en attente, puis applique le
     * filtrage géographique (proximité départ ET arrivée, dans le bon ordre).
     *
     * Le résultat n'est PAS paginé : la pagination reste une préoccupation
     * de présentation gérée par le controller.
     *
     * @param  array $filters Filtres normalisés (voir JourneyController::getShowAllFilter)
     * @return array[]        Trajets correspondants (non paginés)
     */


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

        $mailer = new MailerExample();

        // ====== Matching pour chaque demande
        foreach ($requests as $request) {

            // --- Filtre géographique
            $start = ['lat' => (float) $request['start_lat'], 'lon' => (float) $request['start_lng']];
            $end   = ['lat' => (float) $request['end_lat'],   'lon' => (float) $request['end_lng']];

            if (!$this->geoService->matchesTrackPoints($points, $start, $end)) {
                continue;
            }

            // --- Filtre temporel (±30 min)
            if (!empty($request['start_datetime'])) {
                $diff = abs(strtotime($journey['start_datetime']) - strtotime($request['start_datetime']));
                if ($diff > 1800) continue;
            }

            // --- Envoi du mail
            $date = date('d/m/Y', strtotime($journey['start_datetime']))
                  . ' à ' . date('H:i', strtotime($journey['start_datetime']));

            $mailer->sendHtml(
                $request['requester_email'],
                'Un trajet correspond à votre demande !',
                view('Emails/journeyRequestMatch', [
                    'firstname'  => $request['requester_firstname'],
                    'cityStart'  => $request['city_start_name'],
                    'cityEnd'    => $request['city_end_name'],
                    'date'       => $date,
                    'journeyUrl' => site_url('journeys/' . $journeyId),
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
}