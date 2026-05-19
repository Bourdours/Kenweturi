let modal = document.querySelector('#avatarModal');

function openAvatarModal() {
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.addEventListener('keydown', closeOnEsc);
}

function closeAvatarModal() {
  modal.classList.add('hidden'); modal.classList.remove('flex');
  document.removeEventListener('keydown', closeOnEsc);
}

function closeOnEsc(e) {
  if (e.key === 'Escape') closeAvatarModal();
}

document.querySelectorAll('.jsAvatarOpen').forEach(function (el) {
  el.addEventListener('click', openAvatarModal);
});

if (modal) {
  modal.addEventListener('click', closeAvatarModal);
}

// Confirmation suppression de compte
const deleteForm = document.querySelector('.deleteAccount');

function confirmDelete(e) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
    e.preventDefault();
  }
}

deleteForm.addEventListener('submit', confirmDelete);