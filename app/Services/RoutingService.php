<?php

namespace App\Services;

/**
 * Service de calcul d'itinéraire via l'API Géoplateforme (IGN).
 *
 * Interroge le service de calcul d'itinéraire de la Géoplateforme
 * (https://data.geopf.fr/navigation/itineraire) puis convertit la réponse
 * au format GeoJSON "FeatureCollection" historique (compatible
 * OpenRouteService), afin que le reste de l'application (GeoService,
 * CreateJourneyService, vues, JS) et les tracés déjà stockés en base
 * restent inchangés.
 *
 * Format pivot produit :
 * {
 *   "type": "FeatureCollection",
 *   "features": [{
 *     "type": "Feature",
 *     "geometry": { "type": "LineString", "coordinates": [[lon, lat], ...] },
 *     "properties": {
 *       "segments": [{ "duration": <sec>, "distance": <m> }, ...],
 *       "summary":  { "duration": <sec>, "distance": <m> }
 *     }
 *   }]
 * }
 *
 * Chaque "portion" renvoyée par la Géoplateforme (tronçon entre deux points
 * de passage consécutifs) devient un "segment" : un trajet sans étape produit
 * 1 segment, un trajet avec N étapes produit N+1 segments, comme avec ORS.
 *
 * Sécurité / robustesse :
 *  - URL de l'API constante : aucune donnée externe n'entre dans l'URL (pas de SSRF)
 *  - coordonnées validées (numériques, plages lat/lon) et formatées de manière
 *    indépendante de la locale avant envoi
 *  - vérification TLS explicite (peer + host)
 *  - timeouts de connexion et de requête distincts
 *  - réponse validée structurellement puis RE-SÉRIALISÉE par nos soins :
 *    seuls les champs attendus (geometry, durées, distances) sont conservés,
 *    le JSON stocké en base est donc toujours produit par notre json_encode
 */
class RoutingService
{
    /**
     * URL de l'API de calcul d'itinéraire de la Géoplateforme (IGN).
     */
    private const ROUTING_API_URL = 'https://data.geopf.fr/navigation/itineraire';

    /**
     * Moteur de calcul utilisé. "bdtopo-osrm" est le plus performant
     * (recommandé par l'IGN pour les besoins standards).
     */
    private const RESOURCE = 'bdtopo-osrm';

    /**
     * Timeout total d'un appel HTTP, en secondes.
     */
    private const HTTP_TIMEOUT = 10;

    /**
     * Timeout d'établissement de la connexion, en secondes.
     */
    private const HTTP_CONNECT_TIMEOUT = 5;

    /**
     * Taille maximale de réponse acceptée, en octets (garde-fou mémoire).
     * Un tracé routier GeoJSON longue distance pèse quelques centaines de Ko.
     */
    private const MAX_RESPONSE_BYTES = 10_485_760; // 10 Mo

    /**
     * Récupère un itinéraire au format GeoJSON (FeatureCollection) via
     * l'API Géoplateforme.
     *
     * La signature est identique à l'ancienne implémentation ORS :
     * coordonnées en entrée au format [[lat, lon], [lat, lon], ...],
     * premier point = départ, dernier = arrivée, points intermédiaires
     * = étapes.
     *
     * @param  array<int, array{0: float, 1: float}> $coordinates Tableau de coordonnées
     *         au format [[lat, lon], [lat, lon], ...]
     * @return string|null GeoJSON (FeatureCollection) sérialisé,
     *                     ou null en cas d'erreur ou de réponse invalide
     */
    public function getTrack(array $coordinates): ?string
    {
        if (count($coordinates) < 2) {
            log_message('warning', 'RoutingService: au moins 2 points sont requis.');
            return null;
        }

        // --- Validation + conversion [lat, lon] -> "lon,lat" (format attendu par l'API)
        $points = [];
        foreach ($coordinates as $coordinate) {
            $point = $this->formatPoint($coordinate);
            if ($point === null) {
                log_message('warning', 'RoutingService: coordonnée invalide ignorée -> appel annulé.');
                return null;
            }
            $points[] = $point;
        }

        $start         = array_shift($points);
        $end           = array_pop($points);
        $intermediates = $points; // étapes restantes (éventuellement vide)

        $body = [
            'resource'       => self::RESOURCE,
            'profile'        => 'car',
            'optimization'   => 'fastest',
            'start'          => $start,
            'end'            => $end,
            'geometryFormat' => 'geojson',
            'timeUnit'       => 'second',
            'distanceUnit'   => 'meter',
            'getSteps'       => false,
            'getBbox'        => false,
            'crs'            => 'EPSG:4326',
        ];

        if (!empty($intermediates)) {
            $body['intermediates'] = $intermediates;
        }

        $payload = json_encode($body);
        if ($payload === false) {
            log_message('error', 'RoutingService: échec de sérialisation du corps de requête.');
            return null;
        }

        $ch = curl_init(self::ROUTING_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::HTTP_CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXFILESIZE    => self::MAX_RESPONSE_BYTES,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            log_message('error', 'RoutingService: échec API Géoplateforme (HTTP {code}, cURL: {curl})', [
                'code' => $httpCode,
                'curl' => $curlError !== '' ? $curlError : 'aucune',
            ]);
            return null;
        }

        return $this->convertToFeatureCollection($response);
    }

