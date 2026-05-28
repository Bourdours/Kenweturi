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
    protected \CodeIgniter\Pager\Pager $pager;
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->journeyModel = new JourneyModel();
        $this->bookingModel = new BookingModel();
        $this->reportModel  = new ReportModel();
        $this->pager        = \Config\Services::pager();
        $this->db           = \Config\Database::connect();
    }

    /**
     * Page principale du dashboard.
     * GET /dashboard
     */
    public function show(): string
    {
        $userId = (int) session('user_id');

        $nextJourneys = $this->db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name,
            loc_start.address as address_start, loc_end.address as address_end,
            COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->where('journey.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('journey.start_datetime', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        $lastJourneys = $this->db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name,
            loc_start.address as address_start, loc_end.address as address_end,
            COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0) as booked_seats')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->where('journey.canceled_at', null)
            ->where('journey.start_datetime <', date('Y-m-d H:i:s'))
            ->orderBy('journey.start_datetime', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $nextBookings = $this->db->table('booking')
            ->select('booking.*, journey.start_datetime, journey.seats,
                city_start.name as city_start_name,
                city_end.name as city_end_name,
                loc_start.address as address_start,
                loc_end.address as address_end,
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
            ->join('city city_dropoff',    'city_dropoff.id = loc_dropoff.city_id', 'left')
            ->join('user u',               'u.id = booking.user_id')
            ->where('journey.user_id',     $userId)
            ->where('booking.status', 'pending')
            ->orderBy('booking.sent_at',   'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Réservations faites en tant que passager
        $myBookings = $this->db->table('booking')
            ->select('booking.*, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name, u.firstname as driver_firstname, u.lastname as driver_lastname, u.avatar as driver_avatar, u.is_student as driver_is_student, loc_start.address as address_start, loc_end.address as address_end')
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
            ->limit(5)
            ->get()->getResultArray();

        // Derniers signalements
        $lastReports = $this->db->table('report')
            ->select('report.*, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name,
                loc_start.address as address_start, loc_end.address as address_end')
            ->join('journey',            'journey.id = report.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('report.user_id', $userId)
            ->orderBy('report.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('Dashboard/dashboardShow', [
            'title'        => 'Mon dashboard',
            'nextJourneys' => $nextJourneys,
            'lastJourneys' => $lastJourneys,
            'nextBookings' => $nextBookings,
            'myBookings'   => $myBookings,
            'lastReports'  => $lastReports,
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

        $builder = $this->db->table('journey')
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

        return view('Dashboard/dashboardJourneys', [
            'title'    => $filter === 'past' ? 'Trajets passés' : ($filter === 'upcoming' ? 'Prochains trajets' : 'Mes trajets'),
            'journeys' => $journeys,
            'pager'    => $this->pager,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'filter'   => $filter,
        ]);
    }

    /**
     * Liste paginée des réservations.
     * GET /dashboard/bookings
     */
    public function showBookings(): string
    {
        $userId  = (int) session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $filter  = $this->request->getGet('filter');

        $builder = $this->db->table('booking')
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
            $builder->join('user u',       'u.id = journey.user_id')
                    ->where('booking.user_id', $userId)
                    ->where('booking.status', 'pending')
                    ->orderBy('journey.start_datetime', 'ASC');
            $title = 'Mes réservations';
        } else {
            $builder->join('user u',       'u.id = booking.user_id')
                    ->where('journey.user_id', $userId)
                    ->where('booking.status', 'pending')
                    ->orderBy('booking.sent_at', 'ASC');
            $title = 'Réservations reçues';
        }

        $total    = $builder->countAllResults(false);
        $bookings = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return view('Dashboard/dashboardBookings', [
            'title'    => $title,
            'back'     => $this->request->getGet('back'),
            'bookings' => $bookings,
            'pager'    => $this->pager,
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
        $userId = (int) session('user_id');

        $booking = $this->db->table('booking')
            ->select('booking.*, journey.start_datetime, journey.seats, journey.user_id as driver_id,
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
            ->join('user passenger',       'passenger.id = booking.user_id')
            ->join('user driver',          'driver.id = journey.user_id')
            ->where('booking.id', $id)
            ->groupStart()
                ->where('booking.user_id', $userId)
                ->orWhere('journey.user_id', $userId)
            ->groupEnd()
            ->get()->getRowArray();

        if (!$booking)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        return view('Bookings/bookingShow', [
            'title'    => 'Détail de la réservation',
            'back'     => $this->request->getGet('back'),
            'booking'  => $booking,
            'isDriver' => (int) $booking['driver_id'] === $userId,
        ]);
    }

    /**
     * Liste paginée des signalements effectués par le user.
     * GET /dashboard/reports
     */
    public function showReports(): string
    {
        $userId  = (int) session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $builder = $this->db->table('report')
            ->select('report.*, journey.start_datetime,
                city_start.name as city_start_name,
                city_end.name as city_end_name')
            ->join('journey',            'journey.id = report.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('report.user_id', $userId)
            ->orderBy('report.created_at', 'DESC');

        $total   = $builder->countAllResults(false);
        $reports = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return view('Dashboard/dashboardReports', [
            'title'   => 'Mes signalements',
            'reports' => $reports,
            'pager'   => $this->pager,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Détail d'un signalement.
     * GET /dashboard/reports/:id
     */
    public function showReport(int $id): string|RedirectResponse
    {
        $userId = (int) session('user_id');

        $report = $this->db->table('report')
            ->select('report.*, journey.start_datetime,
                city_start.name as city_start_name,
                city_end.name as city_end_name')
            ->join('journey',            'journey.id = report.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('report.id', $id)
            ->where('report.user_id', $userId)
            ->get()->getRowArray();

        if (!$report)
            return redirect()->to('/dashboard/reports')->with('error', 'Signalement introuvable.');

        return view('Dashboard/dashboardReportShow', [
            'title'  => 'Détail du signalement',
            'back'   => $this->request->getGet('back'),
            'report' => $report,
        ]);
    }
}