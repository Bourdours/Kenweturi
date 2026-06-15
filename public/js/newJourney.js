/** newJourney.js
 *
 * Gère les interactions du formulaire d'ajout de trajet :
 *
 *  1. Autocompletion des adresses sur tous les inputs .address (départ, arrivée, étapes)
 *     via l'API https://data.geopf.fr/geocodage/completion/ pour les suggestions,
 *     puis https://data.geopf.fr/geocodage/search/ pour normaliser l'adresse choisie.
 *
 *  2. Restriction de saisie sur le champ #seats : seuls les chiffres et les touches
 *     de navigation/édition sont autorisés. Les raccourcis clavier (Ctrl+A, Ctrl+C,
 *     Ctrl+V, Cmd+...) restent fonctionnels.
 *
 *  3. Dropdown personnalisé pour le choix du véhicule (#carDropdownBtn / #carDropdownList).
 *
 *  4. Ajout et suppression dynamique d'étapes intermédiaires via #addStageBtn.
 *     Les étapes pré-rendues côté serveur (retour au formulaire après erreur de
 *     validation) sont prises en compte au chargement.
 *
 */


// ========== AUTOCOMPLETION : INITIALISATION DES INPUTS ADRESSES

document.querySelectorAll('#addJourneyForm .address').forEach((input) => {
    setupAddressAutocompletion(input);
});


// ========== NOMBRE DE PLACES : RESTRICTION DE SAISIE

const seatsInput = document.getElementById('seats');

if (seatsInput) {
    seatsInput.addEventListener('keydown', restrictSeatsKeydown);
}


// ========== DROPDOWN VEHICULE : INITIALISATION

const carBtn    = document.getElementById('carDropdownBtn');
const carList   = document.getElementById('carDropdownList');
const carLabel  = document.getElementById('carDropdownLabel');
const carArrow  = document.getElementById('carDropdownArrow');
const carHidden = document.getElementById('carHidden');

if (carBtn && carList && carLabel && carArrow && carHidden) {
    initCarDropdown(carBtn, carList, carLabel, carArrow, carHidden);
    restoreCarDropdownSelection(carList, carLabel, carHidden);
}


// ========== ETAPES INTERMEDIAIRES : INITIALISATION

const stagesContainer = document.getElementById('stagesContainer');
const addStageBtn     = document.getElementById('addStageBtn');

if (stagesContainer && addStageBtn) {
    initStagesManagement(stagesContainer, addStageBtn);
}


/** ========== > FONCTION setupAddressAutocompletion()
 *
 * setupAddressAutocompletion() attache la logique d'autocompletion à un input adresse :
 * création de la liste de suggestions, gestion du debounce sur la saisie,
 * et fermeture de la liste au clic extérieur.
 *
 * @param {HTMLInputElement} input : champ adresse à équiper
 * @returns {void}                 : /
 *
 *  < ==========
 */
function setupAddressAutocompletion(input) {

    if (!input || !input.parentElement) return;

    const DEBOUNCE_DELAY   = 300;
    const MIN_QUERY_LENGTH = 3;
    const list = createSuggestionsList(input);
    let debounceTimer;

    // Au fil de la saisie : on déclenche la requête uniquement après une pause.
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        if (query.length < MIN_QUERY_LENGTH) {
            hideSuggestionsList(list);
            return;
        }
        debounceTimer = setTimeout(() => loadSuggestions(query, input, list), DEBOUNCE_DELAY);
    });

    // Fermeture de la liste si l'utilisateur clique en dehors.
    document.addEventListener('click', (event) => {
        if (!input.contains(event.target) && !list.contains(event.target)) {
            hideSuggestionsList(list);
        }
    });
}


/** ========== > FONCTION createSuggestionsList()
 *
 * createSuggestionsList() crée la liste <ul> destinée à recevoir les suggestions
 * d'adresses et l'insère dans le parent de l'input concerné.
 *
 * @param {HTMLInputElement} input : input adresse parent
 * @returns {HTMLUListElement}     : liste de suggestions créée
 *
 *  < ==========
 */
