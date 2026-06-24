(function () {
    const toggleBtn = document.querySelector('#toggleSelectionBtn');
    const exitBtn = document.querySelector('#exitSelectionBtn');
    const selectionBar = document.querySelector('#selectionBar');
    const selectionCount = document.querySelector('#selectionCount');
    const cancelSelectedBtn = document.querySelector('#cancelSelectedBtn');
    const bulkForm = document.querySelector('#bulkCancelForm');
    const journeyList = document.querySelector('#journeyList');

    if (!toggleBtn || !journeyList) {
        return;
    }

    let selectionMode = false;

    function getCheckboxes() {
        return journeyList.querySelectorAll('.journey-card input[type="checkbox"]');
    }

    function updateCount() {
        const checked = journeyList.querySelectorAll('.journey-card input[type="checkbox"]:checked');
        selectionCount.textContent = checked.length + ' sélectionné' + (checked.length > 1 ? 's' : '');
        cancelSelectedBtn.disabled = checked.length === 0;
        cancelSelectedBtn.classList.toggle('opacity-40', checked.length === 0);
        cancelSelectedBtn.classList.toggle('cursor-not-allowed', checked.length === 0);
        journeyList.querySelectorAll('.journey-card').forEach((card) => {
            const cb = card.querySelector('input[type="checkbox"]');
            if (cb) {
                card.classList.toggle('ring-2', cb.checked);
                card.classList.toggle('ring-action/40', cb.checked);
            }
        });
    }

    function enterSelectionMode() {
        selectionMode = true;
        journeyList.querySelectorAll('.journey-checkbox').forEach((box) => box.classList.remove('hidden'));
        journeyList.querySelectorAll('.journey-card').forEach((card) => {
            card.classList.add('cursor-pointer');
        });
        selectionBar.classList.remove('hidden');
        toggleBtn.classList.add('hidden');
        updateCount();
    }

    function exitSelectionMode() {
        selectionMode = false;
        journeyList.querySelectorAll('.journey-checkbox').forEach((box) => box.classList.add('hidden'));
        getCheckboxes().forEach((cb) => { cb.checked = false; });
        selectionBar.classList.add('hidden');
        toggleBtn.classList.remove('hidden');
    }

    toggleBtn.addEventListener('click', enterSelectionMode);
    exitBtn.addEventListener('click', exitSelectionMode);

    journeyList.addEventListener('click', (event) => {
        if (!selectionMode) {
            return;
        }

        const card = event.target.closest('.journey-card');
        if (!card) {
            return;
        }

        const checkbox = card.querySelector('input[type="checkbox"]');
        if (!checkbox) {
            return;
        }

        if (event.target === checkbox) {
            // On stoppe la propagation pour que le <a> ne reçoive jamais cet event
            // (donc pas de navigation), mais on NE FAIT PAS preventDefault ici :
            // ça laisserait le navigateur cocher/décocher normalement la checkbox.
            event.stopPropagation();
            updateCount();
            return;
        }

        // Clic ailleurs sur la card (pas sur la checkbox) : on bloque la navigation
        // et on bascule manuellement la checkbox.
        event.preventDefault();
        checkbox.checked = !checkbox.checked;
        updateCount();
    });

    cancelSelectedBtn.addEventListener('click', () => {
        const checked = journeyList.querySelectorAll('.journey-card input[type="checkbox"]:checked');
        if (checked.length === 0) {
            return;
        }
        bulkForm.submit();
    });
})();