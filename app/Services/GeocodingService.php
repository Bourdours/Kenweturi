<?php

namespace App\Services;

class GeocodingService
{
    /**
     * URL de base de l'API de géocodage de la Géoplateforme (IGN/BAN).
     */
    private const GEOCODING_API_URL = 'https://data.geopf.fr/geocodage/search';

    /**
     * URL de base de l'API Découpage administratif (geo.api.gouv.fr).
     */
    private const COMMUNES_API_URL = 'https://geo.api.gouv.fr/communes';

    /**
     * Timeout des appels HTTP, en secondes.
     */
    private const HTTP_TIMEOUT = 10;

    /**
     * Récupère les données de localisation d'une adresse via l'API de la Géoplateforme (IGN/BAN).
     *
     * @param  string $address Adresse en texte libre (ex : "8 bd du Port 95000 Cergy")
     * @param  int    $limit   Nombre max de résultats (défaut : 1)
     * @return array|null      Propriétés de l'adresse (avec latitude/longitude),
     *                         ou null si rien trouvé ou en cas d'erreur réseau
     */
    public function getLocationData(string $address, int $limit = 1): ?array
    {
        $url = self::GEOCODING_API_URL . '?' . http_build_query([
            'q'     => $address,
            'index' => 'address',
            'limit' => $limit,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        if (empty($data['features'])) {
            return null;
        }

        $feature = $data['features'][0];
        $result  = $feature['properties'] ?? [];

        if (isset($feature['geometry']['coordinates'])) {
            [$longitude, $latitude] = $feature['geometry']['coordinates'];
            $result['longitude'] = $longitude;
            $result['latitude']  = $latitude;
        }

        return $result;
    }

    /**
     * Récupère le nom officiel d'une commune française à partir d'un couple
     * (nom saisi, code postal) via l'API Découpage administratif (geo.api.gouv.fr).
     *
     * La normalisation (suppression des accents, de la casse et des caractères
     * spéciaux) permet d'accepter des variantes orthographiques de l'utilisateur
     * tout en renvoyant la forme canonique fournie par l'API.
     *
     * @param  string $cityName Nom de la commune saisi par l'utilisateur
     * @param  string $zipCode  Code postal saisi par l'utilisateur
     * @return string|false|null Nom officiel si valide, false si invalide,
     *                           null si le service est indisponible
     */
    public function getCheckedCityName(string $cityName, string $zipCode): string|false|null
    {
        $url = self::COMMUNES_API_URL . '?' . http_build_query([
            'nom'        => $cityName,
            'codePostal' => $zipCode,
            'fields'     => 'nom',
            'limit'      => 5,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $municipalities = json_decode($response, true);
        if (empty($municipalities)) {
            return false;
        }

        // Normalisation : supprime accents, casse et caractères spéciaux
        $normalize = fn($s) => strtolower(preg_replace(
            '/[^a-z0-9]/i',
            '',
            iconv('UTF-8', 'ASCII//TRANSLIT', $s ?? '')
        ));

        $normalizedCity = $normalize($cityName);

        foreach ($municipalities as $m) {
            if ($normalize($m['nom']) === $normalizedCity) {
                return $m['nom']; // forme officielle renvoyée par l'API
            }
        }

        return false;
    }
}