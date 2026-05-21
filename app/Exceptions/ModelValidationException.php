<?php

namespace App\Exceptions;

/**
 * Exception levée lorsqu'un model refuse une insertion à cause
 * de ses règles de validation. Transporte les erreurs renvoyées
 * par le model pour les afficher à l'utilisateur.
 */
class ModelValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation du modèle échouée');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}