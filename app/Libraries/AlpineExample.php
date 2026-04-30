<?php

namespace App\Libraries;

/**
 * Générateur de composants HTML interactifs avec Alpine.js.
 * Requiert que le script Alpine.js soit inclus dans la vue (CDN ou bundle).
 */
class AlpineExample
{
    /**
     * Génère un compteur incrémentable / décrémentable
     *
     * @param int $initial Valeur de départ
     * @param int $min     Valeur minimale
     * @param int $max     Valeur maximale (0 = pas de limite)
     */
    public function counter(int $initial = 0, int $min = 0, int $max = 0): string
    {
        $maxGuard = $max > 0 ? " && count < {$max}" : '';
        $minGuard = "count > {$min}";

        return <<<HTML
            <div x-data="{ count: {$initial} }" class="flex items-center gap-3">
                <button
                    x-on:click="if ({$minGuard}) count--"
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded font-bold"
                >−</button>
                <span x-text="count" class="text-lg font-semibold w-8 text-center"></span>
                <button
                    x-on:click="if (true{$maxGuard}) count++"
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded font-bold"
                >+</button>
            </div>
            HTML;
    }

    /**
     * Génère un bloc accordéon (titre cliquable qui révèle un contenu)
     *
     * @param string $title   Titre visible
     * @param string $content Contenu HTML révélé (non échappé — à sécuriser en amont)
     * @param bool   $open    Ouvert par défaut
     */
    public function accordion(string $title, string $content, bool $open = false): string
    {
        $state        = $open ? 'true' : 'false';
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <div x-data="{ open: {$state} }" class="border border-gray-200 rounded overflow-hidden">
                <button
                    x-on:click="open = !open"
                    class="w-full flex justify-between items-center px-4 py-3 bg-gray-50 hover:bg-gray-100 font-medium text-left"
                >
                    <span>{$escapedTitle}</span>
                    <svg
                        x-bind:class="open ? 'rotate-180' : ''"
                        class="w-4 h-4 transition-transform"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-4 py-3 text-gray-700">
                    {$content}
                </div>
            </div>
            HTML;
    }

    /**
     * Génère une modale avec bouton d'ouverture intégré
     *
     * @param string $triggerLabel Texte du bouton d'ouverture
     * @param string $title        Titre de la modale
     * @param string $body         Contenu HTML du corps (non échappé — à sécuriser en amont)
     */
    public function modal(string $triggerLabel, string $title, string $body): string
    {
        $escapedTrigger = htmlspecialchars($triggerLabel, ENT_QUOTES, 'UTF-8');
        $escapedTitle   = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <div x-data="{ open: false }">
                <button
                    x-on:click="open = true"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium"
                >{$escapedTrigger}</button>

                <div
                    x-show="open"
                    x-on:keydown.escape.window="open = false"
                    class="fixed inset-0 z-50 flex items-center justify-center"
                    style="display: none;"
                >
                    <div x-on:click="open = false" class="absolute inset-0 bg-black/50"></div>
                    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">{$escapedTitle}</h3>
                            <button x-on:click="open = false" class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>
                        <div class="text-gray-700">{$body}</div>
                    </div>
                </div>
            </div>
            HTML;
    }

    /**
     * Génère un système d'onglets
     *
     * @param array $tabs Tableau associatif [ 'Titre' => 'Contenu HTML', ... ]
     */
    public function tabs(array $tabs): string
    {
        if (empty($tabs)) {
            return '';
        }

        $firstKey  = array_key_first($tabs);
        $tabButtons = '';
        $tabPanels  = '';

        foreach ($tabs as $title => $content) {
            $escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $tabButtons .= <<<HTML
                        <button
                            x-on:click="active = '{$escaped}'"
                            x-bind:class="active === '{$escaped}' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium transition"
                        >{$escaped}</button>
                HTML;

            $tabPanels .= <<<HTML
                        <div x-show="active === '{$escaped}'" class="text-gray-700">
                            {$content}
                        </div>
                HTML;
        }

        $escapedFirst = htmlspecialchars($firstKey, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <div x-data="{ active: '{$escapedFirst}' }">
                <div class="flex border-b border-gray-200 mb-4">
                    {$tabButtons}
                </div>
                {$tabPanels}
            </div>
            HTML;
    }

    /**
     * Génère un champ de recherche avec filtrage en temps réel sur une liste
     *
     * @param array  $items       Liste d'éléments à filtrer
     * @param string $placeholder Texte indicatif du champ
     */
    public function liveSearch(array $items, string $placeholder = 'Rechercher…'): string
    {
        $jsonItems   = json_encode(array_values($items), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $escapedPlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <div x-data="{ query: '', items: {$jsonItems} }">
                <input
                    x-model="query"
                    type="text"
                    placeholder="{$escapedPlaceholder}"
                    class="w-full border border-gray-300 rounded px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <ul class="divide-y divide-gray-100">
                    <template x-for="item in items.filter(i => i.toLowerCase().includes(query.toLowerCase()))" x-bind:key="item">
                        <li x-text="item" class="py-2 px-1 text-gray-700"></li>
                    </template>
                    <li
                        x-show="items.filter(i => i.toLowerCase().includes(query.toLowerCase())).length === 0"
                        class="py-2 px-1 text-gray-400 italic"
                    >Aucun résultat.</li>
                </ul>
            </div>
            HTML;
    }
}

/*
|--------------------------------------------------------------------------
| UTILISATION DANS UN CONTROLLER
|--------------------------------------------------------------------------
|
| $alpine = new \App\Libraries\AlpineExample();
|
| // Compteur (départ 5, min 0, max 10)
| echo $alpine->counter(5, 0, 10);
|
| // Accordéon
| echo $alpine->accordion('Qu\'est-ce qu\'Alpine.js ?', '<p>Un framework JS léger.</p>');
| echo $alpine->accordion('Section ouverte', '<p>Contenu visible dès le chargement.</p>', true);
|
| // Modale
| echo $alpine->modal('Ouvrir la modale', 'Confirmation', '<p>Êtes-vous sûr de vouloir continuer ?</p>');
|
| // Onglets
| echo $alpine->tabs([
|     'Accueil'  => '<p>Contenu de l\'onglet Accueil.</p>',
|     'Profil'   => '<p>Contenu de l\'onglet Profil.</p>',
|     'Réglages' => '<p>Contenu de l\'onglet Réglages.</p>',
| ]);
|
| // Recherche en direct
| echo $alpine->liveSearch(['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Nantes']);
|
|--------------------------------------------------------------------------
| INCLURE ALPINE.JS DANS LA VUE
|--------------------------------------------------------------------------
|
| Via CDN (développement) :
| <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
|
| Via npm (production) :
|   npm install alpinejs
|   import Alpine from 'alpinejs'; Alpine.start();
|
*/
