<?php

namespace App\Libraries;

use Pusher\Pusher;
use Pusher\PusherException;

class PusherExample
{
    private Pusher $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('pusher.appKey'),
            env('pusher.appSecret'),
            env('pusher.appId'),
            [
                'cluster' => env('pusher.cluster', 'eu'),
                'useTLS'  => true,
            ]
        );
    }

    /**
     * Envoie un événement sur un canal public
     */
    public function trigger(string $channel, string $event, array $data): bool
    {
        try {
            $this->pusher->trigger($channel, $event, $data);
            return true;
        } catch (PusherException $e) {
            log_message('error', 'PusherExample::trigger - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un événement sur plusieurs canaux à la fois
     */
    public function triggerMultiple(array $channels, string $event, array $data): bool
    {
        try {
            $this->pusher->trigger($channels, $event, $data);
            return true;
        } catch (PusherException $e) {
            log_message('error', 'PusherExample::triggerMultiple - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un événement sur un canal privé (private-*)
     * Le canal doit être authentifié côté client avec pusher.subscribe('private-canal')
     */
    public function triggerPrivate(string $channelName, string $event, array $data): bool
    {
        return $this->trigger('private-' . $channelName, $event, $data);
    }

    /**
     * Authentifie un canal privé (à appeler depuis une route dédiée)
     * Côté client : pusher.config.authEndpoint = '/pusher/auth'
     */
    public function authenticateChannel(string $socketId, string $channelName): string
    {
        return $this->pusher->authorizeChannel($channelName, $socketId);
    }

    /**
     * Retourne l'instance Pusher brute pour les usages avancés
     */
    public function getInstance(): Pusher
    {
        return $this->pusher;
    }
}

/*
|--------------------------------------------------------------------------
| UTILISATION DANS UN CONTROLLER
|--------------------------------------------------------------------------
|
| $pusher = new \App\Libraries\PusherExample();
|
| // Envoyer un message sur un canal public
| $pusher->trigger('chat', 'nouveau-message', ['user' => 'Alice', 'texte' => 'Bonjour !']);
|
| // Envoyer sur plusieurs canaux
| $pusher->triggerMultiple(['canal-1', 'canal-2'], 'alerte', ['message' => 'Mise à jour disponible']);
|
| // Envoyer sur un canal privé
| $pusher->triggerPrivate('user-42', 'notification', ['titre' => 'Nouveau message']);
|
|--------------------------------------------------------------------------
| CÔTÉ CLIENT (JavaScript) — inclure le SDK Pusher JS
|--------------------------------------------------------------------------
|
| <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
| <script>
|   const pusher = new Pusher('APP_KEY', { cluster: 'eu' });
|   const channel = pusher.subscribe('chat');
|   channel.bind('nouveau-message', (data) => {
|     console.log(data.user + ' : ' + data.texte);
|   });
| </script>
|
*/