function createSuggestionsList(input) {

    // Le parent doit être positionné pour permettre le placement absolu de la liste.
    input.parentElement.style.position = 'relative';

    const list = document.createElement('ul');
    list.classList.add('autocomplete-dropdown');
    input.parentElement.appendChild(list);

    return list;
}


/** ========== > FONCTION loadSuggestions()
 *
 * loadSuggestions() interroge l'API de completion d'adresses et transmet le
 * résultat à displaySuggestions() pour affichage.
 *
 * @param {string}            query : début d'adresse saisi
 * @param {HTMLInputElement}  input : champ adresse cible
 * @param {HTMLUListElement}  list  : liste de suggestions
 * @returns {void}                  : /
 *
 *  < ==========
 */
function loadSuggestions(query, input, list) {

    const url = buildCompletionUrl(query);

    requestCompletionAPI(url)
        .then((suggestions) => displaySuggestions(suggestions, input, list))
        .catch((error) => {
            hideSuggestionsList(list);
            console.log(error);
        });
}


/** ========== > FONCTION buildCompletionUrl()
 *
 * buildCompletionUrl() retourne l'URL de l'API Géoplateforme pour la completion
 * d'une adresse en cours de saisie.
 *
 * @param {string} query : début d'adresse à compléter
 * @returns {string}     : URL complète à exploiter avec un fetch
 *
 *  < ==========
 */
function buildCompletionUrl(query) {

    const urlApi = 'https://data.geopf.fr/geocodage/completion/';
    const params = `text=${encodeURIComponent(query)}&maximumResponses=5&type=StreetAddress`;

    return `${urlApi}?${params}`;
}


/** ========== > FONCTION buildSearchUrl()
 *
 * buildSearchUrl() retourne l'URL de l'API Géoplateforme pour la recherche
 * d'une adresse normalisée.
 *
 * @param {string} query : adresse à normaliser
 * @returns {string}     : URL complète à exploiter avec un fetch
 *
 *  < ==========
 */
function buildSearchUrl(query) {

    const urlApi = 'https://data.geopf.fr/geocodage/search';
    const params = `q=${encodeURIComponent(query)}&index=address&limit=1`;

    return `${urlApi}?${params}`;
}


/** ========== > FONCTION requestCompletionAPI()
 *
 * requestCompletionAPI() effectue une requête GET vers l'API de completion
 * et retourne le tableau de suggestions.
 *
 * @param {string} url          : URL de l'API à interroger
 * @returns {Promise<array>}    : tableau de suggestions
 *
 *  < ==========
 */
function requestCompletionAPI(url) {

    return fetch(url)
        .then((response) => {
            if (!response.ok) throw new Error(`Erreur API : ${response.status}`);
            return response.json();
        })
        .then((data) => data.results ?? []);
}


/** ========== > FONCTION requestSearchAPI()
 *
 * requestSearchAPI() effectue une requête GET vers l'API search et retourne
 * la première feature de la réponse, ou null si aucun résultat.
 *
 * @param {string} url              : URL de l'API à interroger
 * @returns {Promise<object|null>}  : première feature ou null
 *
 *  < ==========
 */
function requestSearchAPI(url) {

    return fetch(url)
        .then((response) => {
            if (!response.ok) throw new Error(`Erreur API : ${response.status}`);
            return response.json();
        })
        .then((data) => data.features?.[0] ?? null);
}


/** ========== > FONCTION displaySuggestions()
 *
 * displaySuggestions() vide puis remplit la liste de suggestions à partir
 * des résultats de l'API. Au clic sur une suggestion, l'adresse est normalisée
 * via applySelectedAddress() puis la liste est masquée.
 *
 * @param {array}            suggestions : tableau de suggestions de l'API
 * @param {HTMLInputElement} input       : champ adresse cible
 * @param {HTMLUListElement} list        : liste de suggestions
 * @returns {void}                       : /
 *
 *  < ==========
 */
