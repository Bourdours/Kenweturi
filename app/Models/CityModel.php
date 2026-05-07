<?php 

namespace App\Models;

/**
 * Modèle gérant la table 'city'
 */
class CityModel extends BaseModel
{
    protected $table = 'city';
    protected $primaryKey = 'id';
    protected $userAutoIncrement = true;
    
    protected $allowFields = [
        'name',
        'zipcode'
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[50]',
        'zipcode' => 'required|exact_length[5]|numeric'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Le nom de la ville est obligatoire.',
            'min_length' => 'Le nom doit contenir au moins 2 caractères.',
        ],
        'zipcode' => [
            'required'   => 'Le code postal est obligatoire.',
            'max_length' => 'Le code postal doit contenir 5 caractères.',
            'numeric'    => 'Le code postal ne doit contenir que des chiffres.',
        ],
    ];
}