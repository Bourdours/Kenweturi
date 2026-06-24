<?php

namespace App\Exceptions;

/**
 * Levée lorsqu'on tente de supprimer le dernier administrateur actif.
 */
class LastAdminException extends \RuntimeException
{
}