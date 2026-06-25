<?php

namespace App\Services;

/**
 * Service de calcul géographique.
 *
 * Fournit les outils purement mathématiques utilisés pour le matching
 * des trajets : distance Haversine, point le plus proche sur un tracé,
 * parsing d'un tracé GeoJSON et calcul de sa durée totale.
 *
 * Ce service ne fait aucun accès base de données : il opère uniquement
 * sur des structures déjà chargées par le service appelant. Pour les
 * opérations qui nécessitent de charger un Track ou un Journey,
 * passer par JourneyService.
 */
class GeoService
{
    /**
     * Vérifie si un tracé (déjà parsé en points) passe à proximité
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
     * Parse un tracé GeoJSON et retourne la liste de ses points normalisés.
     *
     * Point d'entrée unique pour transformer un GeoJSON brut en tableau
     * de points exploitables par les autres méthodes du service.
     *
     * @param  string $geoJson Tracé au format GeoJSON
     * @return array<int, array{lat:float, lon:float}> Points du tracé ;
     *               tableau vide si le GeoJSON est invalide ou sans coordonnées
     */
    public function parseTrackPointsFromGeoJson(string $geoJson): array
    {
        $data        = json_decode($geoJson, true);
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
     * Calcule la durée totale d'un trajet à partir de son GeoJSON,
     * en sommant la durée de chacun de ses segments.
     *
     * @param  string $geoJson Tracé au format GeoJSON
     * @return int Durée totale du trajet, en secondes (0 si GeoJSON invalide)
     */
    public function calculateTrackDuration(string $geoJson): int
    {
        $data     = json_decode($geoJson, true);
        $segments = $data['features'][0]['properties']['segments'] ?? [];

        $duration = 0;
        foreach ($segments as $segment) {
            $duration += $segment['duration'] ?? 0;
        }

        return (int) $duration;
    }
}
