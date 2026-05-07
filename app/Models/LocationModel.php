<?php 

namespace App\Models;

/**
 * Modèle gérant la table 'location'
 */
class LocationModel extends BaseModel
{
    protected $table = 'location';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'latitude',
        'longitude',
        'address',
        'note',
        'city_id'
    ];

    protected $validationRules = [
        'latitude'  => 'required|decimal',
        'longitude' => 'required|decimal',
        'address'   => 'required|max_length[255]',
        'note'      => 'permit_empty|max_length[1000]',
        'city_id'   => 'required|integer',
    ];

}