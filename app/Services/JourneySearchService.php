<?php

namespace App\Services;

use App\Models\TrackModel;
use App\Models\JourneyModel;
use App\Models\BookingModel;

use App\Services\GeoService;

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
class JourneySearchService
{
    protected TrackModel $trackModel;
    protected JourneyModel $journeyModel;
    protected BookingModel $bookingModel;

    protected GeoService $geoService;

    public function __construct()
    {
        $this->trackModel           = new TrackModel();
        $this->journeyModel         = new JourneyModel();
        $this->bookingModel         = new BookingModel();

        $this->geoService           = new GeoService();
    }

        public function searchJourneys(array $filters): array
    {
        $candidates = $this->journeyModel->findAllWithFilters($filters);
        $this->attachPendingBookingsCount($candidates);

        $start = ($filters['latStart'] !== null && $filters['lngStart'] !== null)
            ? ['lat' => $filters['latStart'], 'lon' => $filters['lngStart']]
            : null;

        $end = ($filters['latEnd'] !== null && $filters['lngEnd'] !== null)
            ? ['lat' => $filters['latEnd'], 'lon' => $filters['lngEnd']]
            : null;

        return $this->findMatchingJourneys($candidates, $start, $end);
    }

    /**
     * Ajoute à chaque candidat le nombre de demandes de réservation en attente.
     *
     * Une seule requête est effectuée pour l'ensemble des trajets
     * (countPendingByJourneys), puis le résultat est réparti par trajet.
     *
     * @param  array[] $candidates Trajets candidats (modifiés par référence)
     * @return void
     */
    private function attachPendingBookingsCount(array &$candidates): void
    {
        $journeyIds = array_column($candidates, 'id');
        if (empty($journeyIds)) {
            return;
        }

        $pendingByJourney = $this->bookingModel->countPendingByJourneys($journeyIds);

        foreach ($candidates as &$candidate) {
            $candidate['pending_bookings'] = $pendingByJourney[$candidate['id']] ?? 0;
        }
        unset($candidate);
    }

    /**
     * Filtre une liste de trajets candidats en ne gardant que ceux dont le tracé
     * passe à proximité du départ ET de l'arrivée recherchés.
     *
     * Pour chaque trajet : on récupère les points du tracé GeoJSON, on cherche le
     * point le plus proche du départ ; s'il est dans le rayon, on cherche le point
     * le plus proche de l'arrivée PARMI LES POINTS SUIVANTS (pour garantir que le
     * trajet va bien dans le sens départ → arrivée).
     *
     * Filtres partiels gérés : seul le départ, seule l'arrivée, ou aucun des deux
     * (auquel cas tous les trajets sont retournés).
     *
     * @param  array[]    $journeys      Trajets candidats (doivent contenir 'track_id')
     * @param  array|null $start         Point de départ ['lat' => float, 'lon' => float] ou null
     * @param  array|null $end           Point d'arrivée ['lat' => float, 'lon' => float] ou null
     * @param  float      $maxDistanceKm Rayon de tolérance en km (défaut : 10)
     * @return array[]   Sous-ensemble des trajets correspondants
     */
    public function findMatchingJourneys(array $journeys, ?array $start, ?array $end, float $maxDistanceKm = 10): array
    {
        if ($start === null && $end === null) {
            return $journeys;
        }

        $matchingJourneys = [];

        foreach ($journeys as $journey) {

            $points = $this->getTrackPoints((int) $journey['track_id']);

            if (empty($points)) {
                continue;
            }

            $startIndex = 0;

            // --- Contrainte sur le départ
            if ($start !== null) {
                $startIndex      = $this->geoService->findClosestPointIndex($points, $start);
                $distanceToStart = $this->geoService->calculateDistance($start, $points[$startIndex]);

                if ($distanceToStart > $maxDistanceKm) {
                    continue;
                }
            }

            // --- Contrainte sur l'arrivée (après le départ)
            if ($end !== null) {
                $endIndex      = $this->geoService->findClosestPointIndex($points, $end, $startIndex);
                $distanceToEnd = $this->geoService->calculateDistance($end, $points[$endIndex]);

                if ($distanceToEnd > $maxDistanceKm) {
                    continue;
                }
            }

            $matchingJourneys[] = $journey;
        }

        return $matchingJourneys;
    }
        

    /**
     * Récupère et normalise les points du tracé d'un trajet à partir de son ID.
     *
     * Combine l'accès au TrackModel et le parsing GeoJSON délégué à GeoService.
     *
     * @param  int $trackId Identifiant du tracé
     * @return array<int, array{lat:float, lon:float}> Points du tracé ;
     *               tableau vide si le tracé est absent ou invalide
     */
    public function getTrackPoints(int $trackId): array
    {
        $track = $this->trackModel->find($trackId);
        if (empty($track['geojson'])) {
            return [];
        }

        return $this->geoService->parseTrackPointsFromGeoJson($track['geojson']);
    }

}