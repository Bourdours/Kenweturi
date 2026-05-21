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

        // 3 prochains trajets
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

        // 3 derniers trajets
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

        // 3 dernières réservations reçues sur ses trajets
        $lastBookings = $db->table('booking')
            ->select('booking.*, journey.start_datetime, city_start.name as city_start_name, city_end.name as city_end_name, u.firstname, u.lastname')
            ->join('journey',            'journey.id = booking.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = booking.user_id')
            ->where('journey.user_id', $userId)
            ->orderBy('booking.sent_at', 'DESC')
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
            'lastBookings' => $lastBookings,
            'lastReport'   => $lastReport,
        ]);
    }

    /**
     * Liste paginée de tous les trajets du user.
     * GET /dashboard/journeys
     */
    public function showJourneys(): string
    {
        $userId  = session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $db      = \Config\Database::connect();
        $builder = $db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey.user_id', $userId)
            ->orderBy('journey.start_datetime', 'DESC');

        $total    = $builder->countAllResults(false);
        $journeys = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $pager    = \Config\Services::pager();

        return view('Dashboard/dashboardJourneys', [
            'title'    => 'Mes trajets',
            'journeys' => $journeys,
            'pager'    => $pager,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
        ]);
    }

    /**
     * Liste paginée des réservations reçues en tant que driver.
     * GET /dashboard/bookings
     */
    public function showBookings(): string
    {
        $userId  = session('user_id');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $db      = \Config\Database::connect();
        $builder = $db->table('booking')
            ->select('booking.*, journey.start_datetime,
                city_start.name as city_start_name,
                city_end.name as city_end_name')
            ->join('journey',            'journey.id = booking.journey_id')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('booking.user_id', $userId)
            ->orderBy('journey.start_datetime', 'DESC');

        $total    = $builder->countAllResults(false);
        $bookings = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $pager    = \Config\Services::pager();

        return view('Dashboard/dashboardBookings', [
            'title'    => 'Mes réservations',
            'bookings' => $bookings,
            'pager'    => $pager,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
        ]);
    }

    /**
     * Détail d'une réservation.
     * GET /dashboard/bookings/:id
     */
    public function showBooking(int $id): string|RedirectResponse 
    {

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