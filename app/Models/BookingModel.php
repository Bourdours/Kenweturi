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
}