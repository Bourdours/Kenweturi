const dateInput = document.getElementById('date');
if (dateInput) {
    const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
    const frLocale = flatpickr.l10ns.fr;
    flatpickr(dateInput, {
        locale: {
            ...frLocale,
            months: {
                longhand: frLocale.months.longhand.map(cap),
                shorthand: frLocale.months.shorthand.map(cap),
            },
        },
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        disableMobile: !('ontouchstart' in window || navigator.maxTouchPoints > 0),
        defaultDate: dateInput.value || new Date(),
    });
}
