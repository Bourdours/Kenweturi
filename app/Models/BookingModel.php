<?php 

namespace App\Models;

class BookingModel extends BaseModel
{
    protected $table = 'booking';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'booking_date',
        'seat_numbers',
        'status',
        'journey_id',
        'user_id'
    ];

    protected $validationRules = [
        'booking_date' => 'required|valid_date',
        'seat_numbers' => 'required|integer|greater_than[0]|less_than_equal_to[8]',
        'journey_id' => 'required|integer',
        'user_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'booking_date' => [
            'required'   => 'Veuillez renseigner une date de départ.',
            'valid_date' => 'Veuillez renseigner une date valide.',
        ],
        'seat_numbers' => [
            'required' => 'Veuillez renseigner un nombre de siège.',
            'integer' => 'Veuillez renseigner un chiffre.',
            'greater_than' => 'Le chiffre doit être supérieur à 0.',
            'less_than_equal_to' => 'le chiffre doit être inférieur ou égale à 8.'
        ],
    ];

    /**
     * Calcule le nombre de places encore disponibles sur un trajet.
     *
     * @param int $journeyId  Identifiant du trajet
     * @param int $totalSeats Nombre total de places proposées par le conducteur
     * @return int            Places restantes (>= 0)
     */
    public function countRemainingSeats(int $journeyId, int $totalSeats): int
    {
        $bookedSeats = $this->selectSum('seat_numbers')
            ->where('journey_id', $journeyId)
            ->where('status', 'accepted')
            ->get()
            ->getRowArray();

        return max(0, $totalSeats - (int) ($bookedSeats['seat_numbers'] ?? 0));
    }

    /**
     * Récupère le détail d'une réservation avec toutes les informations associées.
     * Vérifie que l'utilisateur est bien le passager ou le conducteur du trajet.
     */
    public function findWithDetails(int $id, int $userId): ?array
    {
        return $this->db->table('booking')
            ->select("booking.*, journey.start_datetime, journey.seats, journey.user_id as driver_id,
                loc_start.address as start_address,
                city_start.name as city_start_name,
                loc_end.address as end_address,
                city_end.name as city_end_name,
                loc_pickup.address as pickup_address,
                city_pickup.name as pickup_city_name,
                loc_dropoff.address as dropoff_address,
                city_dropoff.name as dropoff_city_name,
                passenger.firstname as passenger_firstname,
                passenger.lastname as passenger_lastname,
                passenger.avatar as passenger_avatar,
                passenger.is_student as passenger_is_student,
                driver.firstname as driver_firstname,
                driver.lastname as driver_lastname,
                driver.avatar as driver_avatar,
                driver.is_student as driver_is_student,
                COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id AND b.status = 'accepted'), 0) as booked_seats")
            ->join('journey',              'journey.id = booking.journey_id')
            ->join('location loc_start',   'loc_start.id = journey.location_start_id')
            ->join('location loc_end',     'loc_end.id = journey.location_end_id')
            ->join('city city_start',      'city_start.id = loc_start.city_id')
            ->join('city city_end',        'city_end.id = loc_end.city_id')
            ->join('location loc_pickup',  'loc_pickup.id = booking.location_pickup_id', 'left')
            ->join('city city_pickup',     'city_pickup.id = loc_pickup.city_id', 'left')
            ->join('location loc_dropoff', 'loc_dropoff.id = booking.location_dropoff_id', 'left')
            ->join('city city_dropoff',    'city_dropoff.id = loc_dropoff.city_id', 'left')
            ->join('user passenger',       'passenger.id = booking.user_id')
            ->join('user driver',          'driver.id = journey.user_id')
            ->where('booking.id', $id)
            ->groupStart()
                ->where('booking.user_id', $userId)
                ->orWhere('journey.user_id', $userId)
            ->groupEnd()
            ->get()->getRowArray() ?: null;
    }

    /**
     * Récupère les passagers ayant réservé un trajet.
     *
     * @param int $journeyId Identifiant du trajet
     * @return array         Liste des passagers avec leurs informations
     */
    public function findPassengersByJourney(int $journeyId): array
    {
        return $this->select('booking.*, user.firstname, user.lastname, user.avatar, user.is_student')
            ->join('user', 'user.id = booking.user_id')
            ->where('booking.journey_id', $journeyId)
            ->where('booking.status', 'accepted')
            ->findAll();
    }
    
}