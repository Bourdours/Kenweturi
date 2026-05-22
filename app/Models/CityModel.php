<?php 

namespace App\Models;

/**
 * Modèle gérant la table 'city'
 */
class CityModel extends BaseModel{

    protected $table = 'city';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $allowedFields = [
        'name',
        'zipcode'
    ];

    protected $validationRules = [
        'name'    => 'required|min_length[2]|max_length[50]',
        'zipcode' => 'required|exact_length[5]|numeric'
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Le nom de la ville est obligatoire.',
            'min_length' => 'Le nom doit contenir au moins 2 caractères.',
        ],
        'zipcode' => [
            'required'   => 'Le code postal est obligatoire.',
            'max_length' => 'Le code postal doit contenir exactement 5 chiffres.',
            'numeric'    => 'Le code postal ne doit contenir que des chiffres.',
        ],
    ];

    /**
     * Récupère l'ID d'une ville existante ou la crée si elle n'existe pas.
     *
     * @param string $name    Nom de la ville
     * @param string $zipcode Code postal
     * @return int|string ID de la ville
     */
    public function findOrCreateCity(string $name, string $zipcode)
    {
        $city = $this->where('name', $name)
                    ->where('zipcode', $zipcode)
                    ->first();

        return $city['id'] ?? $this->insert([
            'name'    => $name,
            'zipcode' => $zipcode,
        ]);
    }
}