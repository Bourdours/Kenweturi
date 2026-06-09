<?php

namespace App\Services;

class RoutingService
{
    /**
     * URL de l'API de routage OpenRouteService (profil voiture, sortie GeoJSON).
     */
    private const ROUTING_API_URL = 'https://api.openrouteservice.org/v2/directions/driving-car/geojson';

    /**
     * Timeout des appels HTTP, en secondes.
     */
    private const HTTP_TIMEOUT = 10;

    /**
     * Récupère un itinéraire au format GeoJSON via l'API OpenRouteService.
     *
     * @param  array<int, array{0: float, 1: float}> $coordinates Tableau de coordonnées
     *         au format [[lat, long], [lat, long], ...]
     * @return string|null Réponse GeoJSON brute, ou null en cas d'erreur de l'API
     */
    public function getTrack(array $coordinates): ?string
    {
        $apiKey = $_ENV['ORS_API_KEY'] ?? null;

        $coordFields = [];
        foreach ($coordinates as $coordinate) {
            $lat  = $coordinate[0];
            $long = $coordinate[1];
            $coordFields[] = [$long, $lat];
        }

        $ch = curl_init(self::ROUTING_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json, application/geo+json',
                'Authorization: ' . $apiKey,
            ],
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => json_encode(['coordinates' => $coordFields]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        return $response;
    }
}