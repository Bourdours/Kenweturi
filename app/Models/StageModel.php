<?php

namespace App\Models;

class StageModel extends BaseModel
{
    protected $table = 'stage';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'departure_time',
        'position',
        'location_id',
        'journey_id'
    ];

    protected $validationRules = [
        'departure_time' => 'required|valid_time',
        'position' => 'required|integer|greater_than[0]',
        'location_id' => 'required|integer',
        'journey_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'departure_time' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_time' => 'Veuillez renseigner une date valide.',
        ]
    ];
}