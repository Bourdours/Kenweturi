(function () {
    const HTML = `
<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="confirm-modal-backdrop"></div>
  <div class="relative bg-surface-card rounded-2xl shadow-2xl p-6 w-full max-w-sm">
    <p id="confirm-modal-message" class="text-ink font-semibold text-center mb-6"></p>
    <div class="flex gap-3">
      <button type="button" id="confirm-modal-cancel"
              class="flex-1 border border-ink/20 text-ink/70 font-bold font-display rounded-full py-2.5 hover:border-ink/40 hover:text-ink transition-colors">
        Annuler
      </button>
      <button type="button" id="confirm-modal-ok"
              class="flex-1 bg-danger hover:bg-danger/90 transition-colors text-paper font-bold font-display rounded-full py-2.5">
        Confirmer
      </button>
    </div>
  </div>
</div>`;

    document.body.insertAdjacentHTML('beforeend', HTML);

    const modal    = document.getElementById('confirm-modal');
    const msgEl    = document.getElementById('confirm-modal-message');
    const backdrop = document.getElementById('confirm-modal-backdrop');
    const cancelBtn = document.getElementById('confirm-modal-cancel');
    const okBtn    = document.getElementById('confirm-modal-ok');
    let pendingForm = null;

    function open(message, formId) {
        msgEl.textContent = message;
        pendingForm = formId ? document.getElementById(formId) : null;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function close() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingForm = null;
    }

    cancelBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    okBtn.addEventListener('click', () => { if (pendingForm) pendingForm.submit(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    window.openConfirmModal  = open;
    window.closeConfirmModal = close;
})();
