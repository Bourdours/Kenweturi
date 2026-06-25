<?php

namespace App\Models;


class CarModel extends BaseModel
{
    protected $table = 'car';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'brand',
        'model',
        'color',
        'seats',
        'user_id'
    ];

    protected $validationRules = [
        'brand' => 'required|min_length[3]|max_length[50]',
        'model' => 'required|min_length[3]|max_length[50]',
        'color' => 'required|min_length[4]|max_length[50]',
        'seats' => 'required|integer|greater_than[0]|less_than_equal_to[8]',
        'user_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'brand' => [
            'required'   => 'Veuillez renseigner une marque de voiture.',
            'min_length' => 'La marque doit contenir au moins 3 caractères.',
            'max_length' => 'La marque doit contenir maximum 50 caractères.'
        ],

        'model' => [
            'required'   => 'Veuillez renseigner un model de voiture.',
            'min_length' => 'Le model doit contenir au moins 3 caractères.',
            'max_length' => 'Le model doit contenir maximum 50 caractères.'
        ],

        'color' => [
            'required'   => 'Veuillez renseigner une couleur de voiture.',
            'min_length' => 'La couleur doit contenir au moins 4 caractères.',
            'max_length' => 'La couleur doit contenir maximum 50 caractères.'
        ],

        'seats' => [
            'required'            => 'Veuillez renseigner le nombre de places de votre voiture.',
            'integer'             => 'Le nombre de places doit être un nombre entier.',
            'greater_than'        => 'Le nombre de places doit être d\'au moins 1.',
            'less_than_equal_to'  => 'Le nombre de places ne peut pas dépasser 8.',
        ],
    ];

    /**
     * Récupère toutes les voitures appartenant à un utilisateur.
     *
     * @param  int $userId Identifiant du propriétaire
     * @return array       Liste des voitures de l'utilisateur
     */
    public function findByUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Récupère une voiture uniquement si elle appartient à l'utilisateur fourni.
     * Utile pour vérifier la propriété avant toute action (édition, choix dans un formulaire).
     *
     * @param  int $carId  Identifiant de la voiture
     * @param  int $userId Identifiant du propriétaire attendu
     * @return array|null  Voiture trouvée, ou null si elle n'existe pas / n'appartient pas à l'utilisateur
     */
    public function findOwnedByUser(int $carId, int $userId): ?array
    {
        return $this->where('id', $carId)
            ->where('user_id', $userId)
            ->first();
    }
}
