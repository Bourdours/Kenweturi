<?php

namespace App\Models;

/**
 * Modèle gérant la table 'user'
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

    protected $beforeInsert   = ['hashPassword', 'setRegistrationDate'];
    protected $beforeUpdate   = ['hashPassword'];

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

    /**
     * Récupère un utilisateur à partir de son adresse email.
     *
     * Renvoie le premier enregistrement correspondant. Si le soft delete
     * est activé sur le Model, les comptes supprimés sont automatiquement exclus.
     *
     * @param  string                   $email Adresse email recherchée
     * @return array|object|null                Utilisateur trouvé, ou null si aucun
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Récupère tous les administrateurs actifs.
     *
     * Sert notamment à notifier l'équipe lors d'une nouvelle demande
     * d'inscription. Ne retourne que les comptes dont le statut est « active ».
     *
     * @return array Liste des administrateurs actifs (tableau vide si aucun)
     */
    public function getActiveAdmins(): array
    {
        return $this->where('is_admin', 1)
            ->where('status', 'active')
            ->findAll();
    }

    /**
     * Enregistre le token de réinitialisation de mot de passe.
     *
     * Le token est stocké haché (SHA-256) en base avec sa date d'expiration.
     * Le token brut n'est jamais persisté : il part uniquement dans l'email
     * envoyé à l'utilisateur.
     *
     * @param  int    $userId   Identifiant de l'utilisateur
     * @param  string $rawToken Token brut (non haché) à enregistrer
     * @param  int    $hours    Durée de validité en heures (1 par défaut)
     * @return bool             true si la mise à jour a réussi, false sinon
     */
    public function setResetToken(int $userId, string $rawToken, int $hours = 1): bool
    {
        return $this->update($userId, [
            'reset_token'        => hash('sha256', $rawToken),
            'reset_token_expiry' => date('Y-m-d H:i:s', strtotime("+{$hours} hour")),
        ]);
    }

    /**
     * Récupère un utilisateur via un token de réinitialisation valide.
     *
     * Le token reçu est haché puis comparé à celui stocké en base. Seuls les
     * tokens non expirés (date d'expiration strictement postérieure à maintenant)
     * sont acceptés.
     *
     * @param  string                   $rawToken Token brut issu du lien email
     * @return array|object|null                   Utilisateur correspondant, ou null si le token est invalide/expiré
     */
    public function findByValidResetToken(string $rawToken)
    {
        return $this->where('reset_token', hash('sha256', $rawToken))
            ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur et invalide ses tokens.
     *
     * Le nouveau mot de passe est haché via password_hash(). Tous les tokens
     * (réinitialisation et « Se souvenir de moi ») sont remis à null pour des
     * raisons de sécurité, forçant une reconnexion sur les autres appareils.
     *
     * @param  int    $userId      Identifiant de l'utilisateur
     * @param  string $rawPassword Nouveau mot de passe en clair (sera haché)
     * @return bool                true si la mise à jour a réussi, false sinon
     */
    public function resetPassword(int $userId, string $rawPassword): bool
    {
        return $this->update($userId, [
            'password_hash'         => $rawPassword,
            'reset_token'           => null,
            'reset_token_expiry'    => null,
        ]);
    }
}
