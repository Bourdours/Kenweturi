<?php

namespace App\Exceptions;

/**
 * Levée lorsque l'utilisateur ciblé par la suppression n'existe pas.
 */
class UserNotFoundException extends \RuntimeException
{
}