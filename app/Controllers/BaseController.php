<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Récupère le nom officiel d'une commune française à partir d'un couple (nom saisi, code postal)
     * via l'API Découpage administratif (geo.api.gouv.fr).
     *
     * @param string $cityName Nom de la commune saisi par l'utilisateur
     * @param string $zipCode  Code postal saisi par l'utilisateur
     * @return string|false|null Nom officiel si valide, false si invalide, null si le service est indisponible
     */
    function getCheckedCityName(string $cityName, string $zipCode): string|false|null
    {
        $url = 'https://geo.api.gouv.fr/communes?' . http_build_query([
            'nom'        => $cityName,
            'codePostal' => $zipCode,
            'fields'     => 'nom',
            'limit'      => 5,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

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

    /**
     * Valide une URL de retour pour éviter les redirections ouvertes (open redirect).
     * L'URL doit avoir le même host que l'application et utiliser un schéma http/https.
     *
     * @param string|null $url URL de retour à valider
     * @return string URL validée si conforme, URL de repli (page journeys) sinon
     */
    function validateBackUrl(?string $url): string
    {
        $fallback = site_url('journeys');
        if (empty($url)) {
            return $fallback;
        }
        try {
            $uri     = new URI($url);
            $baseUri = new URI(base_url());

            if ($uri->getHost() !== $baseUri->getHost()) {
                return $fallback;
            }
            if (! in_array($uri->getScheme(), ['http', 'https'], true)) {
                return $fallback;
            }
            if ($uri->getPort() !== $baseUri->getPort()) {
                return $fallback;
            }
            return (string) $uri;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}
