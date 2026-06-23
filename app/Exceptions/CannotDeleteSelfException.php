<?php

namespace App\Exceptions;

/**
 * Levée lorsqu'un utilisateur tente de supprimer son propre compte.
 */
class CannotDeleteSelfException extends \RuntimeException
{
}