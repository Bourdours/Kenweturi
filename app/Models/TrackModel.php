<?php

namespace App\Models;

/**
 * Modèle gérant la table 'track'
 */
class TrackModel extends BaseModel
{
    protected $table = 'track';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'GeoJson'
    ];

    protected $validationRules = [
        'GeoJson' => 'required|valid_json'
    ];
}