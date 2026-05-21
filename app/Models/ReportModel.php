<?php

namespace App\Models;

/**
 * Modèle gérant la table 'report'.
 * Gère la validation des données de signalement.
 */
class ReportModel extends BaseModel
{
    protected $table            = 'report';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'title',
        'description',
        'journey_id',
        'user_id',
    ];

    protected $validationRules = [
        'title'       => 'required|min_length[3]|max_length[127]',
        'description' => 'required|min_length[10]|max_length[1000]',
        'journey_id'  => 'required|is_natural_no_zero|is_not_unique[journey.id]',
        'user_id'     => 'required|is_natural_no_zero|is_not_unique[user.id]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Le titre est obligatoire.',
            'min_length' => 'Le titre doit contenir au moins 3 caractères.',
            'max_length' => 'Le titre doit contenir au maximum 127 caractères.',
        ],
        'description' => [
            'required'   => 'La description est obligatoire.',
            'min_length' => 'La description doit contenir au moins 10 caractères.',
            'max_length' => 'La description doit contenir au maximum 1000 caractères.',
        ],
        'journey_id' => [
            'required'           => 'Le parcours associé est obligatoire.',
            'is_natural_no_zero' => 'Identifiant de parcours invalide.',
            'is_not_unique'      => 'Ce parcours n\'existe pas.',
        ],
        'user_id' => [
            'required'           => 'Utilisateur non identifié.',
            'is_natural_no_zero' => 'Identifiant utilisateur invalide.',
            'is_not_unique'      => 'Cet utilisateur n\'existe pas.',
        ],
    ];

    
}
