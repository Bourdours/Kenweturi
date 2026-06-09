<?php

namespace App\Models;

class RememberTokenModel extends BaseModel
{
    protected $table         = 'remember_tokens';
    protected $allowedFields = ['user_id', 'token', 'expires_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Recherche un jeton d'authentification valide et non expiré en base de données.
     * Le jeton fourni est haché en SHA-256 avant la comparaison.
     *
     * @param string $token Le jeton brut récupéré du cookie.
     * @return array|null Les données du jeton sous forme de tableau, ou null si invalide/expiré.
     */
    public function findByToken(string $token): ?array
    {
        return $this->where('token', hash('sha256', $token))
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Supprime tous les jetons actifs d'un utilisateur.
     * Utile pour déconnecter de force tous les appareils (ex: lors d'un changement de mot de passe).
     *
     * @param int $userId L'identifiant de l'utilisateur.
     * @return void
     */
    public function deleteAll(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }

    /**
     * Supprime un jeton spécifique en base de données à partir de sa valeur brute.
     * Principalement utilisé lors de la déconnexion manuelle d'un appareil spécifique.
     *
     * @param string $token Le jeton brut à révoquer.
     * @return void
     */
    public function deleteOne(string $token): void
    {
        $this->where('token', hash('sha256', $token))->delete();
    }
}
