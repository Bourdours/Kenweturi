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
     * Valide une URL de retour pour éviter les redirections ouvertes (open redirect).
     * L'URL doit avoir le même host que l'application et utiliser un schéma http/https.
     *
     * @param string|null $url URL de retour à valider
     * @return string URL validée si conforme, URL de repli (page journeys) sinon
     */
    function validateBackUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        try {
            $uri     = new URI($url);
            $baseUri = new URI(base_url());

            if ($uri->getHost() !== $baseUri->getHost()) {
                return null;
            }
            if (! in_array($uri->getScheme(), ['http', 'https'], true)) {
                return null;
            }
            if ($uri->getPort() !== $baseUri->getPort()) {
                return null;
            }

            return (string) $uri;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
