(function () {
    const modal      = document.getElementById('modalBook');
    const btnOpen    = document.getElementById('btnOpenBookModal');
    const btnCancel  = document.getElementById('btnCancelBook');
    const btnConfirm = document.getElementById('btnConfirmBook');
    const form       = document.getElementById('formBook');

    if (!modal) return;

    const openModal  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const closeModal = () => { modal.classList.remove('flex'); modal.classList.add('hidden'); };

    btnOpen.addEventListener('click', openModal);
    btnCancel.addEventListener('click', closeModal);
    btnConfirm.addEventListener('click', () => { form.submit(); });
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
})();
