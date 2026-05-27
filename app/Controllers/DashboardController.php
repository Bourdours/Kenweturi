<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JourneyModel;
use App\Models\BookingModel;
use App\Models\ReportModel;
use CodeIgniter\HTTP\RedirectResponse;

class DashboardController extends BaseController
{
    protected JourneyModel $journeyModel;
    protected BookingModel $bookingModel;
    protected ReportModel  $reportModel;

    public function __construct()
    {
        $this->journeyModel = new JourneyModel();
        $this->bookingModel = new BookingModel();
        $this->reportModel  = new ReportModel();
    }

    /**
     * Page principale du dashboard.
     * GET /dashboard
     */
    public function show(): string
    {
        $userId = (int) session('user_id');
        $db     = \Config\Database::connect();

        // prochains trajets
        $nextJourneys = $db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name,
            COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->where('journey.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('journey.start_datetime', 'ASC')
            ->limit(1)
            ->get()->getResultArray();

        // derniers trajets
        $lastJourneys = $db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name,
            COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->where('journey.start_datetime <', date('Y-m-d H:i:s'))
            ->orderBy('journey.start_datetime', 'DESC')
            ->limit(1)
            ->get()->getResultArray();

        // réservation faite sur mon trajet
        $nextBookings = $db->table('booking')
            ->select('booking.*, journey.start_datetime, journey.seats,
                city_start.name as city_start_name,
                city_end.name as city_end_name,
                loc_pickup.address as pickup_address,
                city_pickup.name as pickup_city_name,
                loc_dropoff.address as dropoff_address,
                city_dropoff.name as dropoff_city_name,
                u.firstname as passenger_firstname,
                u.lastname as passenger_lastname,
                u.avatar as passenger_avatar,
                u.is_student as passenger_is_student,
                COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('journey',              'journey.id = booking.journey_id')
            ->join('location loc_start',   'loc_start.id = journey.location_start_id')
            ->join('location loc_end',     'loc_end.id = journey.location_end_id')
            ->join('city city_start',      'city_start.id = loc_start.city_id')
            ->join('city city_end',        'city_end.id = loc_end.city_id')
            ->join('location loc_pickup',  'loc_pickup.id = booking.location_pickup_id', 'left')
            ->join('city city_pickup',     'city_pickup.id = loc_pickup.city_id', 'left')
            ->join('location loc_dropoff', 'loc_dropoff.id = booking.location_dropoff_id', 'left')
            ->join('city city_dropoff',    'city_dropoff.id = loc_dropoff.city_id', 'left')
            ->join('user u',               'u.id = booking.user_id')
            ->where('journey.user_id',     $userId)
            ->orderBy('booking.sent_at',   'DESC')
            ->limit(1)
            ->get()->getResultArray();

        // Réservations faites en tant que passager
        $myBookings = $db->table('booking')
            ->select('booking.*, journey.start_datetime,
                city_start.name as city_start_name,
                city_end.name as city_end_name,
                u.firstname as driver_firstname,
                u.lastname as driver_lastname,
                u.avatar as driver_avatar,
                u.is_student as driver_is_student')
            ->join('journey',            'journey.id = booking.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = journey.user_id')
            ->where('booking.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->where('journey.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('journey.start_datetime', 'ASC')
            ->limit(1)
            ->get()->getResultArray();

        // Dernier signalement
        $lastReport = $db->table('report')
            ->select('report.*, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name')
            ->join('journey',            'journey.id = report.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('report.user_id', $userId)
            ->orderBy('report.created_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return view('Dashboard/dashboardShow', [
            'title'        => 'Mon dashboard',
            'nextJourneys' => $nextJourneys,
            'lastJourneys' => $lastJourneys,
            'nextBookings' => $nextBookings,
            'myBookings'   => $myBookings,
            'lastReport'   => $lastReport,
        ]);
    }

    /**
     * Liste paginée de tous les trajets du user.
     * GET /dashboard/journeys
     */
    public function showJourneys(): string
    {
        $userId  = (int) session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $filter  = $this->request->getGet('filter');
        $perPage = 10;

        $db      = \Config\Database::connect();
        $builder = $db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name,
                COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId);

        if ($filter === 'upcoming') {
            $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'))
                    ->where('journey.canceled_at', null)
                    ->orderBy('journey.start_datetime', 'ASC');
        } elseif ($filter === 'past') {
            $builder->where('journey.start_datetime <', date('Y-m-d H:i:s'))
                    ->orderBy('journey.start_datetime', 'DESC');
        } else {
            $builder->orderBy('journey.start_datetime', 'DESC');
        }

        $total    = $builder->countAllResults(false);
        $journeys = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $pager    = \Config\Services::pager();

        return view('Dashboard/dashboardJourneys', [
            'title'    => $filter === 'past' ? 'Trajets passés' : ($filter === 'upcoming' ? 'Prochains trajets' : 'Mes trajets'),
            'journeys' => $journeys,
            'pager'    => $pager,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'filter'   => $filter,
        ]);
    }

    /**
     * Liste paginée des réservations reçues en tant que driver.
     * GET /dashboard/bookings
     */
    public function showBookings(): string
    {
        $userId  = (int) session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $filter  = $this->request->getGet('filter');
        $db      = \Config\Database::connect();

        $builder = $db->table('booking')
            ->select('booking.*, journey.start_datetime, journey.seats,
                city_start.name as city_start_name,
                city_end.name as city_end_name,
                loc_pickup.address as pickup_address,
                city_pickup.name as pickup_city_name,
                loc_dropoff.address as dropoff_address,
                city_dropoff.name as dropoff_city_name,
                u.firstname as person_firstname,
                u.lastname as person_lastname,
                u.avatar as person_avatar,
                u.is_student as person_is_student,
                COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('journey',              'journey.id = booking.journey_id')
            ->join('location loc_start',   'loc_start.id = journey.location_start_id')
            ->join('location loc_end',     'loc_end.id = journey.location_end_id')
            ->join('city city_start',      'city_start.id = loc_start.city_id')
            ->join('city city_end',        'city_end.id = loc_end.city_id')
            ->join('location loc_pickup',  'loc_pickup.id = booking.location_pickup_id', 'left')
            ->join('city city_pickup',     'city_pickup.id = loc_pickup.city_id', 'left')
            ->join('location loc_dropoff', 'loc_dropoff.id = booking.location_dropoff_id', 'left')
            ->join('city city_dropoff',    'city_dropoff.id = loc_dropoff.city_id', 'left');

        if ($filter === 'mine') {
            $builder->join('user u',          'u.id = journey.user_id')  // driver
                    ->where('booking.user_id', $userId)
                    ->orderBy('journey.start_datetime', 'ASC');
            $title = 'Mes réservations';
        } else {
            $builder->join('user u',          'u.id = booking.user_id')  // passager
                    ->where('journey.user_id', $userId)
                    ->orderBy('booking.sent_at', 'ASC');
            $title = 'Réservations reçues';
        }

        $total    = $builder->countAllResults(false);
        $bookings = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $pager    = \Config\Services::pager();

        $back = $this->request->getGet('back');

        return view('Dashboard/dashboardBookings', [
            'title'    => $title,
            'back'     => $back,
            'bookings' => $bookings,
            'pager'    => $pager,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'filter'   => $filter,
        ]);
    }

    /**
     * Détail d'une réservation.
     * GET /dashboard/bookings/:id
     */
    public function showBooking(int $id): string|RedirectResponse 
    {
        $userId  = (int) session('user_id');
        $db     = \Config\Database::connect();

        $booking = $db->table('booking')
            ->select('booking.*, journey.start_datetime, journey.seats, journey.user_id as driver_id,
                city_start.name as city_start_name,
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
                COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('journey',              'journey.id = booking.journey_id')
            ->join('location loc_start',   'loc_start.id = journey.location_start_id')
            ->join('location loc_end',     'loc_end.id = journey.location_end_id')
            ->join('city city_start',      'city_start.id = loc_start.city_id')
            ->join('city city_end',        'city_end.id = loc_end.city_id')
            ->join('location loc_pickup',  'loc_pickup.id = booking.location_pickup_id', 'left')
            ->join('city city_pickup',     'city_pickup.id = loc_pickup.city_id', 'left')
            ->join('location loc_dropoff', 'loc_dropoff.id = booking.location_dropoff_id', 'left')
            ->join('city city_dropoff',    'city_dropoff.id = loc_dropoff.city_id', 'left')
            ->join('user passenger', 'passenger.id = booking.user_id')
            ->join('user driver',    'driver.id = journey.user_id')
            ->where('booking.id', $id)
            ->groupStart()
                ->where('booking.user_id', $userId)
                ->orWhere('journey.user_id', $userId)
            ->groupEnd()
            ->get()->getRowArray();

        if (!$booking)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        $isDriver = (int) $booking['driver_id'] === $userId;

        $back = $this->request->getGet('back');
        
        return view('Bookings/bookingShow', [
            'title'    => 'Détail de la réservation',
            'back'     => $back,
            'booking'  => $booking,
            'isDriver' => $isDriver,
        ]);
    }

    /**
     * Liste paginée des signalements effectués par le user.
     * GET /dashboard/reports
     */
    public function showReports(): string { }

    /**
     * Détail d'un signalement.
     * GET /dashboard/reports/:id
     */
    public function showReport(int $id): string|RedirectResponse { }
}