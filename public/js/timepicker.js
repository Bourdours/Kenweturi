const timeInput = document.getElementById('time');
if (timeInput) {
    flatpickr(timeInput, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        defaultDate: timeInput.value || new Date(),
        onReady(_, __, fp) {
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab'];
            const onlyDigits = (e) => {
                if (!allowedKeys.includes(e.key) && !/^\d$/.test(e.key)) e.preventDefault();
            };
            fp.hourElement.addEventListener('keydown', onlyDigits);
            fp.minuteElement.addEventListener('keydown', onlyDigits);
            fp.hourElement.addEventListener('input', function () {
                if (this.value.length > 2) this.value = this.value.slice(0, 2);
                if (this.value.length >= 2) {
                    fp.minuteElement.focus();
                    fp.minuteElement.select();
                }
            });
            fp.minuteElement.addEventListener('input', function () {
                if (this.value.length > 2) this.value = this.value.slice(0, 2);
            });
        },
    });
}
