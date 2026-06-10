document.querySelectorAll('#addJourneyRequestForm .address').forEach(input => {
    setupAutocomplete(input, (suggestion, input, list) => {
        applySelectedAddress(suggestion.fulltext, input, list);
    });
});

const seatsInput = document.getElementById('seats');
if (seatsInput) seatsInput.addEventListener('keydown', restrictSeatsKeydown);

function applySelectedAddress(address, input, list) {
    input.value = address;
    hideSuggestionsList(list);

    const url = `https://data.geopf.fr/geocodage/search?q=${encodeURIComponent(address)}&index=address&type=StreetAddress&limit=1`;
    fetch(url)
        .then(r => { if (!r.ok) throw new Error(`Erreur API : ${r.status}`); return r.json(); })
        .then(data => {
            const feature = data.features?.[0];
            if (!feature) return;
            const props      = feature.properties;
            const [lng, lat] = feature.geometry.coordinates;
            const prefix     = input.id === 'startAddress' ? 'start' : 'end';

            const latField     = document.getElementById(`${prefix}Lat`);
            const lngField     = document.getElementById(`${prefix}Lng`);
            const cityField    = document.getElementById(`${prefix}City`);
            const zipcodeField = document.getElementById(`${prefix}Zipcode`);

            if (latField)     latField.value     = lat;
            if (lngField)     lngField.value     = lng;
            if (cityField)    cityField.value    = props.city     ?? '';
            if (zipcodeField) zipcodeField.value = props.postcode ?? '';

            input.value = props.label ?? address;
        });
}

function restrictSeatsKeydown(event) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'];
    if (event.ctrlKey || event.metaKey) return;
    if (!allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) event.preventDefault();
}
