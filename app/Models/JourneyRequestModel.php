<?php

namespace App\Models;

/**
 * Modèle gérant la table 'journey_request'.
 * Gère la validation des données.
 */
class JourneyRequestModel extends BaseModel
{
    protected $table = 'journey_request';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'start_datetime',
        'seats',
        'message',
        'user_id',
        'location_start_id',
        'location_end_id'

    ];

    protected $validationRules = [
        'start_datetime'    => 'required|valid_date',
        'seats'             => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[8]',
        'message'           => 'permit_empty|max_length[2000]',
        'user_id'           => 'required|integer|is_not_unique[user.id]',
        'location_start_id' => 'required|integer|is_not_unique[location.id]',
        'location_end_id'   => 'required|integer|is_not_unique[location.id]',
    ];

    protected $validationMessages = [
        'start_datetime' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Veuillez renseigner une date valide.',
            'after'      => 'La date de départ doit être dans le futur.',
        ],
        'seats' => [
            'required'              => 'Veuillez renseigner le nombre de places.',
            'greater_than_equal_to' => 'Le trajet doit avoir au moins 1 places.',
            'less_than_equal_to'    => 'Le trajet ne peut pas dépasser 8 places.',
        ],
        'message' => [
            'required'   => 'Le message est obligatoire.',
            'max_length' => 'Le message doit contenir au maximum 2000 caractères.',
        ],
        'location_start_id' => [
            'required'      => 'Le lieu de départ est obligatoire.',
            'integer'       => 'Identifiant du lieu de départ invalide.',
            'is_not_unique' => 'Le lieu de départ sélectionné n\'existe pas.',
        ],
        'location_end_id' => [
            'required'      => 'Le lieu d\'arrivée est obligatoire.',
            'integer'       => 'Identifiant du lieu d\'arrivée invalide.',
            'is_not_unique' => 'Le lieu d\'arrivée sélectionné n\'existe pas.',
        ],
    ];

    /**
     * Récupère une demande de trajet avec toutes ses informations liées :
     * adresses et villes de départ/arrivée, heure.
     *
     * @param int $journeyId Identifiant du trajet
     * @return array|null    Trajet enrichi, ou null si introuvable
     */
    public function findWithDetails(int $id, int $userId): ?array
    {
        return $this->db->table($this->table)
            ->select('journey_request.*,
                loc_start.address as address_start,
                loc_start.latitude as latitude_start,
                loc_start.longitude as longitude_start,
                loc_end.address   as address_end,
                loc_end.latitude   as latitude_end,
                loc_end.longitude  as longitude_end,
                city_start.name   as city_start_name,
                city_start.zipcode as city_start_zipcode,
                city_end.name     as city_end_name,
                city_end.zipcode   as city_end_zipcode')
            ->join('location loc_start', 'loc_start.id = journey_request.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey_request.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey_request.id', $id)
            ->where('journey_request.user_id', $userId)
            ->get()
            ->getRowArray();
    }
}