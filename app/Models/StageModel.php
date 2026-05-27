<?php

namespace App\Models;

class StageModel extends BaseModel
{
    protected $table = 'stage';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'departure_time',
        'position',
        'location_id',
        'journey_id'
    ];

    protected $validationRules = [
        'departure_time' => 'required|valid_date[H:i:s]',
        'position' => 'required|integer|greater_than[0]',
        'location_id' => 'required|integer',
        'journey_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'departure_time' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_time' => 'Veuillez renseigner une date valide.',
        ]
    ];

    /**
     * Récupère les étapes intermédiaires d'un trajet, ordonnées par position.
     *
     *
     * @param int $journeyId Identifiant du trajet
     * @return array         Étapes enrichies (adresse, coordonnées, ville)
     */
    public function findByJourney(int $journeyId): array
    {
        return $this->db->table($this->table)
            ->select('stage.*,
                location.address,
                location.latitude,
                location.longitude,
                city.name as city_name')
            ->join('location', 'location.id = stage.location_id')
            ->join('city',     'city.id = location.city_id')
            ->where('stage.journey_id', $journeyId)
            ->orderBy('stage.position', 'ASC')
            ->get()
            ->getResultArray();
    }
}