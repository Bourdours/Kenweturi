// Modal de choix du rôle
const roleModal = document.querySelector('#roleModal');
const roleCancelBtn = document.querySelector('#roleCancelBtn');
const roleModalName = document.querySelector('#roleModalName');

if (roleModal && roleCancelBtn && roleModalName) {

    // Ouvre le modal et définit l'action du formulaire selon l'utilisateur cliqué
    document.querySelectorAll('.accept-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            roleModalName.textContent = this.dataset.name;
            document.querySelector('#roleForm').action = this.dataset.url;
            roleModal.classList.remove('hidden');
            roleModal.classList.add('flex');
        });
    });

    // Soumet le formulaire avec le rôle choisi (0 = formateur, 1 = étudiant)
    document.querySelectorAll('.role-choice-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelector('#roleValue').value = this.dataset.role;
            document.querySelector('#roleForm').submit();
        });
    });

    // Ferme le modal via le bouton Annuler
    roleCancelBtn.addEventListener('click', () => {
        roleModal.classList.remove('flex');
        roleModal.classList.add('hidden');
    });

    // Ferme le modal en cliquant en dehors
    roleModal.addEventListener('click', (e) => {
        if (e.target === roleModal) {
            roleModal.classList.remove('flex');
            roleModal.classList.add('hidden');
        }
    });
}

// Ferme les modals ouverts en appuyant sur Échap
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (modal && !modal.classList.contains('hidden')) {
            pendingDeleteForm = null;
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
        if (roleModal && !roleModal.classList.contains('hidden')) {
            roleModal.classList.remove('flex');
            roleModal.classList.add('hidden');
        }
    }
});