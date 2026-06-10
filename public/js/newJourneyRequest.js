document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#addJourneyRequestForm .address').forEach(input => {
        setupAutocomplete(input, (suggestion, input, list) => {
            applySelectedAddress(suggestion, input, list);
        });
    });

    const seatsInput = document.getElementById('seats');
    if (seatsInput) seatsInput.addEventListener('keydown', restrictSeatsKeydown);

    function applySelectedAddress(suggestion, input, list) {
        console.log('applySelectedAddress called', suggestion, input.id);
        input.value = suggestion.fulltext;
        hideSuggestionsList(list);

        const prefix = input.id === 'startAddress' ? 'start' : 'end';
        document.getElementById(`${prefix}Lat`).value     = suggestion.lat;
        document.getElementById(`${prefix}Lng`).value     = suggestion.lng;
        document.getElementById(`${prefix}City`).value    = suggestion.city;
        document.getElementById(`${prefix}Zipcode`).value = suggestion.zipcode;
    }

    function restrictSeatsKeydown(event) {
        const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'];
        if (event.ctrlKey || event.metaKey) return;
        if (!allowedKeys.includes(event.key) && !/^\d$/.test(event.key)) event.preventDefault();
    }
});


