<?php

namespace App\Services;

use App\Models\JourneyModel;
use App\Models\TrackModel;
use App\Models\LocationModel;
use App\Models\StageModel;
use App\Models\CityModel;
use App\Models\CarModel;
use App\Models\UserModel;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

use App\Services\RoutingService;
use App\Services\GeocodingService;

use DateTimeImmutable;


class CreateJourneyService
{
    protected JourneyModel $journeyModel;
    protected TrackModel $trackModel;
    protected LocationModel $locationModel;
    protected StageModel $stageModel;
    protected CityModel $cityModel;
    protected CarModel $carModel;
    protected UserModel $userModel;

    protected RoutingService $routingService;
    protected GeocodingService $geocodingService;

    public function __construct()
    {
        $this->journeyModel         = new JourneyModel();
        $this->trackModel           = new TrackModel();
        $this->locationModel        = new LocationModel();
        $this->stageModel           = new StageModel();
        $this->cityModel            = new CityModel();
        $this->carModel             = new CarModel();
        $this->userModel            = new UserModel();

        $this->routingService       = new RoutingService();
        $this->geocodingService     = new GeocodingService();
    }

    public function fetchAllLocationsData(array $addresses): array
    {
        $locationsData = [];
        $errors = [];

        foreach ($addresses as $key => $address) {
            $data = $this->geocodingService->getLocationData($address);
            if (!empty($address)) {
                if ($data === null) throw new ExternalApiException("Adresse introuvable: $address");
                if (empty($data['street']) && empty($data['locality'])) {
                    $errors[$key . 'Address'] = "L'adresse \"$address\" doit contenir un nom de rue.";
                    continue;
                }
                $locationsData[$key] = $data;
            }
        }

        if (!empty($errors)) {
            throw new AddressValidationException($errors);
        }

        return $locationsData;
    }

    public function fetchTrackOrFail(array $locationsData): string
    {
        $locationsCoordinates = [];
        foreach ($locationsData as $location) {
            $locationsCoordinates[] = [$location['latitude'], $location['longitude']];
        }

        $geoJsonTrack = $this->routingService->getTrack($locationsCoordinates);

        if ($geoJsonTrack === null) {
            throw new ExternalApiException('Calcul du trace impossible.');
        }

        return $geoJsonTrack;
    }

