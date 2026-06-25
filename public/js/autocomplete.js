const DEBOUNCE_DELAY = 300;
const MIN_QUERY_LENGTH = 3;

function setupAutocomplete(input, onSelect) {
    if (!input || !input.parentElement) return;

    const list = createSuggestionsList(input);
    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        if (query.length < MIN_QUERY_LENGTH) { hideSuggestionsList(list); return; }
        debounceTimer = setTimeout(() => loadSuggestions(query, input, list, onSelect), DEBOUNCE_DELAY);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !list.contains(e.target)) hideSuggestionsList(list);
    });
}

function createSuggestionsList(input) {
    input.parentElement.style.position = 'relative';
    const list = document.createElement('ul');
    list.classList.add('autocomplete-dropdown');
    input.parentElement.appendChild(list);
    return list;
}

function loadSuggestions(query, input, list, onSelect) {
    const url = `https://data.geopf.fr/geocodage/completion/?text=${encodeURIComponent(query)}&maximumResponses=5&type=StreetAddress`;
    fetch(url)
        .then(r => { if (!r.ok) throw new Error(`Erreur API : ${r.status}`); return r.json(); })
        .then(data => {
            const suggestions = (data.results ?? []).map(s => ({
                fulltext: s.fulltext,
                lat: s.y,
                lng: s.x,
                city: s.city ?? '',
                zipcode: s.zipcode ?? '',
            }));
            displaySuggestions(suggestions, input, list, onSelect);
        })
        .catch(() => hideSuggestionsList(list));
}

function displaySuggestions(suggestions, input, list, onSelect) {
    list.innerHTML = '';
    if (!suggestions.length) { hideSuggestionsList(list); return; }
    suggestions.forEach(suggestion => {
        const item = document.createElement('li');
        item.classList.add('autocomplete-item');
        item.textContent = suggestion.fulltext;
        item.addEventListener('click', () => onSelect(suggestion, input, list));
        list.appendChild(item);
    });
    list.style.display = 'block';
}

function hideSuggestionsList(list) {
    list.style.display = 'none';
    list.innerHTML = '';
}
