<?php

namespace App\Models;

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
        'location_start_id',
        'location_end_id'

    ];

    protected $validationRules = [
        'start_datetime' => 'required|valid_date|after[now]',
        'seats' => 'required|integer|greater_than_equal_to[2]|less_than_equal_to[8]',
        'note' => 'less_than_equal_to[1000]',
        'smoking' => 'required|in_list[0,1]',
        'canceled_at' => 'permit_empty|valid_date',
        'track_id' => 'required|integer',
        'user_id' => 'required|integer',
        'location_start_id' => 'required|integer',
        'location_end_id' => 'required|integer',
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
    ];
}