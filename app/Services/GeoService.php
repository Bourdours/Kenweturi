<?php

namespace App\Services;

use App\Models\JourneyModel;
use App\Models\TrackModel;

use DateTimeImmutable;

/**
 * Service de géolocalisation.
 *
 * Fournit les outils de calcul géographique utilisés pour le matching
 * des trajets : distance Haversine, point le plus proche sur un tracé,
 * et filtrage d'une liste de trajets par proximité géographique.
 */
class GeoService
{
    protected TrackModel $trackModel;
    protected JourneyModel $journeyModel;

    public function __construct()
    {
        $this->trackModel = new TrackModel();
        $this->journeyModel = new JourneyModel();
    }

    /**
     * Vérifie si un tracé GeoJSON (déjà parsé en points) passe à proximité
     * d'un départ ET d'une arrivée donnés, dans le bon ordre.
     *
     * @param  array[] $points        Points du tracé ['lat' => float, 'lon' => float]
     * @param  array   $start         Point de départ ['lat' => float, 'lon' => float]
     * @param  array   $end           Point d'arrivée ['lat' => float, 'lon' => float]
     * @param  float   $maxDistanceKm Rayon de tolérance en km (défaut : 10)
     * @return bool    true si le tracé passe à proximité du départ ET de l'arrivée
     */
    public function matchesTrackPoints(array $points, array $start, array $end, float $maxDistanceKm = 10): bool
    {
        if (empty($points)) return false;

        $startIndex      = $this->findClosestPointIndex($points, $start);
        $distanceToStart = $this->calculateDistance($start, $points[$startIndex]);

        if ($distanceToStart > $maxDistanceKm) return false;

        $endIndex      = $this->findClosestPointIndex($points, $end, $startIndex);
        $distanceToEnd = $this->calculateDistance($end, $points[$endIndex]);

        return $distanceToEnd <= $maxDistanceKm;
    }

    /**
     * Récupère et normalise les points du tracé d'un trajet.
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

        $data        = json_decode($track['geojson'], true);
        $coordinates = $data['features'][0]['geometry']['coordinates'] ?? [];

        $points = [];
        foreach ($coordinates as $coordinate) {
            $points[] = ['lat' => (float) $coordinate[1], 'lon' => (float) $coordinate[0]];
        }

        return $points;
    }

    /**
     * Renvoie l'indice du point du tracé le plus proche d'une cible.
     *
     * @param  array<int, array{lat:float, lon:float}> $points    Points du tracé
     * @param  array{lat:float, lon:float}             $target    Point cible
     * @param  int                                     $fromIndex Indice de départ de la recherche
     * @return int|null Indice du point le plus proche, ou null si la plage est vide
     */
    public function findClosestPointIndex(array $points, array $target, int $fromIndex = 0): ?int
    {
        $closestIndex = null;
        $minDistance  = INF;
        $count        = count($points);

        for ($i = $fromIndex; $i < $count; $i++) {
            $distance = $this->calculateDistance($target, $points[$i]);
            if ($distance < $minDistance) {
                $minDistance  = $distance;
                $closestIndex = $i;
            }
        }

        return $closestIndex;
    }

    /**
     * Calcule la distance en kilomètres entre deux points géographiques
     * via la formule de Haversine (Terre supposée sphérique, rayon 6371 km).
     *
     * @param  array{lat:float, lon:float} $coord1 Premier point
     * @param  array{lat:float, lon:float} $coord2 Second point
     * @return float Distance entre les deux points, en kilomètres
     */
    public function calculateDistance(array $coord1, array $coord2): float
    {
        $earthRadiusKm = 6371;

        $lat1 = deg2rad($coord1['lat']);
        $lon1 = deg2rad($coord1['lon']);
        $lat2 = deg2rad($coord2['lat']);
        $lon2 = deg2rad($coord2['lon']);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2
           + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Calcule l'heure d'arrivée d'un trajet à partir de son tracé et de son heure de départ.
     *
     * @param  int $journeyId Identifiant du trajet
     * @return DateTimeImmutable Heure d'arrivée estimée
     */
    public function getArrivaleDateTime(int $journeyId): DateTimeImmutable {
    
        $trackId = $this->journeyModel->select(['track_id'])->where([
            'id'=>$journeyId,
        ])->first();

        $track = $this->trackModel->where([
            'id'=>$trackId,
        ])->first();

        $trackDuration = $this->calculateTrackDuration(json_decode($track['geojson']));
        $journeyStartDateTime = new DateTimeImmutable($this->journeyModel->select(['start_datetime'])->where(['id'=>$journeyId])->first()['start_datetime']);

        return $journeyStartDateTime->modify('+'.$trackDuration.' seconds');
    }


    /**
     * Calcule la durée totale d'un trajet à partir de son GeoJSON,
     * en sommant la durée de chacun de ses segments.
     *
     * @param  object|null $track Trajet au format GeoJSON décodé en objet
     * @return int Durée totale du trajet, en secondes
     */
    private function calculateTrackDuration(?object $track): int {

        $duration=0;
        $segments = $track->features[0]->properties->segments ?? null;

        foreach($segments as $segment){
            $duration += $segment->duration;
        }

        return (int) $duration;
    }
}
