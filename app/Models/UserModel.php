<?php

namespace App\Models;

/**
 * Modèle gérant la table 'users'
 * S'occupe de la validation, du hachage des mots de passe et de la gestion des données.
 */
class UserModel extends BaseModel
{
    // Indique la table de la base de données utilisée par ce modèle
    protected $table            = 'user';

    // Définit la clé primaire de la table
    protected $primaryKey       = 'id';

    // Active l'incrémentation automatique de l'ID à chaque nouvel enregistrement
    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    // Liste des colonnes que l'on autorise à modifier ou insérer (sécurité)
    protected $allowedFields = [
        'firstname',
        'lastname',
        'email',
        'gender',
        'birth_date',
        'biography',
        'avatar',
        'is_student',
        'registered_at',
        'password_hash',
        'remember_token',
        'is_admin',
        'is_banned',
        'status',
        'city_id',
        'reset_token',
        'reset_token_expiry',
        'role',
    ];

    // Règles de validation appliquées automatiquement avant chaque insertion
    protected $validationRules = [
        'firstname'     => 'required|min_length[2]|max_length[100]',
        'lastname'      => 'required|min_length[2]|max_length[100]',
        'email'         => 'required|valid_email|is_unique[user.email]',
        'gender'        => 'required|in_list[Homme,Femme,Autre]',
        'birth_date'    => 'required|valid_date',

    ];

    // Messages d'erreurs
    protected $validationMessages = [
        'firstname' => [
            'required'   => 'Le prénom est obligatoire.',
            'min_length' => 'Le prénom doit contenir au moins 2 caractères.'
        ],
        'lastname' => [
            'required'   => 'Le nom est obligatoire.',
            'min_length' => 'Le nom doit contenir au moins 2 caractères.'
        ],
        'email' => [
            'required'    => 'L\'adresse email est obligatoire.',
            'valid_email' => 'Veuillez saisir une adresse email valide.',
            'is_unique'   => 'Désolé, cet email est déjà utilisé.'
        ],
        'gender' => [
            'required' => 'Veuillez choisir un genre.',
            'in_list'  => 'Le genre sélectionné n\'est pas valide.'
        ],
    ];

    // Fonctions à exécuter automatiquement juste avant l'insertion en base de données
    protected $beforeInsert = ['hashPassword', 'setRegistrationDate'];

    // Hash le mot de passe
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password_hash'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password_hash'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Génère automatiquement la date et l'heure actuelle pour le champ registered_at
     */
    protected function setRegistrationDate(array $data)
    {
        if (!isset($data['data']['registered_at'])) {
            $data['data']['registered_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * Récupère tous les utilisateurs en attente avec le nom de leur ville.
     * Fait une jointure LEFT JOIN sur la table 'city' pour afficher le nom au lieu de l'ID.
     *
     * @return array Liste des utilisateurs avec le champ 'city_name'
     */
    public function getPendingUsersWithCity(): array
    {
        return $this->db->table('user')
            ->select('user.*, city.name AS city_name')
            ->join('city', 'city.id = user.city_id', 'left')
            ->where('user.status', 'pending')
            ->get()
            ->getResultArray();
    }
}
