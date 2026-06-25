/**
 * Filtre la liste des utilisateurs en temps réel
 * selon le nom ou l'email saisi dans la barre de recherche.
 */
function filterUsers() {
    const query = document.querySelector('#searchUser').value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    let count = 0;

    rows.forEach(row => {
        const name = row.dataset.name;
        const email = row.dataset.email;
        const match = name.includes(query) || email.includes(query);
        row.classList.toggle('hidden', !match);
        if (match) count++;
    });

    // Met à jour le compteur et affiche le message si aucun résultat
    document.querySelector('#userCount').textContent = count;
    document.querySelector('#noResults').classList.toggle('hidden', count > 0);
}

// Formulaire de suppression en attente de confirmation
let pendingDeleteForm = null;

document.addEventListener('DOMContentLoaded', () => {
    // Barre de recherche
    document.querySelector('#searchUser').addEventListener('keyup', filterUsers);

    // Éléments du modal de confirmation de suppression
    const modal = document.querySelector('#deleteModal');
    const modalName = document.querySelector('#deleteModalName');
    const confirmBtn = document.querySelector('#deleteConfirmBtn');
    const cancelBtn = document.querySelector('#deleteCancelBtn');

    // Intercepte le submit de chaque formulaire de suppression
    // pour afficher le modal de confirmation avant envoi
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('.delete-btn');
            modalName.textContent = btn.dataset.name;
            pendingDeleteForm = this;
            modal.classList.remove('hidden');
        });
    });

    // Confirmer la suppression
    confirmBtn.addEventListener('click', () => {
        if (pendingDeleteForm) pendingDeleteForm.submit();
    });

    // Annuler
    cancelBtn.addEventListener('click', () => {
        pendingDeleteForm = null;
        modal.classList.add('hidden');
    });

    // Ferme le modal en cliquant en dehors 
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            pendingDeleteForm = null;
            modal.classList.add('hidden');
        }
    });

    // Ferme le modal en cliquant en appuyant sur Échap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            pendingDeleteForm = null;
            modal.classList.add('hidden');
        }
    });
});