function displaySuggestions(suggestions, input, list) {

    list.innerHTML = '';

    if (!suggestions.length) {
        hideSuggestionsList(list);
        return;
    }

    suggestions.forEach((suggestion) => {

        const item = document.createElement('li');
        item.classList.add('autocomplete-item');
        item.textContent = suggestion.fulltext;
        item.addEventListener('click', () => {
            applySelectedAddress(suggestion.fulltext, input);
            hideSuggestionsList(list);
        });

        list.appendChild(item);
    });

    list.style.display = 'block';
}


/** ========== > FONCTION applySelectedAddress()
 *
 * applySelectedAddress() affecte l'adresse choisie à l'input puis tente de
 * la remplacer par sa version normalisée via l'API search. En cas d'échec
 * ou d'absence de résultat, la valeur provisoire est conservée pour ne
 * pas vider l'input.
 *
 * @param {string}           query : adresse choisie par l'utilisateur
 * @param {HTMLInputElement} input : champ adresse cible
 * @returns {void}                 : /
 *
 *  < ==========
 */
function applySelectedAddress(query, input) {

    input.value = query;

    const url = buildSearchUrl(query);

    requestSearchAPI(url)
        .then((feature) => {
            if (feature && feature.properties?.label) {
                input.value = feature.properties.label;
            }
        })
        .catch((error) => console.log(error));
}


/** ========== > FONCTION hideSuggestionsList()
 *
 * hideSuggestionsList() vide et masque la liste de suggestions.
 *
 * @param {HTMLUListElement} list : liste de suggestions
 * @returns {void}                : /
 *
 *  < ==========
 */
function hideSuggestionsList(list) {

    list.innerHTML = '';
    list.style.display = 'none';
}


/** ========== > FONCTION restrictSeatsKeydown()
 *
 * restrictSeatsKeydown() empêche la saisie de caractères non numériques dans
 * le champ "nombre de places". Les touches de navigation et d'édition restent
 * autorisées, ainsi que les raccourcis clavier (Ctrl/Cmd + ...).
 *
 * @param {KeyboardEvent} event : événement keydown
 * @returns {void}              : /
 *
 *  < ==========
 */
function restrictSeatsKeydown(event) {

    // Autorise tous les raccourcis (Ctrl+A, Ctrl+C, Ctrl+V, Cmd+...).
    if (event.ctrlKey || event.metaKey) return;

    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];

    if (!allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) {
        event.preventDefault();
    }
}


/** ========== > FONCTION initCarDropdown()
 *
 * initCarDropdown() active le dropdown véhicule : ouverture/fermeture au clic
 * sur le bouton, sélection d'un véhicule au clic sur un item, fermeture au
 * clic en dehors.
 *
 * @param {HTMLElement}      btn    : bouton d'ouverture du dropdown
 * @param {HTMLElement}      list   : liste des véhicules
 * @param {HTMLElement}      label  : label affichant la sélection courante
 * @param {HTMLElement}      arrow  : flèche du dropdown
 * @param {HTMLInputElement} hidden : input caché contenant l'id du véhicule
 * @returns {void}                  : /
 *
 *  < ==========
 */
function initCarDropdown(btn, list, label, arrow, hidden) {

    btn.addEventListener('click', () => toggleCarDropdown(list, arrow));

    list.querySelectorAll('.autocomplete-item').forEach((item) => {
        item.addEventListener('click', () => selectCar(item, label, hidden, list, arrow));
    });

    document.addEventListener('click', (event) => {
        if (!btn.contains(event.target) && !list.contains(event.target)) {
            closeCarDropdown(list, arrow);
        }
    });
}


/** ========== > FONCTION toggleCarDropdown()
 *
 * toggleCarDropdown() bascule l'état ouvert/fermé du dropdown véhicule.
 *
 * @param {HTMLElement} list  : liste des véhicules
 * @param {HTMLElement} arrow : flèche du dropdown
 * @returns {void}            : /
 *
 *  < ==========
 */
