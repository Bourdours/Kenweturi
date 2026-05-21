<?php

namespace App\Exceptions;

/**
 * Exception levée lorsqu'un service externe (géocodage, routage)
 * ne répond pas ou renvoie une réponse inexploitable.
 */
class ExternalApiException extends \RuntimeException {}