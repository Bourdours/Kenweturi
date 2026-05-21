const dateInput = document.getElementById('date');
if (dateInput) {
    flatpickr(dateInput, {
        locale: 'fr',
        dateFormat: 'Y-m-d',
        disableMobile: true,
        defaultDate: dateInput.value || null,
    });
}
