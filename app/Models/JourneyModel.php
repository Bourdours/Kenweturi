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

    // Ajout des nouveaux champs liés à la récurrence et mise en correspondance des noms
    protected $allowedFields = [
        'start_datetime', // Sera combiné dans le contrôleur via startDate et startTime
        'seats',
        'note',
        'smoking',
        'canceled_at',
        'track_id',
        'user_id',
        'car_id',         // Reçu via le champ 'car' de la vue
        'location_start_id',
        'location_end_id',
        'is_recurring',    // Nouveau champ en BDD basé sur 'isRecurring'
        'recurring_days',  // Stocké en JSON ou chaîne (ex: 'lundi,mardi')
        'recurring_weeks'  // Stocké en JSON ou chaîne (ex: '1,2')
    ];

    // Règles de validation calquées sur les attributs "name" des inputs de la vue
    protected $validationRules = [
        'startDate'         => 'permit_empty',
        'startTime'         => 'permit_empty',
        'seats'             => 'permit_empty',
        'smoking'           => 'permit_empty',
        'car'               => 'permit_empty',
        'startAddress'      => 'permit_empty',
        'endAddress'        => 'permit_empty',
        'isRecurring'       => 'permit_empty',
        'recurringDays'     => 'permit_empty',
        'recurringWeeks'    => 'permit_empty',
    ];

    protected $validationMessages = [
        'startDate' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Le format de la date de départ n\'est pas valide.',
        ],
        'startTime' => [
            'required'   => 'Veuillez renseigner une heure de départ.',
            'valid_date' => 'Le format de l\'heure de départ n\'est pas valide.',
        ],
        'seats' => [
            'required'                 => 'Veuillez renseigner le nombre de places.',
            'greater_than_equal_to[1]' => 'Le trajet doit avoir au moins 1 place.',
            'less_than_equal_to[9]'    => 'Le trajet ne peut pas dépasser 9 places.',
        ],
        'smoking' => [
            'required' => 'Veuillez indiquer si le covoiturage est fumeur ou non.',
            'in_list'  => 'La valeur sélectionnée pour l\'option fumeur n\'est pas valide.',
        ],
        'car' => [
            'required'      => 'Veuillez sélectionner un véhicule.',
            'is_not_unique' => 'Le véhicule sélectionné n\'existe pas.',
        ],
        'startAddress' => [
            'required'   => 'L\'adresse de départ est obligatoire.',
            'min_length' => 'L\'adresse de départ semble trop courte.',
        ],
        'endAddress' => [
            'required'   => 'L\'adresse d\'arrivée est obligatoire.',
            'min_length' => 'L\'adresse d\'arrivée semble trop courte.',
        ],
        'note' => [
           'max_length' => 'Le message doit contenir au maximum 1000 caractères.',
        ],
        'isRecurring' => [
            'required' => 'Veuillez spécifier si le trajet est récurrent.',
            'in_list'  => 'Option de récurrence invalide.',
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
}