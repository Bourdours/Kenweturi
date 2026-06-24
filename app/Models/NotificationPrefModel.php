<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationPrefModel extends Model
{
    protected $table      = 'user_notification_pref';
    protected $primaryKey = 'user_id';

    protected $allowedFields = ['user_id', 'pref', 'enabled'];

    public const PREFS = [
        'booking_request'   => 'Nouvelle demande de réservation sur votre trajet',
        'booking_accepted'  => 'Votre réservation a été acceptée',
        'booking_rejected'  => 'Votre réservation a été refusée',
        'booking_cancelled' => 'Un passager a annulé sa réservation',
        'journey_cancelled' => 'Un trajet sur lequel vous êtes réservé a été annulé',
        'journey_request'   => 'Un trajet correspond à votre demande de trajet',
        'admin_registration' => 'Nouvelle demande d\'inscription à valider (administrateurs)',
        'admin_report'      => 'Nouveau signalement à traiter (administrateurs)',
    ];

    /**
     * Retourne true si l'utilisateur veut recevoir ce type de notification.
     * Par défaut (ligne absente) = activé.
     */
    public function wantsNotif(int $userId, string $pref): bool
    {
        $row = $this->where('user_id', $userId)->where('pref', $pref)->first();
        return $row === null || (bool) $row['enabled'];
    }

    /**
     * Retourne toutes les préférences d'un utilisateur sous forme [pref => bool].
     * Les prefs absentes sont considérées comme activées.
     */
    public function getAllForUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        $map  = array_column($rows, 'enabled', 'pref');

        $result = [];
        foreach (array_keys(self::PREFS) as $key) {
            $result[$key] = isset($map[$key]) ? (bool) $map[$key] : true;
        }
        return $result;
    }

    /**
     * Sauvegarde les préférences d'un utilisateur.
     * $submitted  = slugs cochés dans le formulaire.
     * $allowedMap = sous-ensemble de PREFS à traiter (par défaut tous).
     *               Permet de ne pas écraser les prefs hors portée (ex: admin_* pour un user).
     */
    public function saveForUser(int $userId, array $submitted, array $allowedMap = self::PREFS): void
    {
        foreach (array_keys($allowedMap) as $key) {
            $enabled  = in_array($key, $submitted, true) ? 1 : 0;
            $existing = $this->where('user_id', $userId)->where('pref', $key)->first();

            if ($existing) {
                $this->where('user_id', $userId)->where('pref', $key)
                     ->set('enabled', $enabled)->update();
            } else {
                $this->insert(['user_id' => $userId, 'pref' => $key, 'enabled' => $enabled]);
            }
        }
    }
}