function toggleCarDropdown(list, arrow) {

    const isOpen = list.style.display === 'block';

    list.style.display    = isOpen ? 'none' : 'block';
    arrow.style.transform = isOpen ? ''     : 'rotate(180deg)';
}


/** ========== > FONCTION closeCarDropdown()
 *
 * closeCarDropdown() ferme le dropdown véhicule.
 *
 * @param {HTMLElement} list  : liste des véhicules
 * @param {HTMLElement} arrow : flèche du dropdown
 * @returns {void}            : /
 *
 *  < ==========
 */
function closeCarDropdown(list, arrow) {

    list.style.display    = 'none';
    arrow.style.transform = '';
}


/** ========== > FONCTION selectCar()
 *
 * selectCar() applique la sélection d'un véhicule : met à jour l'input caché,
 * le label affiché, et ferme le dropdown.
 *
 * @param {HTMLElement}      item   : élément <li> sélectionné
 * @param {HTMLElement}      label  : label affichant la sélection
 * @param {HTMLInputElement} hidden : input caché contenant l'id du véhicule
 * @param {HTMLElement}      list   : liste des véhicules
 * @param {HTMLElement}      arrow  : flèche du dropdown
 * @returns {void}                  : /
 *
 *  < ==========
 */
function selectCar(item, label, hidden, list, arrow) {

    const value = item.dataset.value;

    hidden.value      = value;
    label.textContent = item.textContent.trim();
    label.classList.remove('text-ink/30', 'text-ink');
    label.classList.add(value ? 'text-ink' : 'text-ink/30');

    closeCarDropdown(list, arrow);
}


/** ========== > FONCTION restoreCarDropdownSelection()
 *
 * restoreCarDropdownSelection() restaure l'état visuel du dropdown véhicule
 * après un retour au formulaire suite à une erreur de validation : l'input
 * caché est pré-rempli côté serveur, on retrouve l'item correspondant pour
 * mettre à jour le label.
 *
 * @param {HTMLElement}      list   : liste des véhicules
 * @param {HTMLElement}      label  : label affichant la sélection
 * @param {HTMLInputElement} hidden : input caché contenant l'id du véhicule
 * @returns {void}                  : /
 *
 *  < ==========
 */
function restoreCarDropdownSelection(list, label, hidden) {

    const oldValue = hidden.value;
    if (!oldValue) return;

    const selected = list.querySelector(`[data-value="${oldValue}"]`);
    if (!selected) return;

    label.textContent = selected.textContent.trim();
    label.classList.remove('text-ink/30');
    label.classList.add('text-ink');
}


/** ========== > FONCTION initStagesManagement()
 *
 * initStagesManagement() initialise la gestion des étapes intermédiaires :
 * ajout dynamique au clic sur le bouton, et câblage de la suppression sur
 * les étapes pré-rendues côté serveur via old().
 *
 * @param {HTMLElement} container : conteneur des étapes
 * @param {HTMLElement} addBtn    : bouton "ajouter une étape"
 * @returns {void}                : /
 *
 *  < ==========
 */
function initStagesManagement(container, addBtn) {

    // Câblage de la suppression sur les étapes pré-rendues par le serveur.
    // L'autocompletion est déjà branchée sur leurs inputs par le querySelectorAll initial.
    container.querySelectorAll('.dynamic-stage .remove-stage').forEach((btn) => {
        const row = btn.closest('.dynamic-stage');
        btn.addEventListener('click', () => removeStage(row, container));
    });

    addBtn.addEventListener('click', () => addStage(container));
}


/** ========== > FONCTION addStage()
 *
 * addStage() ajoute une nouvelle étape intermédiaire au formulaire, lui
 * attache l'autocompletion et le handler de suppression.
 *
 * @param {HTMLElement} container : conteneur des étapes
 * @returns {void}                : /
 *
 *  < ==========
 */
