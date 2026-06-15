(function () {
    const btn    = document.getElementById('smokingDropdownBtn');
    const list   = document.getElementById('smokingDropdownList');
    const label  = document.getElementById('smokingDropdownLabel');
    const arrow  = document.getElementById('smokingDropdownArrow');
    const hidden = document.getElementById('smokingHidden');

    if (!btn || !list || !label || !arrow || !hidden) return;

    btn.addEventListener('click', () => {
        const isOpen = list.style.display === 'block';
        list.style.display    = isOpen ? 'none' : 'block';
        arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
    });

    list.querySelectorAll('.autocomplete-item').forEach((item) => {
        item.addEventListener('click', () => {
            hidden.value          = item.dataset.value;
            label.textContent     = item.textContent.trim();
            list.style.display    = 'none';
            arrow.style.transform = '';
        });
    });

    document.addEventListener('click', (event) => {
        if (!btn.contains(event.target) && !list.contains(event.target)) {
            list.style.display    = 'none';
            arrow.style.transform = '';
        }
    });
})();

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
