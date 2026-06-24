<?php

namespace App\Exceptions;

/**
 * Levée lorsqu'on tente de supprimer un super-administrateur (interdit à tous).
 */
class CannotDeleteSuperadminException extends \RuntimeException
{
}