    /**
     * Valide une coordonnée [lat, lon] et la formate en chaîne "lon,lat".
     *
     * Le formatage via sprintf('%.6F') (F majuscule) est indépendant de la
     * locale PHP : pas de risque de virgule décimale ("2,33") qui casserait
     * le séparateur "lon,lat". 6 décimales ≈ précision 11 cm, suffisant.
     *
     * @param  mixed $coordinate Coordonnée attendue au format [lat, lon]
     * @return string|null "lon,lat", ou null si la coordonnée est invalide
     */
    private function formatPoint(mixed $coordinate): ?string
    {
        if (!is_array($coordinate) || !isset($coordinate[0], $coordinate[1])
            || !is_numeric($coordinate[0]) || !is_numeric($coordinate[1])) {
            return null;
        }

        $lat = (float) $coordinate[0];
        $lon = (float) $coordinate[1];

        if ($lat < -90.0 || $lat > 90.0 || $lon < -180.0 || $lon > 180.0) {
            return null;
        }

        return sprintf('%.6F,%.6F', $lon, $lat);
    }

    /**
     * Convertit la réponse brute de l'API Géoplateforme au format
     * FeatureCollection historique (compatible ORS).
     *
     * Mapping effectué :
     *  - geometry            -> features[0].geometry
     *  - portions[].duration -> features[0].properties.segments[].duration
     *  - portions[].distance -> features[0].properties.segments[].distance
     *  - duration/distance   -> features[0].properties.summary
     *
     * Seuls ces champs sont conservés : le GeoJSON stocké est intégralement
     * reconstruit par nos soins (allow-list), jamais recopié tel quel.
     *
     * @param  string $rawResponse Réponse JSON brute de l'API
     * @return string|null GeoJSON sérialisé, ou null si la réponse est inexploitable
     */
    private function convertToFeatureCollection(string $rawResponse): ?string
    {
        $data = json_decode($rawResponse, true);

        if (
            !is_array($data)
            || ($data['geometry']['type'] ?? null) !== 'LineString'
            || empty($data['geometry']['coordinates'])
            || !is_array($data['geometry']['coordinates'])
        ) {
            log_message('error', 'RoutingService: réponse Géoplateforme sans géométrie exploitable.');
            return null;
        }

        // --- Reconstruction de la géométrie (allow-list : uniquement des paires numériques)
        $lineCoordinates = [];
        foreach ($data['geometry']['coordinates'] as $pair) {
            if (!is_array($pair) || !isset($pair[0], $pair[1])
                || !is_numeric($pair[0]) || !is_numeric($pair[1])) {
                log_message('error', 'RoutingService: coordonnée non numérique dans la géométrie renvoyée.');
                return null;
            }
            $lineCoordinates[] = [(float) $pair[0], (float) $pair[1]];
        }

        // --- Conversion des portions en segments (durée/distance par tronçon)
        $segments = [];
        foreach ($data['portions'] ?? [] as $portion) {
            $segments[] = [
                'duration' => max(0.0, (float) ($portion['duration'] ?? 0)),
                'distance' => max(0.0, (float) ($portion['distance'] ?? 0)),
            ];
        }

        // Sécurité : si l'API ne renvoyait aucune portion, on crée un segment
        // unique à partir des totaux pour ne jamais produire un GeoJSON
        // sans segments (computeStageDepartures lèverait une exception).
        if (empty($segments)) {
            $segments[] = [
                'duration' => max(0.0, (float) ($data['duration'] ?? 0)),
                'distance' => max(0.0, (float) ($data['distance'] ?? 0)),
            ];
        }

        $featureCollection = [
            'type'     => 'FeatureCollection',
            'features' => [[
                'type'       => 'Feature',
                'geometry'   => [
                    'type'        => 'LineString',
                    'coordinates' => $lineCoordinates,
                ],
                'properties' => [
                    'segments' => $segments,
                    'summary'  => [
                        'duration' => max(0.0, (float) ($data['duration'] ?? 0)),
                        'distance' => max(0.0, (float) ($data['distance'] ?? 0)),
                    ],
                ],
            ]],
        ];

        $json = json_encode($featureCollection);
        if ($json === false) {
            log_message('error', 'RoutingService: échec de sérialisation du GeoJSON de sortie.');
            return null;
        }

        return $json;
    }
}