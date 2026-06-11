(function () {
    const modal      = document.getElementById('modalCancelJourney');
    const btnOpen    = document.getElementById('btnOpenCancelModal');
    const btnCancel  = document.getElementById('btnCancelCancelModal');
    const btnConfirm = document.getElementById('btnConfirmCancelJourney');
    const form       = document.getElementById('form-cancel-journey');

    if (!modal || !btnOpen || !btnCancel || !btnConfirm || !form) return;

    const openModal  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const closeModal = () => { modal.classList.remove('flex'); modal.classList.add('hidden'); };

    btnOpen.addEventListener('click', openModal);
    btnCancel.addEventListener('click', closeModal);
    btnConfirm.addEventListener('click', () => { form.submit(); });
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
})();