function addStage(container) {

    // L'étape 1 est statique : la numérotation des étapes dynamiques démarre à 2.
    const stageNumber = container.querySelectorAll('.dynamic-stage').length + 2;
    const row         = buildStageRow(stageNumber);

    setupAddressAutocompletion(row.querySelector('.address'));

    row.querySelector('.remove-stage').addEventListener('click', () => {
        removeStage(row, container);
    });

    container.appendChild(row);
}


/** ========== > FONCTION buildStageRow()
 *
 * buildStageRow() construit l'élément DOM d'une étape intermédiaire (label,
 * input adresse, bouton de suppression).
 *
 * @param {number} stageNumber : numéro de l'étape (affiché dans le label)
 * @returns {HTMLDivElement}   : élément DOM de la ligne d'étape
 *
 *  < ==========
 */
function buildStageRow(stageNumber) {

    const row = document.createElement('div');
    row.className = 'flex gap-4 items-start dynamic-stage';
    row.innerHTML = `
        <div class="flex flex-col items-center shrink-0 w-3 pt-8">
            <div class="w-2 h-2 bg-ink/20 rounded-full shrink-0 mt-0.5"></div>
            <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
        </div>
        <div class="flex-1 pb-4 flex items-start gap-2">
            <div class="flex-1">
                <label class="text-ink/50 text-xs font-medium mb-1.5 block">Étape ${stageNumber} <span class="text-ink/30 font-normal">(optionnel)</span></label>
                <input class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="stagesAddresses[]" type="text" placeholder="Adresse de l'étape...">
            </div>
            <button type="button" class="remove-stage mt-6 text-ink/30 hover:text-action text-sm transition-colors cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `;

    return row;
}


/** ========== > FONCTION removeStage()
 *
 * removeStage() supprime une étape du DOM puis renumérote les étapes restantes.
 *
 * @param {HTMLElement} row       : ligne de l'étape à supprimer
 * @param {HTMLElement} container : conteneur des étapes
 * @returns {void}                : /
 *
 *  < ==========
 */
function removeStage(row, container) {

    row.remove();
    renumberStages(container);
}


/** ========== > FONCTION renumberStages()
 *
 * renumberStages() met à jour les labels des étapes dynamiques pour garantir
 * une numérotation continue après une suppression. La numérotation démarre
 * à 2 car l'étape 1 est statique.
 *
 * @param {HTMLElement} container : conteneur des étapes
 * @returns {void}                : /
 *
 *  < ==========
 */
function renumberStages(container) {

    container.querySelectorAll('.dynamic-stage label').forEach((label, index) => {
        label.innerHTML = `Étape ${index + 2} <span class="text-ink/30 font-normal">(optionnel)</span>`;
    });
}

const addCarBtn = document.querySelector('#addCarBtn');

if (addCarBtn) {
    document.querySelectorAll('.js-car-input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); addCarBtn.click(); }
        });
    });

    addCarBtn.addEventListener('click', async function () {
        const brand  = document.querySelector('#vehicleBrand').value.trim();
        const model  = document.querySelector('#vehicleModel').value.trim();
        const color  = document.querySelector('#vehicleColor').value.trim();
        const seats  = document.querySelector('#vehicleSeats').value.trim();
        const carError = document.querySelector('#carError');

        if (!brand || !model || !color || !seats || isNaN(seats) || seats < 1 || seats > 9) {
            carError.classList.remove('hidden');
            return;
        }
        carError.classList.add('hidden');

        const formData = new FormData();
        formData.append(addCarBtn.dataset.csrfName, addCarBtn.dataset.csrfValue);
        formData.append('brand', brand);
        formData.append('model', model);
        formData.append('color', color);
        formData.append('seats', seats);

        const response = await fetch(addCarBtn.dataset.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });

        const data = await response.json();

        if (data.csrfToken) {
            addCarBtn.dataset.csrfValue = data.csrfToken;
        }

        if (data.success) {
            const list = document.querySelector('#carDropdownList');
            const li   = document.createElement('li');
            li.className     = 'autocomplete-item';
            li.dataset.value = data.car_id;
            li.textContent   = data.label;
            list.appendChild(li);

            // Sélectionner directement la nouvelle voiture
            selectCar(li, carLabel, carHidden, carList, carArrow);

            document.querySelector('#addCarForm').classList.add('hidden');
            document.querySelector('#btnToggleAddCar').innerHTML =
                '<i class="fa-solid fa-plus text-xs"></i> Ajouter une voiture';

            document.querySelector('#vehicleBrand').value = '';
            document.querySelector('#vehicleModel').value = '';
            document.querySelector('#vehicleColor').value = '';
            document.querySelector('#vehicleSeats').value = '';
        }
    });
}

