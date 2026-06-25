document.addEventListener('DOMContentLoaded', function () {
    const isRecurringInput = document.getElementById('isRecurring');
    const yesBtn = document.getElementById('recurringYes');
    const noBtn = document.getElementById('recurringNo');
    const daysWrapper = document.getElementById('recurringDays');
    const weeksWrapper = document.getElementById('recurringWeeks');

    if (!isRecurringInput || !yesBtn || !noBtn || !daysWrapper || !weeksWrapper) return;

    const ACTIVE_CLASSES = ['bg-action', 'text-ink', 'border-action'];

    function setActiveButton(activeBtn, inactiveBtn) {
        ACTIVE_CLASSES.forEach(c => activeBtn.classList.add(c));
        activeBtn.classList.remove('text-ink/60');

        ACTIVE_CLASSES.forEach(c => inactiveBtn.classList.remove(c));
        inactiveBtn.classList.add('text-ink/60');
    }

    function showRecurring(isYes) {
        // On force la valeur textuelle '1' ou '0'
        isRecurringInput.value = isYes ? '1' : '0';

        // Utile pour forcer le validateur à voir le changement
        isRecurringInput.dispatchEvent(new Event('change'));

        if (isYes) {
            setActiveButton(yesBtn, noBtn);
            daysWrapper.classList.remove('hidden');
            weeksWrapper.classList.remove('hidden');
        } else {
            setActiveButton(noBtn, yesBtn);
            daysWrapper.classList.add('hidden');
            weeksWrapper.classList.add('hidden');

            document.querySelectorAll('.day-checkbox').forEach(cb => { cb.checked = false; });
            document.querySelectorAll('.week-checkbox').forEach(cb => { cb.checked = false; });
        }
    }

    yesBtn.addEventListener('click', () => showRecurring(true));
    noBtn.addEventListener('click', () => showRecurring(false));

    // Initialisation selon valeur existante (ex: retour de formulaire avec erreurs)
    showRecurring(isRecurringInput.value === '1');
});
