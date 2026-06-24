<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

use App\Models\CarModel;
use App\Models\JourneyModel;

use App\Services\JourneyService;
use App\Services\CreateJourneyService;
use App\Services\JourneySearchService;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

class JourneyController extends BaseController{

    protected CarModel $carModel;
    protected JourneyModel $journeyModel;
    protected JourneyService $journeyService;
    protected CreateJourneyService $createJourneyService;
    protected JourneySearchService $journeySearchService;

    public function __construct(){

        $this->carModel = new CarModel();
        $this->journeyModel = new JourneyModel();
        $this->journeyService = new JourneyService();
        $this->createJourneyService = new CreateJourneyService();
        $this->journeySearchService = new JourneySearchService();
    }

    /**
     * Affiche le formulaire de création d'un nouveau trajet.
     *
     * Récupère la liste des voitures appartenant à l'utilisateur connecté
     * pour permettre la sélection d'un véhicule dans le formulaire.
     *
     * @return string Vue HTML du formulaire de création de trajet
     */
    public function showCreateForm()
    {

        $userId = session('user_id');

        $userCars = $this->carModel->findByUser($userId);

        return view('Journeys/newJourney', [
            'title' => "Publier un trajet",
            'cars' => $userCars,
        ]);
    }

    /**
     * Traite la soumission du formulaire de création d'un trajet.
     *
     * Valide les données, géocode les adresses et calcule le tracé via les APIs
     * externes, puis stocke l'ensemble en session et redirige vers la page de
     * confirmation. La persistance en base n'a lieu qu'après confirmation.
     *
     * @return RedirectResponse Redirection vers la prévisualisation ou
     *                          retour au formulaire avec les erreurs
     */
    public function create(): RedirectResponse
    {
        $userId = session('user_id');

        // ====== Validation des données du formulaire
        $carId                    = (int) $this->request->getPost('car');
        $maxSeats                 = $this->journeyService->getMaxSeatsForCar($carId, $userId);
        $createValidationRules    = $this->getCreateValidationRules($maxSeats);
        $createValidationMessages = $this->getCreateValidationMessages($maxSeats);

        if (!$this->validate($createValidationRules, $createValidationMessages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ====== Récupération des données du formulaire
        $createFormData = $this->getCreateFormData();
        $rawPost        = $this->request->getPost();

        // ====== Géocodage + calcul du tracé (sans persistance)
        try {
            $locationsData = $this->createJourneyService->fetchAllLocationsData($createFormData['location']);
            $geoJsonTrack  = $this->createJourneyService->fetchTrackOrFail($locationsData);
        } catch (ExternalApiException $e) {
            return redirect()->back()->withInput()
                ->with('errors', ['api' => 'Service de cartographie indisponible, réessayez plus tard.']);
        } catch (AddressValidationException $e) {
            return redirect()->back()->withInput()
                ->with('errors', $e->getErrors());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('errors', ['db' => 'Une erreur est survenue.']);
        }

        // ====== Stockage en session pour la page de confirmation
        session()->set('journey_preview', [
            'rawPost'        => $rawPost,
            'createFormData' => $createFormData,
            'locationsData'  => $locationsData,
            'geoJsonTrack'   => $geoJsonTrack,
        ]);

        return redirect()->to('/journeys/preview');
    }

    /**
     * Affiche la page de prévisualisation du trajet avant publication.
     *
     * Lit les données géocodées stockées en session par create() et les
     * transforme en un tableau compatible avec la vue de détail, sans aucun
     * accès à la base de données pour le trajet lui-même.
     *
     * @return string|RedirectResponse Vue de prévisualisation ou redirection
     *                                 vers le formulaire si la session est absente
     */
    public function showPreview(): string|RedirectResponse
    {
        $preview = session()->get('journey_preview');
        if (!$preview) {
            return redirect()->to('/journeys/new');
        }

        $userId      = (int) session('user_id');
        $previewData = $this->createJourneyService->buildPreviewData(
            $userId,
            $preview['createFormData'],
            $preview['locationsData'],
            $preview['geoJsonTrack']
        );

        return view('Journeys/journeyPreview', [
            'title' => 'Confirmer le trajet',
            ...$previewData,
        ]);
    }

    /**
     * Confirme la publication du trajet : persiste les données en session en base.
     *
     * Si le trajet est récurrent, persiste N trajets indépendants et redirige
     * vers le dashboard avec un message récapitulatif. Si le trajet est unique,
     * redirige vers le dashboard avec un message de confirmation simple.
     *
     * @return RedirectResponse Redirection vers le dashboard ou vers le formulaire
     *                          en cas d'erreur de persistance
     */
    public function confirm(): RedirectResponse
    {
    $preview = session()->get('journey_preview');
    if (!$preview) {
        return redirect()->to('/journeys/new');
    }

    $userId = (int) session('user_id');

    try {
        $result = $this->createJourneyService->persistJourney(
            $userId,
            $preview['createFormData'],
            $preview['locationsData'],
            $preview['geoJsonTrack']
        );
    } catch (ExternalApiException $e) {
        session()->remove('journey_preview');
        return redirect()->to('/journeys/new')
            ->with('errors', ['api' => 'Service de cartographie indisponible, réessayez plus tard.']);
    } catch (ModelValidationException $e) {
        session()->remove('journey_preview');
        return redirect()->to('/journeys/new')
            ->with('errors', $e->getErrors());
    } catch (\Throwable $e) {
        session()->remove('journey_preview');
        return redirect()->to('/journeys/new')
            ->with('errors', ['db' => 'Une erreur est survenue lors de l\'enregistrement.']);
    }

    session()->remove('journey_preview');

    // ====== Matching avec les demandes de trajet existantes
    foreach ($result['journeyIds'] as $journeyId) {
        try {
            $this->journeyService->notifyMatchingRequests($journeyId, $preview['geoJsonTrack']);
        } catch (\Throwable $e) {
            log_message('error', 'notifyMatchingRequests: ' . $e->getMessage());
        }
    }

    // ====== Message et redirection
    $successMessage = $result['count'] > 1
        ? $result['count'] . ' trajets récurrents ont été publiés.'
        : 'Votre trajet a été publié.';

    return redirect()->to('/dashboard/journeys?filter=upcoming')
        ->with('success', $successMessage);
    }

    /**
     * Redirige vers le formulaire de création avec les données pré-remplies
     * depuis la session de prévisualisation, pour permettre la modification.
     *
     * @return RedirectResponse Redirection vers le formulaire de création
     */
    public function modify(): RedirectResponse
    {
        $preview = session()->get('journey_preview');
        if (!$preview) {
            return redirect()->to('/journeys/new');
        }

        session()->setFlashdata('_ci_old_input', [
            'get'  => [],
            'post' => $preview['rawPost'],
        ]);
        session()->remove('journey_preview');

        return redirect()->to('/journeys/new');
    }

    /**
     * Affiche le détail d'un trajet.
     *
     * Délègue au JourneyService l'assemblage des données métier du trajet
     * (étapes, places restantes, passagers, demandes en attente, réservation
     * de l'utilisateur courant). Le controller ne gère que les paramètres HTTP
     * (id, session, filtres GET) et la redirection si le trajet n'existe pas.
     *
     * @param  int|string $id Identifiant du trajet à afficher
     * @return string|RedirectResponse Vue de détail, ou redirection vers la
     *                                 liste si le trajet n'existe pas
     */
    public function show($id): string|RedirectResponse
    {
        $journeyId = (int) $id;
        $userId    = (int) session('user_id');

        $details = $this->journeyService->getJourneyDetails($journeyId, $userId);
        
        if ($details === null) {
            return redirect()->to('/journeys');
        }

        if ($details['journey']['canceled_at']) {
            return redirect()->to('journeys')->with('error', 'Ce trajet a été annulé.');
        }

        return view('Journeys/journeyShow', [
            'title'          => 'Détail du trajet',
            'back'           => $this->validateBackUrl($this->request->getGet('back')),
            'availableSeats' => $this->request->getGet('seats') ?? 1,
            'boardingCity'   => $this->request->getGet('boardingCity'),
            // Spread du tableau : passe journey, stages, passengers, isBooked, etc.
            ...$details,
        ]);
    }

    /**
     * Affiche la liste des trajets correspondant aux filtres de recherche.
     *
     * Délègue au JourneyService la recherche (chargement des candidats,
     * enrichissement des demandes en attente, filtrage géographique sur le
     * tracé). Le controller ne gère que la lecture des filtres HTTP, la
     * pagination en PHP et le rendu de la vue.
     *
     * @return string|RedirectResponse Vue HTML de la liste paginée des trajets
     */
    public function showAll(): string|RedirectResponse
    {
        // --- Récupération des filtres
        $filters = $this->getShowAllFilter();

        // --- Recherche métier (candidats + matching géographique)
        $matchingJourneys = $this->journeySearchService->searchJourneys($filters);

        // --- Pagination en PHP
        $perPage  = 5;
        $total    = count($matchingJourneys);
        $journeys = array_slice($matchingJourneys, ($filters['page'] - 1) * $perPage, $perPage);
        $pager    = \Config\Services::pager();

        return view('Journeys/journeyShowAll', [
            'title'    => 'Rechercher un trajet',
            'journeys' => $journeys,
            'pager'    => $pager,
            'total'    => $total,
            'perPage'  => $perPage,
            // Spread du tableau : passe startAddress, endAddress, latStart, etc.
            ...$filters,
        ]);
    }

    /**
     * Annule un trajet et notifie les passagers acceptés par email.
     *
     * Renseigne le champ canceled_at avec la date courante (soft delete).
     * Vérifie que le trajet existe et que l'utilisateur connecté en est le driver.
     *
     * @param  int $id Identifiant du trajet
     * @return RedirectResponse Redirection vers le dashboard
     */
    public function cancel(int $id): RedirectResponse 
    {
        $journey = $this->journeyModel->findWithDetails($id);
        if (!$journey || $journey['user_id'] !== session()->get('user_id')) {
            return redirect()->to('journeys')->with('error', 'Accès non autorisé.');
        }

        if ($journey['canceled_at']) {
            return redirect()->to('journeys')->with('error', 'Ce trajet est déjà annulé.');
        }

        if ($journey['start_datetime'] < date('Y-m-d H:i:s')) {
            return redirect()->to('journeys')->with('error', 'Impossible d\'annuler un trajet passé.');
        }

        $this->journeyModel->update($id, ['canceled_at' => date('Y-m-d H:i:s')]);
        $this->journeyService->notifyCancelledJourney($journey);
        return redirect()->to('dashboard/journeys?filter=upcoming')->with('success', 'Trajet annulé.');
    }

    /**
     * Retourne les règles de validation du formulaire de création de trajet.
     *
     * La borne max de 'seats' est calculée dynamiquement à partir de la capacité
     * de la voiture sélectionnée (capacité - 1 pour le conducteur). Si la voiture
     * ne peut pas être résolue, on retombe sur une borne par défaut : la règle
     * sur 'car' invalidera le formulaire de toute façon.
     *
     * Les champs 'recurringDays' et 'recurringWeeks' sont validés par des règles
     * personnalisées (valid_recurring_days / valid_recurring_weeks, définies dans
     * App\Validation\CustomRules) qui n'exigent une sélection que si isRecurring = 1.
     *
     * @param  int $maxSeats Nombre maximum de places réservables (défaut : 9)
     * @return array<string, string> Règles de validation CodeIgniter indexées par champ
     */
    private function getCreateValidationRules(int $maxSeats = 9): array {
    return [
        'startDate'      => 'required|valid_date[Y-m-d]',
        'startTime'      => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
        'seats'          => 'required|integer|greater_than[0]|less_than_equal_to[' . $maxSeats . ']',
        'note'           => 'permit_empty|max_length[500]',
        'smoking'        => 'in_list[0,1]',
        'startAddress'   => 'required|string|max_length[255]',
        'endAddress'     => 'required|string|max_length[255]',
        'car'            => 'required|integer|greater_than[0]',
        'isRecurring'    => 'permit_empty|in_list[0,1]',
        'recurringDays'  => 'permit_empty',
        'recurringWeeks' => 'permit_empty',
    ];
}

    /**
     * Retourne les messages d'erreur personnalisés associés aux règles de validation.
     *
     * @param  int $maxSeats Nombre maximum de places réservables (défaut : 9),
     *                       injecté dans le message d'erreur du champ 'seats'
     * @return array<string, string>> Messages indexés par champ puis par règle
     */
    private function getCreateValidationMessages(int $maxSeats = 9): array {

        return [
            'startDate'    => [
                'required'   => 'La date de départ est obligatoire.',
                'valid_date' => 'La date de départ n\'est pas valide.',
            ],
            'startTime'    => [
                'required'      => 'L\'heure de départ est obligatoire.',
                'regex_match'   => 'L\'heure de départ n\'est pas valide (format HH:MM).',
            ],
            'seats'        => [
                'required'      => 'Le nombre de places est obligatoire.',
                'integer'       => 'Le nombre de places doit être un entier.',
                'greater_than'  => 'Le nombre de places doit être supérieur à 0.',
                'less_than_equal_to' => "Le nombre de places ne peut pas dépasser {$maxSeats} (capacité de votre voiture).",
            ],
            'note'         => [
                'max_length'    => 'La note ne peut pas dépasser 500 caractères.',
            ],
            'startAddress' => [
                'required'      => 'L\'adresse de départ est obligatoire.',
                'max_length'    => 'L\'adresse de départ ne peut pas dépasser 255 caractères.',
            ],
            'endAddress'   => [
                'required'      => 'L\'adresse d\'arrivée est obligatoire.',
                'max_length'    => 'L\'adresse d\'arrivée ne peut pas dépasser 255 caractères.',
            ],
            'car'          => [
                'required'      => 'Veuillez sélectionner un véhicule.',
                'integer'       => 'Le véhicule sélectionné n\'est pas valide.',
                'greater_than'  => 'Veuillez sélectionner un véhicule.',
            ],
            'smoking'      => [
                'in_list'       => 'Veuillez indiquer si le covoiturage est fumeur ou non.',
            ],
            'isRecurring'  => [
                'in_list'       => 'La valeur du trajet récurrent n\'est pas valide.',
            ],
            'recurringDays' => [
                'valid_recurring_days' => 'Veuillez sélectionner au moins un jour valide pour un trajet récurrent.',
            ],
            'recurringWeeks' => [
                'valid_recurring_weeks' => 'Veuillez sélectionner au moins une semaine valide pour un trajet récurrent.',
            ],
        ];

    }

    /**
     * Récupère et nettoie les adresses (départ, étapes, arrivée) postées dans le formulaire.
     *
     * Les étapes intermédiaires sont indexées par 'stage0', 'stage1', etc.
     * Les clés réservées sont 'start' et 'end'.
     *
     * @return array<string, string> Adresses nettoyées indexées par clé
     */
    private function getLocationsCreateFormData(): array {

        $locations['start'] = $this->sanitizeAddress($this->request->getPost('startAddress'));

        $stagesAddresses = $this->request->getPost('stagesAddresses');

        if (is_array($stagesAddresses)) {
            foreach ($stagesAddresses as $key => $stageAddress) {
                $locations['stage' . (int) $key] = $this->sanitizeAddress($stageAddress);
            }
        }

        $locations['end'] = $this->sanitizeAddress($this->request->getPost('endAddress'));

        return $locations;

    }

    /**
     * Récupère les champs du trajet (hors adresses) postés dans le formulaire.
     *
     * Si le trajet est marqué comme récurrent (isRecurring = '1'), les jours et
     * semaines sélectionnés sont filtrés via une liste blanche (sanitizeRecurringValues)
     * en plus de la validation déjà effectuée par les règles personnalisées. Si le
     * trajet n'est pas récurrent, ces deux champs sont vidés pour éviter de persister
     * des valeurs incohérentes avec isRecurring = false.
     *
     * @return array{startDate:string, startTime:string, seats:mixed, note:mixed,
     *               smoking:mixed, car:mixed, isRecurring:bool,
     *               recurringDays:array<string>, recurringWeeks:array<string>} Données brutes du POST
     */
    private function getJourneyCreateFormData(): array {

    // On utilise filter_var pour récupérer proprement le booléen, faux par défaut
    $isRecurring = filter_var($this->request->getPost('isRecurring'), FILTER_VALIDATE_BOOLEAN);

    return [
        'startDate'      => $this->request->getPost('startDate'),
        'startTime'      => $this->request->getPost('startTime'),
        'seats'          => $this->request->getPost('seats'),
        'note'           => $this->request->getPost('note'),
        'smoking'        => $this->request->getPost('smoking'),
        'car'            => $this->request->getPost('car'),
        'isRecurring'    => $isRecurring,
        'recurringDays'  => $isRecurring
            ? $this->sanitizeRecurringValues(
                $this->request->getPost('recurringDays'),
                ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']
            )
            : [],
        'recurringWeeks' => $isRecurring
            ? $this->sanitizeRecurringValues(
                $this->request->getPost('recurringWeeks'),
                ['1', '2', '3', '4']
            )
            : [],
    ];
}

    /**
     * Agrège l'ensemble des données du formulaire de création de trajet.
     *
     * @return array{location: array<string,string>, journey: array} Données du formulaire
     *               structurées en deux sous-tableaux : 'location' et 'journey'
     */
    private function getCreateFormData(): array {

        return [
            "location"=>$this->getLocationsCreateFormData(),
            "journey"=>$this->getJourneyCreateFormData(),
        ];

    }

    /**
     * Récupère et normalise les filtres de recherche depuis la query string.
     *
     * Les coordonnées géographiques sont converties en float, ou null si
     * absentes/vides (un paramètre vide comme ?startLat= ne doit pas être
     * interprété comme la coordonnée 0.0).
     *
     * @return array{
     *     startAddress: ?string,
     *     endAddress: ?string,
     *     latStart: ?float,
     *     lngStart: ?float,
     *     latEnd: ?float,
     *     lngEnd: ?float,
     *     filterDate: ?string,
     *     filterTime: ?string,
     *     availableSeats: int,
     *     smoking: ?string,
     *     page: int
     * }
     */
    private function getShowAllFilter(): array
    {
        return [
            'startAddress'      => $this->request->getGet('startAddress'),
            'endAddress'        => $this->request->getGet('endAddress'),
            'searchingRadius'   => (float) $this->request->getGet('searchingRadius'),
            'filterDate'        => $this->request->getGet('date'),
            'filterTime'        => $this->request->getGet('time'),
            'smoking'           => $this->request->getGet('smoking'),
            'page'              => (int) ($this->request->getGet('page') ?? 1),
        ];
    }

    /**
     * Convertit une valeur de query string en float, ou null si vide/absente.
     *
     * @param  mixed $value
     * @return float|null
     */
    private function parseFloatOrNull($value): ?float
    {
        return ($value !== null && $value !== '') ? (float) $value : null;
    }

    /**
     * Nettoie une valeur d'adresse soumise dans le formulaire.
     *
     * Retire les balises HTML et les espaces en début/fin. Renvoie une chaîne
     * vide si la valeur n'est pas une chaîne.
     *
     * @param  mixed $value Valeur brute issue du POST
     * @return string       Adresse nettoyée (chaîne vide si entrée invalide)
     */
    private function sanitizeAddress($value): string {

        if (!is_string($value)) {
            return '';
        }
        return trim(strip_tags($value));

    }

    /**
     * Filtre un tableau de valeurs postées (jours ou semaines de récurrence)
     * pour ne garder que celles présentes dans la liste blanche fournie.
     *
     * Sert de filet de sécurité en plus de la validation CodeIgniter (règles
     * valid_recurring_days / valid_recurring_weeks), au cas où des valeurs
     * auraient été manipulées côté client malgré la validation.
     *
     * @param  mixed $value Valeur brute issue du POST (tableau attendu)
     * @param  array<string> $allowedValues Liste des valeurs autorisées
     * @return array<string> Valeurs filtrées, réindexées
     */
    private function sanitizeRecurringValues($value, array $allowedValues): array {

        if (!is_array($value)) {
            return [];
        }
        return array_values(array_intersect($value, $allowedValues));

    }

}