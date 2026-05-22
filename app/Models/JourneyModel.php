<?php

namespace App\Models;

/**
 * Modèle pour la gestion des trajets de covoiturage.
 *
 * Gère les opérations CRUD sur la table `journey` ainsi que la validation
 * des données associées (créneau, places, lieux de départ et d'arrivée,
 * tracé du parcours, etc.).
 *
 * Un journey est lié à :
 * - un utilisateur conducteur (user_id)
 * - un tracé GeoJSON (track_id)
 * - une localisation de départ (location_start_id)
 * - une localisation d'arrivée (location_end_id)
 *
 * Règles métier appliquées via la validation :
 * - La date de départ doit être dans le futur
 * - Le nombre de places est compris entre 1 et 8
 * - Les lieux de départ et d'arrivée doivent être différents
 * - Toutes les clés étrangères doivent référencer des entrées existantes
 *
 * @package App\Models
 */
class JourneyModel extends BaseModel
{
    protected $table = 'journey';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'start_datetime',
        'seats',
        'note',
        'smoking',
        'canceled_at',
        'track_id',
        'user_id',
        'car_id',
        'location_start_id',
        'location_end_id'

    ];

    protected $validationRules = [
        'start_datetime'    => 'required|valid_date[Y-m-d H:i:s]|after_now',
        'seats'             => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[8]',
        'note'              => 'permit_empty|string|max_length[1000]',
        'smoking'           => 'required|in_list[0,1]',
        'canceled_at'       => 'permit_empty|valid_date',
        'track_id'          => 'required|integer|is_not_unique[track.id]',
        'user_id'           => 'required|integer|is_not_unique[user.id]',
        'location_start_id' => 'required|integer|is_not_unique[location.id]',
        'location_end_id'   => 'required|integer|is_not_unique[location.id]|differs[location_start_id]',
    ];

    protected $validationMessages = [
        'start_datetime' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Veuillez renseigner une date valide.',
            'after'      => 'La date de départ doit être dans le futur.',
        ],
        'seats' => [
            'required'              => 'Veuillez renseigner le nombre de places.',
            'greater_than_equal_to' => 'Le trajet doit avoir au moins 2 places.',
            'less_than_equal_to'    => 'Le trajet ne peut pas dépasser 8 places.',
        ],
        'note' => [
           'less_than_equal_to' => 'Le message doit contenir au maximum 1000 caractères.',
        ],
        'smoking' => [
            'required' => 'Veuillez indiquer si le covoiturage est fumeur ou non.',
            'in_list'  => 'La valeur sélectionnée n\'est pas valide.',
        ],
        'canceled_at' => [
            'valid_date' => 'La date d\'annulation doit être une date valide.',
        ],
        'track_id' => [
            'required'      => 'L\'identifiant du tracé est obligatoire.',
            'integer'       => 'L\'identifiant du tracé doit être un nombre entier.',
            'is_not_unique' => 'Le tracé spécifié n\'existe pas.',
        ],
        'user_id' => [
            'required'      => 'L\'identifiant de l\'utilisateur est obligatoire.',
            'integer'       => 'L\'identifiant de l\'utilisateur doit être un nombre entier.',
            'is_not_unique' => 'L\'utilisateur spécifié n\'existe pas.',
        ],
        'location_start_id' => [
            'required'      => 'Le lieu de départ est obligatoire.',
            'integer'       => 'Le lieu de départ doit être un identifiant valide.',
            'is_not_unique' => 'Le lieu de départ sélectionné n\'existe pas.',
        ],
        'location_end_id' => [
            'required'      => 'Le lieu d\'arrivée est obligatoire.',
            'integer'       => 'Le lieu d\'arrivée doit être un identifiant valide.',
            'is_not_unique' => 'Le lieu d\'arrivée sélectionné n\'existe pas.',
        ]
    ];
}