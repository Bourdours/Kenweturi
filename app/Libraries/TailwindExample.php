<?php

namespace App\Libraries;

/**
 * Générateur de composants HTML stylisés avec Tailwind CSS.
 * Requiert que public/css/tailwind.css soit inclus dans la vue.
 */
class TailwindExample
{
    /**
     * Génère une alerte colorée selon le type
     *
     * @param string $type    'success' | 'error' | 'warning' | 'info'
     * @param string $message Texte affiché dans l'alerte
     */
    public function alert(string $type, string $message): string
    {
        $styles = [
            'success' => 'bg-green-100 border border-green-400 text-green-800',
            'error'   => 'bg-red-100 border border-red-400 text-red-800',
            'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-800',
            'info'    => 'bg-blue-100 border border-blue-400 text-blue-800',
        ];

        $classes = $styles[$type] ?? $styles['info'];
        $escaped = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        return "<div class=\"{$classes} px-4 py-3 rounded\">{$escaped}</div>";
    }

    /**
     * Génère un bouton stylisé
     *
     * @param string $label   Texte du bouton
     * @param string $variant 'primary' | 'secondary' | 'danger'
     * @param array  $attrs   Attributs HTML supplémentaires (type, id, onclick…)
     */
    public function button(string $label, string $variant = 'primary', array $attrs = []): string
    {
        $styles = [
            'primary'   => 'bg-blue-600 hover:bg-blue-700 text-white',
            'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
            'danger'    => 'bg-red-600 hover:bg-red-700 text-white',
        ];

        $classes  = ($styles[$variant] ?? $styles['primary']) . ' px-4 py-2 rounded font-medium transition';
        $escaped  = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $attrStr  = '';

        foreach ($attrs as $key => $val) {
            $attrStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
        }

        return "<button class=\"{$classes}\"{$attrStr}>{$escaped}</button>";
    }

    /**
     * Génère un badge (pastille colorée)
     *
     * @param string $text  Texte affiché
     * @param string $color 'green' | 'red' | 'yellow' | 'blue' | 'gray'
     */
    public function badge(string $text, string $color = 'gray'): string
    {
        $styles = [
            'green'  => 'bg-green-100 text-green-800',
            'red'    => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'blue'   => 'bg-blue-100 text-blue-800',
            'gray'   => 'bg-gray-100 text-gray-700',
        ];

        $classes = ($styles[$color] ?? $styles['gray']) . ' text-xs font-semibold px-2.5 py-0.5 rounded-full';
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return "<span class=\"{$classes}\">{$escaped}</span>";
    }

    /**
     * Génère une carte avec titre, contenu et pied optionnel
     *
     * @param string $title   Titre de la carte
     * @param string $body    Contenu HTML (non échappé — à sécuriser en amont)
     * @param string $footer  Contenu HTML du pied de carte (optionnel)
     */
    public function card(string $title, string $body, string $footer = ''): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $footerHtml   = $footer
            ? "<div class=\"px-6 py-4 border-t border-gray-200 text-sm text-gray-500\">{$footer}</div>"
            : '';

        return <<<HTML
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">{$escapedTitle}</h3>
                </div>
                <div class="px-6 py-4">{$body}</div>
                {$footerHtml}
            </div>
            HTML;
    }
}

/*
|--------------------------------------------------------------------------
| UTILISATION DANS UN CONTROLLER
|--------------------------------------------------------------------------
|
| $tw = new \App\Libraries\TailwindExample();
|
| // Alerte
| echo $tw->alert('success', 'Enregistrement réussi.');
| echo $tw->alert('error',   'Une erreur est survenue.');
|
| // Bouton
| echo $tw->button('Envoyer');
| echo $tw->button('Supprimer', 'danger', ['type' => 'submit', 'id' => 'btn-delete']);
|
| // Badge
| echo $tw->badge('Actif',   'green');
| echo $tw->badge('Inactif', 'red');
|
| // Carte
| echo $tw->card(
|     'Titre de la carte',
|     '<p>Contenu principal de la carte.</p>',
|     'Dernière mise à jour : aujourd\'hui'
| );
|
|--------------------------------------------------------------------------
| INCLURE LE CSS DANS LA VUE
|--------------------------------------------------------------------------
|
| <link rel="stylesheet" href="/css/tailwind.css">
|
| Rebuild en développement :   npm run dev
| Build minifié en production : npm run build
|
*/
