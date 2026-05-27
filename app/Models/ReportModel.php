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
        'status',      
        'admin_comment', 
        'resolved_at',   
        'resolved_by',  
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
/**
     * Récupère les signalements ouverts avec les alias attendus par la vue
     */
    public function getOpenReports(): array
    {
        // En utilisant $this directement, on profite des capacités natives du modèle
        return $this->select('report.*, 
                      reporter.firstname AS reporter_firstname, reporter.lastname AS reporter_lastname,
                      reported.firstname AS reported_firstname, reported.lastname AS reported_lastname,
                      j.start_datetime AS journey_date')
            ->join('user reporter', 'reporter.id = report.user_id')
            ->join('journey j', 'j.id = report.journey_id')
            ->join('user reported', 'reported.id = j.user_id')
            ->where('report.status', 'open')
            ->orderBy('report.created_at', 'DESC')
            ->findAll(); // findAll() retourne directement un tableau de résultats
    }

    /**
     * Clôture un signalement
     */
    public function resolve(int $id, string $action, string $comment, int $adminId): bool
    {
        // Utilisation propre de la méthode native update() sans risque de conflit d'alias
        return $this->update($id, [
            'status'        => 'closed',
            'admin_comment' => $comment,
            'resolved_at'   => date('Y-m-d H:i:s'),
            'resolved_by'   => $adminId,
        ]);
    }

    /**
     * Récupère un report spécifique avec l'ID du créateur du trajet
     */
    public function getWithJourneyOwner(int $id): ?array
    {
        return $this->select('report.*, j.user_id AS reported_user_id')
            ->join('journey j', 'j.id = report.journey_id')
            ->where('report.id', $id)
            ->first(); // first() retourne la première ligne sous forme de tableau ou null
    }
}
