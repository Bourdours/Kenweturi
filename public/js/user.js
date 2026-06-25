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

if (modal) {
  document.querySelectorAll('.jsAvatarOpen').forEach(function (el) {
    el.addEventListener('click', openAvatarModal);
  });

  modal.addEventListener('click', closeAvatarModal);
}

// Confirmation suppression de compte
const deleteForm = document.querySelector('#formDeleteAccount');
const modalSupprimer = document.querySelector('#modalSupprimer');
const btnOpenDeleteModal = document.querySelector('#btnOpenDeleteModal');
const btnCancelDelete = document.querySelector('#btnCancelDelete');
const deleteAccountPassword = document.querySelector('#deleteAccountPassword');
const deletePasswordError = document.querySelector('#deletePasswordError');

if (deleteForm && modalSupprimer) {

  // Ouvrir le modal au clic sur "Supprimer mon compte"
  const openDeleteModal = () => { modalSupprimer.classList.remove('hidden'); modalSupprimer.classList.add('flex'); deleteAccountPassword.focus(); };
  const closeDeleteModal = () => { modalSupprimer.classList.remove('flex'); modalSupprimer.classList.add('hidden'); deletePasswordError.classList.add('hidden'); };

  btnOpenDeleteModal?.addEventListener('click', openDeleteModal);

  btnCancelDelete?.addEventListener('click', closeDeleteModal);

  modalSupprimer.addEventListener('click', (e) => {
    if (e.target === modalSupprimer) closeDeleteModal();
  });

  // Intercepter la soumission pour valider que ce n'est pas vide
  deleteForm.addEventListener('submit', function (e) {
    const passwordValue = deleteAccountPassword.value.trim();

    if (!passwordValue) {
      e.preventDefault();
      deletePasswordError.classList.remove('hidden');
      deleteAccountPassword.focus();
    } else {
      deletePasswordError.classList.add('hidden');
    }
  });
}
// Ajouter une voiture
const CAR_BRANDS = [
  'Alfa Romeo', 'Aston Martin', 'Audi', 'Bentley', 'BMW', 'Bugatti',
  'Cadillac', 'Chevrolet', 'Chrysler', 'Citroën', 'Cupra', 'Dacia',
  'Dodge', 'DS Automobiles', 'Ferrari', 'Fiat', 'Ford', 'Genesis',
  'Honda', 'Hummer', 'Hyundai', 'Infiniti', 'Isuzu', 'Jaguar', 'Jeep',
  'Kia', 'Lamborghini', 'Lancia', 'Land Rover', 'Lexus', 'Lincoln',
  'Lotus', 'Maserati', 'Maybach', 'Mazda', 'McLaren', 'Mercedes-Benz',
  'MG', 'Mini', 'Mitsubishi', 'Morgan', 'Nissan', 'Oldsmobile', 'Opel',
  'Peugeot', 'Pontiac', 'Porsche', 'Ram', 'Renault', 'Rolls-Royce',
  'Rover', 'Saab', 'Seat', 'Skoda', 'Smart', 'Subaru', 'Suzuki',
  'Tesla', 'Toyota', 'Triumph', 'Volkswagen', 'Volvo',
  'BYD', 'Nio', 'Xpeng', 'Lynk & Co', 'Ora', 'Aiways', 'Omoda',
  'Alpine', 'Ligier', 'Microcar', 'Aixam'
];

const brandInput = document.querySelector('#vehicleBrand');
const suggestionsContainer = document.querySelector('#brandSuggestions');
const addCarBtn = document.querySelector('#addCarBtn');