const btnToggleAddCar = document.querySelector('#btnToggleAddCar');
const addCarForm      = document.querySelector('#addCarForm');

if (btnToggleAddCar && addCarForm) {
    btnToggleAddCar.addEventListener('click', () => {
        const isHidden = addCarForm.classList.contains('hidden');
        addCarForm.classList.toggle('hidden', !isHidden);
        btnToggleAddCar.innerHTML = isHidden
            ? '<i class="fa-solid fa-xmark text-xs"></i> Annuler'
            : '<i class="fa-solid fa-plus text-xs"></i> Ajouter une voiture';
    });
}

// Autocomplete marques voiture
const CAR_BRANDS = [
  'Alfa Romeo', 'Aston Martin', 'Audi', 'Bentley', 'BMW', 'Bugatti',
  'Cadillac', 'Chevrolet', 'Chrysler', 'Citroën', 'Cupra', 'Dacia',
  'Dodge', 'DS Automobiles', 'Ferrari', 'Fiat', 'Ford', 'Genesis',
  'Honda', 'Hummer', 'Hyundai', 'Infiniti', 'Isuzu', 'Jaguar', 'Jeep',
  'Kia', 'Lamborghini', 'Lancia', 'Land Rover', 'Lexus', 'Lincoln',
  'Lotus', 'Maserati', 'Maybach', 'Mazda', 'McLaren', 'Mercedes-Benz',
  'MG', 'Mini', 'Mitsubishi', 'Morgan', 'Nissan', 'Oldsmobile', 'Opel',
  'Peugeot', 'Pontiac', 'Porsche', 'Ram', 'Renault', 'Rolls-Royce',
  'Rover', 'Saab', 'Seat', 'Skoda', 'Smart', 'Subaru', 'Suzuki',
  'Tesla', 'Toyota', 'Triumph', 'Volkswagen', 'Volvo',
  'BYD', 'Nio', 'Xpeng', 'Lynk & Co', 'Ora', 'Aiways', 'Omoda',
  'Alpine', 'Ligier', 'Microcar', 'Aixam'
];

const brandInput           = document.querySelector('#vehicleBrand');
const brandSuggestionsJourney = document.querySelector('#brandSuggestions');

if (brandInput && brandSuggestionsJourney) {
    brandInput.addEventListener('input', function () {
        const value = this.value.trim().toLowerCase();
        brandSuggestionsJourney.innerHTML = '';
        if (value.length < 1) { brandSuggestionsJourney.classList.add('hidden'); return; }
        const filtered = CAR_BRANDS.filter(b => b.toLowerCase().includes(value));
        if (filtered.length > 0) {
            filtered.forEach(brand => {
                const li = document.createElement('li');
                li.textContent = brand;
                li.className = 'px-3 py-2 text-sm text-ink hover:bg-action/10 cursor-pointer transition-colors';
                li.addEventListener('click', () => { brandInput.value = brand; brandSuggestionsJourney.classList.add('hidden'); });
                brandSuggestionsJourney.appendChild(li);
            });
            brandSuggestionsJourney.classList.remove('hidden');
        } else {
            brandSuggestionsJourney.classList.add('hidden');
        }
    });
    document.addEventListener('click', e => {
        if (e.target !== brandInput && e.target !== brandSuggestionsJourney) {
            brandSuggestionsJourney.classList.add('hidden');
        }
    });
}