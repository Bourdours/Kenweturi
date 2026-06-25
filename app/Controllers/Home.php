<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $asDriver    = null;
        $asPassenger = null;

        if (session()->get('isLoggedIn')) {
            $userId = session()->get('user_id');
            $db     = \Config\Database::connect();
            $now    = date('Y-m-d H:i:s');

            $asDriver = $db->table('journey')
                ->select("journey.id, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name, 'driver' as role")
                ->join('location loc_start', 'loc_start.id = journey.location_start_id')
                ->join('location loc_end',   'loc_end.id = journey.location_end_id')
                ->join('city city_start',    'city_start.id = loc_start.city_id')
                ->join('city city_end',      'city_end.id = loc_end.city_id')
                ->where('journey.user_id', $userId)
                ->where('journey.canceled_at', null)
                ->where('journey.start_datetime >', $now)
                ->orderBy('journey.start_datetime', 'ASC')
                ->limit(1)
                ->get()->getRowArray();

            $asPassenger = $db->table('booking')
                ->select("journey.id, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name, 'passenger' as role")
                ->join('journey',            'journey.id = booking.journey_id')
                ->join('location loc_start', 'loc_start.id = journey.location_start_id')
                ->join('location loc_end',   'loc_end.id = journey.location_end_id')
                ->join('city city_start',    'city_start.id = loc_start.city_id')
                ->join('city city_end',      'city_end.id = loc_end.city_id')
                ->where('booking.user_id', $userId)
                ->where('booking.status', 'accepted')
                ->where('journey.canceled_at', null)
                ->where('journey.start_datetime >', $now)
                ->orderBy('journey.start_datetime', 'ASC')
                ->limit(1)
                ->get()->getRowArray();
        }

        return view('home', [
            'title'            => "Page d'accueil",
            'nextDriverJourney'    => $asDriver    ?? null,
            'nextPassengerJourney' => $asPassenger ?? null,
        ]);
    }

    public function test(int $id, int $id2): string
    {
        return view('test', ["id1" => $id, "id2" => $id2]);
    }
}