    public function persistJourney(int $userId, array $createFormData, array $locationsData, string $geoJsonTrack): array
    {
        $locationEntities         = $this->buildLocationEntities($locationsData);
        $journeyStartDateTime     = $this->buildJourneyStartDateTime($createFormData['journey']);
        $stagesDeparturesDateTime = $this->computeStageDepartures($createFormData['journey'], $geoJsonTrack);

        $journeyForm = $createFormData['journey'];
        $isRecurring = !empty($journeyForm['isRecurring']);

        $targetDates = [];

        if ($isRecurring) {
            $targetDates = $this->computeRecurringDates(
                $journeyForm['startDate'],
                $journeyForm['recurringDays'],
                $journeyForm['recurringWeeks']
            );
        }

        if (empty($targetDates)) {
            $targetDates = [$journeyStartDateTime->format('Y-m-d')];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $trackId = $this->trackModel->insert(['geojson' => $geoJsonTrack]);

        $locationsId = [];
        foreach ($locationEntities as $location) {
            $locationsId[] = $this->locationModel->insert($location);
        }
        $locationStartId = array_shift($locationsId);
        $locationEndId   = array_pop($locationsId);

        $journeyIds = [];

        foreach ($targetDates as $date) {
            $startDateTimeStr = $date . ' ' . $journeyStartDateTime->format('H:i:s');

            $journeyEntity = [
                'start_datetime'    => $startDateTimeStr,
                'seats'             => $journeyForm['seats'],
                'note'              => $journeyForm['note'],
                'smoking'           => $journeyForm['smoking'],
                'car_id'            => $journeyForm['car'],
                'track_id'          => $trackId,
                'user_id'           => $userId,
                'location_start_id' => $locationStartId,
                'location_end_id'   => $locationEndId,
            ];

            $journeyId    = $this->journeyModel->insert($journeyEntity);
            $journeyIds[] = $journeyId;

            for ($i = 0; $i < count($locationsId); $i++) {
                $this->stageModel->insert([
                    'departure_time' => $stagesDeparturesDateTime[$i]->format('H:i:s'),
                    'location_id'    => $locationsId[$i],
                    'journey_id'     => $journeyId,
                    'position'       => $i + 1,
                ]);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Transaction echouee lors de la creation des trajets.');
        }

        return [
            'journeyIds' => $journeyIds,
            'count'      => count($journeyIds),
        ];
    }

    public function buildPreviewData(int $userId, array $createFormData, array $locationsData, string $geoJsonTrack): array
    {
        $journeyForm = $createFormData['journey'];
        $isRecurring = !empty($journeyForm['isRecurring']);

        if ($isRecurring) {
            $dates = $this->computeRecurringDates(
                $journeyForm['startDate'],
                $journeyForm['recurringDays'],
                $journeyForm['recurringWeeks']
            );

            $formatter = new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );

            return [
                'isRecurring'    => true,
                'recurringDates' => array_map(
                    fn($d) => $formatter->format(new \DateTime($d)),
                    $dates
                ),
                'recurringCount' => count($dates),
                'startTime'      => $journeyForm['startTime'],
                'journey'        => null,
                'stages'         => [],
                'remainingSeats' => 0,
            ];
        }

        $user = $this->userModel->find($userId);
        $car  = $this->carModel->find($createFormData['journey']['car']);

        $startDateTime = $this->buildJourneyStartDateTime($createFormData['journey']);

        $geoData       = json_decode($geoJsonTrack, true);
        $segments      = $geoData['features'][0]['properties']['segments'] ?? [];
        $totalDuration = (int) array_sum(array_column($segments, 'duration'));
        $endDateTime   = $startDateTime->modify('+' . $totalDuration . ' seconds');

        $stageDepartures = $this->computeStageDepartures($createFormData['journey'], $geoJsonTrack);

        $locStart = $locationsData['start'];
        $locEnd   = $locationsData['end'];

        $journey = [
            'id'                => null,
            'user_id'           => $userId,
            'driver_id'         => $userId,
            'start_datetime'    => $startDateTime->format('Y-m-d H:i:s'),
            'end_datetime'      => $endDateTime->format('Y-m-d H:i:s'),
            'seats'             => $journeyForm['seats'],
            'note'              => $journeyForm['note'],
            'smoking'           => $journeyForm['smoking'],
            'city_start_name'   => $locStart['city'],
            'address_start'     => $locStart['name'],
            'lat_start'         => $locStart['latitude'],
            'lng_start'         => $locStart['longitude'],
            'city_end_name'     => $locEnd['city'],
            'address_end'       => $locEnd['name'],
            'lat_end'           => $locEnd['latitude'],
            'lng_end'           => $locEnd['longitude'],
            'driver_firstname'  => $user['firstname'],
            'driver_lastname'   => $user['lastname'],
            'driver_avatar'     => $user['avatar'] ?? null,
            'driver_is_student' => $user['is_student'] ?? false,
            'car_brand'         => $car['brand'],
            'car_model'         => $car['model'],
            'car_color'         => $car['color'],
            'track_geojson'     => $geoJsonTrack,
            'canceled_at'       => null,
        ];

        $stages     = [];
        $stageIndex = 0;
        foreach ($locationsData as $key => $loc) {
            if (!str_starts_with($key, 'stage')) continue;
            $stages[] = [
                'city_name'      => $loc['city'],
                'address'        => $loc['name'],
                'latitude'       => $loc['latitude'],
                'longitude'      => $loc['longitude'],
                'departure_time' => $stageDepartures[$stageIndex]->format('H:i:s'),
            ];
            $stageIndex++;
        }

        return [
            'isRecurring'    => false,
            'journey'        => $journey,
            'stages'         => $stages,
            'remainingSeats' => (int) $journeyForm['seats'],
        ];
    }

    private function computeRecurringDates(string $startDate, array $days, array $weeks): array
    {
        $offsets = [
            'lundi'    => 0,
            'mardi'    => 1,
            'mercredi' => 2,
            'jeudi'    => 3,
            'vendredi' => 4,
        ];

        $anchor = new \DateTime($startDate);
        $dowNum = (int) $anchor->format('N');
        $anchor->modify('-' . ($dowNum - 1) . ' days');

        $dates = [];

        foreach ($weeks as $week) {
            $weekOffset = ((int) $week - 1) * 7;
            foreach ($days as $day) {
                if (!isset($offsets[$day])) continue;
                $date = clone $anchor;
                $date->modify('+' . ($weekOffset + $offsets[$day]) . ' days');
                $dates[] = $date->format('Y-m-d');
            }
        }

        $dates = array_unique($dates);
        sort($dates);

        return $dates;
    }

    private function buildLocationEntities(array $locationsData): array
    {
        $locationEntities = [];

        foreach ($locationsData as $location) {
            $cityId = $this->cityModel->findOrCreateCity($location['city'], $location['postcode']);
            $locationEntities[] = [
                'longitude' => $location['longitude'],
                'latitude'  => $location['latitude'],
                'address'   => $location['name'],
                'note'      => '',
                'city_id'   => $cityId,
            ];
        }

        return $locationEntities;
    }

    private function buildJourneyStartDateTime(array $journeyData): DateTimeImmutable
    {
        $journeyStartTimeArray = explode(':', $journeyData['startTime']);
        $journeyStartHour      = (int) $journeyStartTimeArray[0];
        $journeyStartMinute    = (int) $journeyStartTimeArray[1];

        return (new DateTimeImmutable($journeyData['startDate']))
            ->setTime($journeyStartHour, $journeyStartMinute, 0);
    }

    private function computeStageDepartures(array $journeyData, string $geoJsonTrack): array
    {
        $data     = json_decode($geoJsonTrack, true);
        $segments = $data['features'][0]['properties']['segments'] ?? [];

        if (empty($segments)) {
            throw new ExternalApiException('Trace GeoJSON invalide: segments manquants.');
        }

        if (count($segments) === 1) {
            return [];
        }

        $departure                = $this->buildJourneyStartDateTime($journeyData);
        $stagesDeparturesDateTime = [];

        for ($i = 0; $i < count($segments) - 1; $i++) {
            $departure                  = $departure->modify('+' . (int) $segments[$i]['duration'] . ' seconds');
            $stagesDeparturesDateTime[] = $departure;
        }

        return $stagesDeparturesDateTime;
    }
}
