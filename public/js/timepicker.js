const timeInput = document.getElementById('time');
if (timeInput) {
    flatpickr(timeInput, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        defaultDate: timeInput.value || new Date(),
    });
}
