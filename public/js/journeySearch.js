document.querySelectorAll('#addJourneyForm .address').forEach(input => {
    setupAutocomplete(input, (suggestion, input, list) => {
        input.value = suggestion.fulltext;
        const parent = input.parentElement;
        const lng    = parent.querySelector('.lng');
        const lat    = parent.querySelector('.lat');
        const city   = parent.querySelector('.city');
        const zip    = parent.querySelector('.zipcode');
        if (lng)  lng.value                   = suggestion.x;
        if (lat)  lat.value                   = suggestion.y;
        if (city) city.setAttribute('value', suggestion.city    ?? '');
        if (zip)  zip.setAttribute('value',  suggestion.zipcode ?? '');
        hideSuggestionsList(list);
    });
});
