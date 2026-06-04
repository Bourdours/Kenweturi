document.querySelectorAll('#cityStart, #cityEnd').forEach(input => {
    setupAutocomplete(input, (suggestion, input, list) => {
        input.value = suggestion.city ?? suggestion.fulltext;
        hideSuggestionsList(list);
    });
});
