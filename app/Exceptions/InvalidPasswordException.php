<?php

namespace App\Exceptions;

/**
 * Levée lorsqu'un admin simple tente de supprimer un autre admin
 * (seul un super-administrateur en a le droit).
 */
class InvalidPasswordException extends \RuntimeException
{
}