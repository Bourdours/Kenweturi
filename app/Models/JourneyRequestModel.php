<?php

namespace App\Models;

/**
 * Modèle gérant la table 'journey_request'.
 * Gère la validation des données.
 */
class JourneyRequestModel extends BaseModel
{
    protected $table = 'journey_request';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'start_datetime',
        'seats',
        'message',
        'user_id',
        'location_start_id',
        'location_end_id'

    ];

    protected $validationRules = [
        'start_datetime' => 'required|valid_date|after[now]',
        'seats' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[8]',
        'message' => 'required|less_than_equal_to[2000]',
        'user_id' => 'required|integer|is_not_unique[user.id]',
        'location_start_id' => 'required|integer|is_not_unique[location.id]',
        'location_end_id' => 'required|integer|is_not_unique[location.id]',
    ];

    protected $validationMessages = [
        'start_datetime' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Veuillez renseigner une date valide.',
            'after'      => 'La date de départ doit être dans le futur.',
        ],
        'seats' => [
            'required'              => 'Veuillez renseigner le nombre de places.',
            'greater_than_equal_to' => 'Le trajet doit avoir au moins 1 places.',
            'less_than_equal_to'    => 'Le trajet ne peut pas dépasser 8 places.',
        ],
        'message' => [
            'required'   => 'Le message est obligatoire.',
            'max_length' => 'Le message doit contenir au maximum 2000 caractères.',
        ],
        'location_start_id' => [
            'required'      => 'Le lieu de départ est obligatoire.',
            'integer'       => 'Identifiant du lieu de départ invalide.',
            'is_not_unique' => 'Le lieu de départ sélectionné n\'existe pas.',
        ],
        'location_end_id' => [
            'required'      => 'Le lieu d\'arrivée est obligatoire.',
            'integer'       => 'Identifiant du lieu d\'arrivée invalide.',
            'is_not_unique' => 'Le lieu d\'arrivée sélectionné n\'existe pas.',
        ],
    ];
}