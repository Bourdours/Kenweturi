<?php

namespace App\Models;

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
        'created_at',
        'track_id',
        'user_id',
        'location_start_id',
        'location_end_id'

    ];
}