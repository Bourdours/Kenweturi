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

// // Confirmation suppression de compte
function confirmDelete(e) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
    e.preventDefault();
  }
}

const deleteForm = document.querySelector('.deleteAccount');
if (deleteForm) {
  deleteForm.addEventListener('submit', confirmDelete);
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
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault(); 
        addCarBtn.click(); 
      }
    });
  });

  addCarBtn.addEventListener('click', function () {
    const brand    = document.querySelector('#vehicleBrand').value.trim();
    const model    = document.querySelector('#vehicleModel').value.trim();
    const color    = document.querySelector('#vehicleColor').value.trim();
    const seats    = document.querySelector('#vehicleSeats').value.trim();
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
    csrf.type  = 'hidden';
    csrf.name  = addCarBtn.dataset.csrfName;
    csrf.value = addCarBtn.dataset.csrfValue;
    form.appendChild(csrf);

    [['brand', brand], ['model', model], ['color', color], ['seats', seats]].forEach(function ([name, val]) {
      const input = document.createElement('input');
      input.type  = 'hidden';
      input.name  = name;
      input.value = val;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  });
}