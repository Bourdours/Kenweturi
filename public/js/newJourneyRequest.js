/** newJourneyRequest.js
 *
 * Gère les interactions du formulaire de demande de trajet :
 *
 *  1. Autocompletion des adresses sur les inputs #startAddress et #endAddress
 *     via l'API https://data.geopf.fr/geocodage/completion/
 *
 *  2. Remplissage des champs cachés (lat, lng, city, zipcode) à la sélection
 *
 *  3. Restriction de saisie sur le champ #seats
 *
 */


// ========== AUTOCOMPLETION : INITIALISATION

document.querySelectorAll('#addJourneyRequestForm .address').forEach((input) => {
    setupAddressAutocompletion(input);
});


// ========== NOMBRE DE PLACES : RESTRICTION DE SAISIE

const seatsInput = document.getElementById('seats');
if (seatsInput) {
    seatsInput.addEventListener('keydown', restrictSeatsKeydown);
}


/** ========== > FONCTION setupAddressAutocompletion() */
function setupAddressAutocompletion(input) {

    if (!input || !input.parentElement) return;

    const DEBOUNCE_DELAY   = 300;
    const MIN_QUERY_LENGTH = 3;
    const list = createSuggestionsList(input);
    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        if (query.length < MIN_QUERY_LENGTH) {
            hideSuggestionsList(list);
            return;
        }
        debounceTimer = setTimeout(() => loadSuggestions(query, input, list), DEBOUNCE_DELAY);
    });

    document.addEventListener('click', (event) => {
        if (!input.contains(event.target) && !list.contains(event.target)) {
            hideSuggestionsList(list);
        }
    });
}


/** ========== > FONCTION createSuggestionsList() */
function createSuggestionsList(input) {

    input.parentElement.style.position = 'relative';

    const list = document.createElement('ul');
    list.classList.add('autocomplete-dropdown');
    input.parentElement.appendChild(list);

    return list;
}


/** ========== > FONCTION loadSuggestions() */
function loadSuggestions(query, input, list) {

    const url = buildCompletionUrl(query);

    requestCompletionAPI(url)
        .then((suggestions) => displaySuggestions(suggestions, input, list))
        .catch((error) => {
            hideSuggestionsList(list);
            console.log(error);
        });
}


/** ========== > FONCTION buildCompletionUrl() */
function buildCompletionUrl(query) {
    return `https://data.geopf.fr/geocodage/completion/?text=${encodeURIComponent(query)}&maximumResponses=5&type=StreetAddress`;
}


/** ========== > FONCTION buildSearchUrl() */
function buildSearchUrl(query) {
    return `https://data.geopf.fr/geocodage/search?q=${encodeURIComponent(query)}&index=address&limit=1`;
}


/** ========== > FONCTION requestCompletionAPI() */
function requestCompletionAPI(url) {
    return fetch(url)
        .then((response) => {
            if (!response.ok) throw new Error(`Erreur API : ${response.status}`);
            return response.json();
        })
        .then((data) => data.results ?? []);
}


/** ========== > FONCTION requestSearchAPI() */
function requestSearchAPI(url) {
    return fetch(url)
        .then((response) => {
            if (!response.ok) throw new Error(`Erreur API : ${response.status}`);
            return response.json();
        })
        .then((data) => data.features?.[0] ?? null);
}


/** ========== > FONCTION displaySuggestions() */
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
        item.addEventListener('click', () => applySelectedAddress(suggestion.fulltext, input, list));
        list.appendChild(item);
    });

    list.style.display = 'block';
}


/** ========== > FONCTION hideSuggestionsList() */
function hideSuggestionsList(list) {
    list.style.display = 'none';
    list.innerHTML     = '';
}


/** ========== > FONCTION applySelectedAddress()
 *
 * Normalise l'adresse choisie via l'API search et remplit les champs cachés
 * (lat, lng, city, zipcode) correspondant à l'input (start ou end).
 */
function applySelectedAddress(address, input, list) {

    input.value = address;
    hideSuggestionsList(list);

    const url = buildSearchUrl(address);

    requestSearchAPI(url).then((feature) => {
        if (!feature) return;

        const props = feature.properties;
        const [lng, lat] = feature.geometry.coordinates;

        const prefix = input.id === 'startAddress' ? 'start' : 'end';

        const latField      = document.getElementById(`${prefix}Lat`);
        const lngField      = document.getElementById(`${prefix}Lng`);
        const cityField     = document.getElementById(`${prefix}City`);
        const zipcodeField  = document.getElementById(`${prefix}Zipcode`);

        if (latField)     latField.value     = lat;
        if (lngField)     lngField.value     = lng;
        if (cityField)    cityField.value    = props.city ?? '';
        if (zipcodeField) zipcodeField.value = props.postcode ?? '';

        input.value = props.label ?? address;
    });
}


/** ========== > FONCTION restrictSeatsKeydown() */
function restrictSeatsKeydown(event) {

    const allowedKeys = [
        'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'
    ];

    if (event.ctrlKey || event.metaKey) return;

    if (!allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) {
        event.preventDefault();
    }
}