if (brandInput && suggestionsContainer) {

  brandInput.addEventListener('input', function () {
    const value = this.value.trim().toLowerCase();
    suggestionsContainer.innerHTML = '';

    if (value.length < 1) {
      suggestionsContainer.classList.add('hidden');
      return;
    }

    // Filtrer le tableau des marques
    const filteredBrands = CAR_BRANDS.filter(brand => brand.toLowerCase().includes(value));

    // Si on trouve des marques correspondantes
    if (filteredBrands.length > 0) {
      filteredBrands.forEach(brand => {
        const li = document.createElement('li');
        li.textContent = brand;
        // Styles Tailwind pour l'affichage de la liste
        li.className = 'px-3 py-2 text-sm text-ink hover:bg-action/10 cursor-pointer transition-colors';

        // Événement au clic sur une suggestion de la liste
        li.addEventListener('click', function () {
          brandInput.value = brand;
          suggestionsContainer.classList.add('hidden');
        });

        suggestionsContainer.appendChild(li);
      });
      suggestionsContainer.classList.remove('hidden'); // Rend la liste visible
    } else {
      suggestionsContainer.classList.add('hidden'); // Cache si aucune correspondance
    }
  });

  // Ferme la liste si l'utilisateur clique dans la page
  document.addEventListener('click', function (e) {
    if (e.target !== brandInput && e.target !== suggestionsContainer) {
      suggestionsContainer.classList.add('hidden');
    }
  });
}

// Gestion de l'ajout du véhicule au clic sur le bouton "Ajouter"
if (addCarBtn) {
  document.querySelectorAll('.js-car-input').forEach(input => {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        addCarBtn.click();
      }
    });
  });

  addCarBtn.addEventListener('click', function () {
    const brand = document.querySelector('#vehicleBrand').value.trim();
    const model = document.querySelector('#vehicleModel').value.trim();
    const color = document.querySelector('#vehicleColor').value.trim();
    const seats = document.querySelector('#vehicleSeats').value.trim();
    const carError = document.querySelector('#carError');

    if (!brand || !model || !color || !seats || isNaN(seats) || seats < 1 || seats > 9) {
      carError.classList.remove('hidden');
      return;
    }

    carError.classList.add('hidden');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = addCarBtn.dataset.action;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = addCarBtn.dataset.csrfName;
    csrf.value = addCarBtn.dataset.csrfValue;
    form.appendChild(csrf);

    [['brand', brand], ['model', model], ['color', color], ['seats', seats]].forEach(function ([name, val]) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = val;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  });
}

// Gestion de la modale de confirmation de suppression de véhicule
const deleteCarModal = document.querySelector('#deleteCarModal');
const deleteCarForm = document.querySelector('#deleteCarForm');
const deleteCarLabel = document.querySelector('#deleteCarLabel');
const cancelDeleteCar = document.querySelector('#cancelDeleteCar');

if (deleteCarModal && deleteCarForm && deleteCarLabel) {
  document.querySelectorAll('.deleteCar').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.carId;
      const label = btn.dataset.carLabel;
      deleteCarLabel.textContent = label;
      deleteCarForm.action = `${window.baseUrl}car/${id}/delete`;
      deleteCarModal.classList.remove('hidden');
      deleteCarModal.classList.add('flex');
    });
  });
}

cancelDeleteCar?.addEventListener('click', () => {
  deleteCarModal.classList.add('hidden');
  deleteCarModal.classList.remove('flex');
});

deleteCarModal?.addEventListener('click', (e) => {
  if (e.target === deleteCarModal) {
    deleteCarModal.classList.add('hidden');
    deleteCarModal.classList.remove('flex');
  }
});

const deleteAvatarModal = document.querySelector('#deleteAvatarModal');
const btnDeleteAvatar = document.querySelector('#btnDeleteAvatar');
const cancelDeleteAvatar = document.querySelector('#cancelDeleteAvatar');
const confirmDeleteAvatar = document.querySelector('#confirmDeleteAvatar');

btnDeleteAvatar?.addEventListener('click', () => {
  deleteAvatarModal.classList.remove('hidden');
  deleteAvatarModal.classList.add('flex');
});

cancelDeleteAvatar?.addEventListener('click', () => {
  deleteAvatarModal.classList.add('hidden');
  deleteAvatarModal.classList.remove('flex');
});

deleteAvatarModal?.addEventListener('click', (e) => {
  if (e.target === deleteAvatarModal) {
    deleteAvatarModal.classList.add('hidden');
    deleteAvatarModal.classList.remove('flex');
  }
});

confirmDeleteAvatar?.addEventListener('click', () => {
  document.querySelector('#formDeleteAvatar').submit();
});