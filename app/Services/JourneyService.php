<?php

namespace App\Services;

use App\Libraries\MailerExample;
use App\Models\JourneyModel;
use App\Models\JourneyRequestModel;

/**
 * Service métier lié aux trajets.
 *
 * Regroupe la logique de haut niveau qui ne relève pas directement
 * d'un controller : détection des demandes compatibles avec un nouveau
 * trajet et envoi des notifications par mail aux demandeurs.
 */
class JourneyService
{
    protected JourneyModel $journeyModel;
    protected JourneyRequestModel $journeyRequestModel;
    protected GeoService $geoService;

    public function __construct()
    {
        $this->journeyModel        = new JourneyModel();
        $this->journeyRequestModel = new JourneyRequestModel();
        $this->geoService          = new GeoService();
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

        // ====== Récupération de toutes les demandes en attente
        $requests = $this->journeyRequestModel
            ->select('journey_request.*,
                      u.email          as requester_email,
                      u.firstname      as requester_firstname,
                      loc_start.latitude  as start_lat,
                      loc_start.longitude as start_lng,
                      loc_end.latitude    as end_lat,
                      loc_end.longitude   as end_lng,
                      city_start.name  as city_start_name,
                      city_end.name    as city_end_name')
            ->join('user u',             'u.id = journey_request.user_id')
            ->join('location loc_start', 'loc_start.id = journey_request.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey_request.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey_request.user_id !=', $journey['user_id'])
            ->where('journey_request.start_datetime >=', date('Y-m-d H:i:s'))
            ->findAll();

        if (empty($requests)) return;

        // ====== Parse des points du tracé GeoJSON
        $data        = json_decode($geoJsonTrack, true);
        $coordinates = $data['features'][0]['geometry']['coordinates'] ?? [];

        $points = [];
        foreach ($coordinates as $coord) {
            $points[] = ['lat' => (float) $coord[1], 'lon' => (float) $coord[0]];
        }

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
}