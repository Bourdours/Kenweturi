document.addEventListener('DOMContentLoaded', () => {
    const favoriteInput = document.getElementById('favoriteAddress');

    if (favoriteInput) {
        setupAutocomplete(favoriteInput, (suggestion, input, list) => {
            input.value = suggestion.fulltext;
            hideSuggestionsList(list);
        });
    }
});