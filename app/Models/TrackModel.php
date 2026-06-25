<?php

namespace App\Models;

/**
 * Modèle de gestion des tracés de parcours (table `track`).
 *
 * Stocke les tracés GeoJSON issus de l'API de routage, associés
 * aux trajets de covoiturage.
 */
class TrackModel extends BaseModel
{
    protected $table            = 'track';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'geojson',
    ];

    protected $validationRules = [
        'geojson' => 'required|string|valid_json',
    ];

    protected $validationMessages = [
        'geojson' => [
            'required'   => 'Le tracé du parcours est obligatoire.',
            'string'     => 'Le tracé doit être une chaîne de caractères.',
            'valid_json' => 'Le tracé fourni n\'est pas un GeoJSON valide.',
        ],
    ];
}
