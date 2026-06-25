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
                COALESCE(loc_pickup.latitude,  loc_start.latitude)  as lat_pickup,
                COALESCE(loc_pickup.longitude, loc_start.longitude) as lng_pickup,
                loc_dropoff.address as dropoff_address,
                city_dropoff.name as dropoff_city_name,
                COALESCE(loc_dropoff.latitude,  loc_end.latitude)  as lat_dropoff,
                COALESCE(loc_dropoff.longitude, loc_end.longitude) as lng_dropoff,
                track.geojson as track_geojson,
                passenger.firstname as passenger_firstname,
                passenger.lastname as passenger_lastname,
                passenger.email as passenger_email,
                passenger.avatar as passenger_avatar,
                passenger.is_student as passenger_is_student,
                driver.firstname as driver_firstname,
                driver.lastname as driver_lastname,
                driver.email as driver_email,
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
            ->join('track',                'track.id = journey.track_id', 'left')
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

    /**
     * Récupère les reservations envoyées (en cours) pour un trajet.
     *
     * @param int $journeyId Identifiant du trajet
     * @return array         nombres de reservation envoyées 
     */
    public function countPendingBookings(int $journeyId): int
    {
        return $this->where('journey_id', $journeyId)
            ->where('status', 'pending')
            ->countAllResults();
    }

    /**
     * Récupère la réservation d'un utilisateur donné sur un trajet donné (s'il en a une).
     *
     * @param  int $journeyId Identifiant du trajet
     * @param  int $userId    Identifiant du passager
     * @return array|null     Réservation, ou null si l'utilisateur n'a pas réservé ce trajet
     */
    public function findUserBooking(int $journeyId, int $userId): ?array
    {
        return $this->where('journey_id', $journeyId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Compte le nombre de réservations en attente pour chaque trajet d'une liste.
     *
     * @param  int[] $journeyIds Identifiants des trajets à inspecter
     * @return array<int,int>   Tableau indexé par journey_id, valeur = nombre de demandes 'pending'
     *                          (les trajets sans demande en attente ne figurent pas dans le tableau)
     */
    public function countPendingByJourneys(array $journeyIds): array
    {
        if (empty($journeyIds)) {
            return [];
        }

        $rows = $this->select('journey_id, COUNT(*) as pending_bookings')
            ->where('status', 'pending')
            ->whereIn('journey_id', $journeyIds)
            ->groupBy('journey_id')
            ->findAll();

        return array_column($rows, 'pending_bookings', 'journey_id');
    }

    public function countByStatus(string $label, int $journeyId): int
    {

        return $this->where('journey_id', $journeyId)
            ->where('status', $label)
            ->countAllResults();
    }

    /**
     * Refuse toutes les réservations d'un passager (filtrées par booking.user_id).
     */
    public function rejectAllByPassenger(int $userId): void
    {
        $this->where('user_id', $userId)
            ->whereIn('status', ['pending', 'accepted'])
            ->set(['status' => 'rejected'])
            ->update();
    }

    /**
     * Refuse toutes les réservations (en attente ou acceptées) d'une liste de trajets.
     *
     * @param int[] $journeyIds
     */
    public function rejectAllForJourneys(array $journeyIds): void
    {
        if ($journeyIds === []) {
            return;
        }

        $this->whereIn('journey_id', $journeyIds)
            ->whereIn('status', ['pending', 'accepted'])
            ->set(['status' => 'rejected'])
            ->update();
    }

    /**
     * Réservations actives (en attente ou acceptées) d'un passager, enrichies
     * des infos nécessaires pour prévenir les conducteurs de l'annulation.
     *
     * @return array<int,array>
     */
    public function findActiveByPassengerWithDetails(int $userId): array
    {
        return $this->db->table('booking')
            ->select('booking.id, booking.journey_id, booking.status,
                    journey.start_datetime,
                    city_start.name     as city_start_name,
                    city_end.name       as city_end_name,
                    passenger.firstname as passenger_firstname,
                    passenger.lastname  as passenger_lastname,
                    driver.id           as driver_id,
                    driver.email        as driver_email,
                    driver.firstname    as driver_firstname')
            ->join('journey',            'journey.id = booking.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user passenger',     'passenger.id = booking.user_id')
            ->join('user driver',        'driver.id = journey.user_id')
            ->where('booking.user_id', $userId)
            ->whereIn('booking.status', ['pending', 'accepted'])
            ->get()
            ->getResultArray();
    }

    /**
     * Passagers acceptés sur une liste de trajets, enrichis des infos nécessaires
     * au mail d'annulation. Lecture pré-capture (avant rejet).
     *
     * @param int[] $journeyIds
     * @return array<int,array>
     */
    public function findAcceptedNotificationsForJourneys(array $journeyIds): array
    {
        if ($journeyIds === []) {
            return [];
        }

        return $this->db->table('booking')
            ->select('passenger.id      as user_id,
                    passenger.email     as email,
                    passenger.firstname as firstname,
                                        journey.start_datetime,
                    city_start.name     as city_start_name,
                    city_end.name       as city_end_name')
            ->join('journey',            'journey.id = booking.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user passenger',     'passenger.id = booking.user_id')
            ->whereIn('booking.journey_id', $journeyIds)
            ->where('booking.status', 'accepted')
            ->get()->getResultArray();
    }
}
