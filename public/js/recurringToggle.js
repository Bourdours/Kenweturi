document.addEventListener('DOMContentLoaded', function () {
    const isRecurringInput = document.getElementById('isRecurring');
    const yesBtn = document.getElementById('recurringYes');
    const noBtn = document.getElementById('recurringNo');
    const daysWrapper = document.getElementById('recurringDays');
    const weeksWrapper = document.getElementById('recurringWeeks');

    if (!isRecurringInput || !yesBtn || !noBtn || !daysWrapper || !weeksWrapper) return;

    const ACTIVE_CLASSES = ['bg-action', 'text-white', 'border-action'];

    function setActiveButton(activeBtn, inactiveBtn) {
        ACTIVE_CLASSES.forEach(c => activeBtn.classList.add(c));
        activeBtn.classList.remove('text-ink/60');

        ACTIVE_CLASSES.forEach(c => inactiveBtn.classList.remove(c));
        inactiveBtn.classList.add('text-ink/60');
    }

    function resetCheckboxGroup(checkboxSelector, btnSelector) {
        document.querySelectorAll(checkboxSelector).forEach(cb => {
            cb.checked = false;
        });
        document.querySelectorAll(btnSelector).forEach(btn => {
            ACTIVE_CLASSES.forEach(c => btn.classList.remove(c));
            btn.classList.add('text-ink/60');
        });
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

            resetCheckboxGroup('.hidden-day-checkbox', '.day-toggle-btn');
            resetCheckboxGroup('.hidden-week-checkbox', '.week-toggle-btn');
        }
    }

    yesBtn.addEventListener('click', () => showRecurring(true));
    noBtn.addEventListener('click', () => showRecurring(false));

    // Initialisation selon valeur existante (ex: retour de formulaire avec erreurs)
    showRecurring(isRecurringInput.value === '1');

    // Fonction générique pour synchroniser un bouton-toggle avec sa checkbox cachée
    function bindToggleButton(btn, checkbox) {
        function syncButtonState() {
            if (checkbox.checked) {
                ACTIVE_CLASSES.forEach(c => btn.classList.add(c));
                btn.classList.remove('text-ink/60');
            } else {
                ACTIVE_CLASSES.forEach(c => btn.classList.remove(c));
                btn.classList.add('text-ink/60');
            }
        }

        btn.addEventListener('click', () => {
            checkbox.checked = !checkbox.checked;
            syncButtonState();
        });

        syncButtonState(); // état initial (utile si old() a déjà coché certaines valeurs)
    }

    // Boutons de sélection des jours
    document.querySelectorAll('.day-toggle-btn').forEach(btn => {
        const checkbox = document.getElementById('day_' + btn.dataset.day);
        if (checkbox) bindToggleButton(btn, checkbox);
    });

    // Boutons de sélection des semaines
    document.querySelectorAll('.week-toggle-btn').forEach(btn => {
        const checkbox = document.getElementById('week_' + btn.dataset.week);
        if (checkbox) bindToggleButton(btn, checkbox);
    });
});