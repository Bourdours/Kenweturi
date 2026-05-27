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