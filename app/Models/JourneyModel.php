<?php

namespace App\Models;

/**
 * Modèle pour la gestion des trajets de covoiturage (Adapté à la vue de publication).
 *
 * @package App\Models
 */
class JourneyModel extends BaseModel
{
    protected $table = 'journey';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'start_datetime',
        'seats',
        'note',
        'smoking',
        'canceled_at',
        'track_id',
        'user_id',
        'car_id',
        'location_start_id',
        'location_end_id',
    ];

    protected $validationRules = [
        'start_datetime'    => 'required|valid_date[Y-m-d H:i:s]',
        'seats'             => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[8]',
        'note'              => 'permit_empty|string|max_length[1000]',
        'smoking'           => 'required|in_list[0,1]',
        'canceled_at'       => 'permit_empty|valid_date',
        'track_id'          => 'required|integer|is_not_unique[track.id]',
        'user_id'           => 'required|integer|is_not_unique[user.id]',
        'location_start_id' => 'required|integer|is_not_unique[location.id]',
        'location_end_id'   => 'required|integer|is_not_unique[location.id]|differs[location_start_id]',
        'car_id'            => 'required|integer|is_not_unique[car.id]',
    ];

    protected $validationMessages = [
        'start_datetime' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Veuillez renseigner une date valide.',
        ],
        'seats' => [
            'required'              => 'Veuillez renseigner le nombre de places.',
            'greater_than_equal_to' => 'Le trajet doit avoir au moins 1 place.',
            'less_than_equal_to'    => 'Le trajet ne peut pas dépasser 8 places.',
        ],
        'note' => [
           'max_length' => 'Le message doit contenir au maximum 1000 caractères.',
        ],
        'smoking' => [
            'required' => 'Veuillez indiquer si le covoiturage est fumeur ou non.',
            'in_list'  => 'La valeur sélectionnée n\'est pas valide.',
        ],
        'canceled_at' => [
            'valid_date' => 'La date d\'annulation doit être une date valide.',
        ],
        'track_id' => [
            'required'      => 'L\'identifiant du tracé est obligatoire.',
            'integer'       => 'L\'identifiant du tracé doit être un nombre entier.',
            'is_not_unique' => 'Le tracé spécifié n\'existe pas.',
        ],
        'user_id' => [
            'required'      => 'L\'identifiant de l\'utilisateur est obligatoire.',
            'integer'       => 'L\'identifiant de l\'utilisateur doit être un nombre entier.',
            'is_not_unique' => 'L\'utilisateur spécifié n\'existe pas.',
        ],
        'location_start_id' => [
            'required'      => 'Le lieu de départ est obligatoire.',
            'integer'       => 'Le lieu de départ doit être un identifiant valide.',
            'is_not_unique' => 'Le lieu de départ sélectionné n\'existe pas.',
        ],
        'location_end_id' => [
            'required'      => 'Le lieu d\'arrivée est obligatoire.',
            'integer'       => 'Le lieu d\'arrivée doit être un identifiant valide.',
            'is_not_unique' => 'Le lieu d\'arrivée sélectionné n\'existe pas.',
        ]
    ];

    /**
     * Récupère un trajet avec toutes ses informations liées.
     */
    public function findWithDetails(int $journeyId): ?array
    {
        return $this->db->table($this->table)
            ->select('journey.*,
                loc_start.address   as address_start,
                loc_end.address     as address_end,
                loc_start.latitude  as lat_start,
                loc_start.longitude as lng_start,
                loc_end.latitude    as lat_end,
                loc_end.longitude   as lng_end,
                city_start.name     as city_start_name,
                city_end.name       as city_end_name,
                track.geojson       as track_geojson,
                u.firstname       as driver_firstname,
                u.lastname        as driver_lastname,
                u.id              as driver_id,
                u.avatar          as driver_avatar,
                u.biography       as driver_biography,
                u.is_student      as driver_is_student,
                car.brand         as car_brand,
                car.model         as car_model,
                car.color         as car_color,
                car.seats         as car_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = journey.user_id')
            ->join('car',                'car.id = journey.car_id', 'left')
            ->join('track',              'track.id = journey.track_id', 'left')
            ->where('journey.id', $journeyId)
            ->where('u.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    /**
     * Récupère tous les trajets correspondant aux filtres non géographiques.
     */
    public function findAllWithFilters(array $filters): array
    {
        $builder = $this->db->table($this->table)
            ->select("journey.*,
                loc_start.address as address_start,
                loc_end.address   as address_end,
                city_start.name   as city_start_name,
                city_end.name     as city_end_name,
                city_start.name   as city_boarding_name,
                u.firstname       as driver_firstname,
                u.lastname        as driver_lastname,
                u.avatar          as driver_avatar,
                u.is_student      as driver_is_student,
                track.geojson     as track_geojson,
                COALESCE((SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('lat', loc_s.latitude, 'lng', loc_s.longitude) SEPARATOR ','), ']')
                 FROM stage s JOIN location loc_s ON loc_s.id = s.location_id
                 WHERE s.journey_id = journey.id), '[]') as stages_json,
                (journey.seats - COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id AND b.status = 'accepted'), 0)) as remaining_seats")
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = journey.user_id')
            ->join('track',              'track.id = journey.track_id', 'left')
            ->where('journey.canceled_at', null)
            ->where('u.deleted_at', null)
            ->having('remaining_seats >=', 1)
            ->orderBy('journey.start_datetime', 'ASC');

        if (!empty($filters['filterDate']) && !empty($filters['filterTime'])) {
            $center = strtotime($filters['filterDate'] . ' ' . $filters['filterTime'] . ':00');
            $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s', $center - 1800))
                ->where('journey.start_datetime <=', date('Y-m-d H:i:s', $center + 1800));
        } elseif (!empty($filters['filterDate'])) {
            $builder->where('DATE(journey.start_datetime)', $filters['filterDate']);
            if ($filters['filterDate'] === date('Y-m-d')) {
                $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'));
            }
        } else {
            $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'));
        }

        if (isset($filters['smoking']) && $filters['smoking'] !== null && $filters['smoking'] !== '') {
            $builder->where('journey.smoking', $filters['smoking']);
        }

        return $builder->get()->getResultArray();
    }

    public function findByUserInTimeRange(int $userId, string $fromDateTime, string $toDateTime): ?array
    {
        return $this->where('user_id', $userId)
            ->where('start_datetime >=', $fromDateTime)
            ->where('start_datetime <',  $toDateTime)
            ->first();
    }

    public function findActive(int $id)
    {
        return $this->where('id', $id)
            ->where('canceled_at', null)
            ->first();
    }

    public function getNumberOfSeats(int $id): int
    {
        $row = $this->select('seats')->find($id);
        return $row === null ? 0 : $row['seats'];
    }

    /**
     * Annule tous les trajets actifs d'un utilisateur (soft cancel via canceled_at).
     */
    public function cancelAllByUser(int $userId): void
    {
        $this->where('user_id', $userId)
            ->where('canceled_at', null)
            ->set(['canceled_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Trajets actifs (non annulés) d'un conducteur, joints aux villes de
     * départ/arrivée. Sert à la fois à notifier les passagers et à récupérer
     * les IDs (via array_column) pour le rejet des réservations.
     *
     * @return array<int,array>
     */
    public function findActiveByUserWithCities(int $userId): array
    {
        return $this->db->table($this->table)
            ->select('journey.id, journey.start_datetime,
                    city_start.name as city_start_name,
                    city_end.name   as city_end_name')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->get()
            ->getResultArray();
    }
}
