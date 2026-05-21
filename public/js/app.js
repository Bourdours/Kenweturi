// Champs du formulaire
const cityInput = document.querySelector('input[name="cityName"]');
const zipInput = document.querySelector('input[name="postalCode"]');
const form = cityInput ? cityInput.closest('form') : null;
const errorText = document.querySelector('.errorMessage');

// Le script ne s'exécute que si les deux champs cibles (Ville et Code Postal) existent sur la page
if (cityInput && zipInput) {

  let isValid = false;

  if (cityInput.value.trim() && zipInput.value.trim().length === 5) {
    isValid = true;
  }

  /**
   * @var {HTMLDivElement} dropdown - Création dynamique de l'élément conteneur 
   * qui affichera la liste des suggestions sous le champ de saisie de la Ville.
   */
  const dropdown = document.createElement('div');
  dropdown.classList.add('autocomplete-dropdown');
  cityInput.parentElement.style.position = 'relative';
  cityInput.parentElement.appendChild(dropdown);

  /**
   * @var {number|null} debounceTimer - ID du chronomètre pour la fonction "debounce".
   */
  let debounceTimer = null;

  /**
   * Événement 'input' sur le champ VILLE.
   * Se déclenche à chaque modification du texte. Gère le délai d'attente
   * et efface les alertes visuelles d'erreur dès que l'utilisateur corrige sa saisie.
   */
  cityInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const val = cityInput.value.trim();

    cityInput.style.borderColor = "";
    zipInput.style.borderColor = "";
    if (errorText) {
      errorText.textContent = "";
      errorText.classList.add('hidden');
    }

    if (val.length < 2) { closeDropdown(); return; }
    debounceTimer = setTimeout(() => fetchCities(val), 250);
  });

  /**
  * Événement 'input' sur le champ CODE POSTAL.
  * Déclenché à chaque chiffre tapé. Dès que la saisie atteint exactement 5 chiffres, 
  * une requête API recherche la commune associée pour remplir automatiquement la Ville.
  */
  zipInput.addEventListener('input', async () => {
    const zipVal = zipInput.value.trim();

    cityInput.style.borderColor = "";
    zipInput.style.borderColor = "";
    if (errorText) {
      errorText.textContent = "";
      errorText.classList.add('hidden');
    }

    if (zipVal.length === 5 && /^\d+$/.test(zipVal)) {
      try {
        const url = `https://geo.api.gouv.fr/communes?codePostal=${zipVal}&fields=nom`;
        const res = await fetch(url);
        const cities = await res.json();

        if (cities.length > 0) {
          cityInput.value = cities[0].nom;
          isValid = true;
          cityInput.style.borderColor = "";
          zipInput.style.borderColor = "";
          if (errorText) {
            errorText.textContent = "";
            errorText.classList.add('hidden');
          }
        } else {
          zipInput.style.borderColor = "red";
          isValid = false;
          if (errorText) {
            errorText.textContent = "Le code postal et la ville ne correspondent pas à une commune valide.";
            errorText.classList.remove('hidden');
          }
        }
      } catch (error) {
        console.error("Erreur lors de la recherche du code postal :", error);
        isValid = false;
      }
    } else {
      isValid = false;
    }
  });

  /**
   * Interroge l'API Géo pour obtenir la liste des villes contenant le nom saisi.
   * * @async
   * @function fetchCities
   * @param {string} name - Le nom (ou début de nom) de la commune recherchée
   * @returns {Promise<void>} - Transmet les données au dropdown.
   */
  async function fetchCities(name) {
    try {
      const url = `https://geo.api.gouv.fr/communes?nom=${encodeURIComponent(name)}&fields=nom,codesPostaux,codeDepartement&boost=population&limit=8`;
      const res = await fetch(url);
      const cities = await res.json();
      displayDropdown(cities);
    } catch (e) {
      closeDropdown();
    }
  }

  /**
  * Lorsque l'utilisateur clique en dehors d'un des deux champs, on relance une 
  * vérification s'assurer que la ville écrite correspond au CP écrit.
  */
  cityInput.addEventListener('blur', validateCityZip);
  zipInput.addEventListener('blur', validateCityZip);

  /**
  * Effectue la vérification finale de cohérence entre la ville saisie et le code postal saisi.
  * Télécharge toutes les communes valides pour le code postal donné et cherche une correspondance.
  * * @async
  * @function validateCityZip
  * @returns {Promise<void>} - Modifie la variable globale 'isValid' et ajuste les styles CSS.
  */
  async function validateCityZip() {
    const typedCity = cityInput.value.trim();
    const typedZip = zipInput.value.trim();

    if (isValid && typedCity && typedZip.length === 5) {
      return;
    }

    if (!typedCity || typedZip.length !== 5) {
      isValid = false;
      return;
    }

    try {
      const url = `https://geo.api.gouv.fr/communes?codePostal=${typedZip}&fields=nom`;
      const res = await fetch(url);
      const cities = await res.json();

      const normalize = (str) => str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, "");
      const typedCityNorm = normalize(typedCity);

      const isMatchFound = cities.some(c => normalize(c.nom) === typedCityNorm);

      if (isMatchFound) {
        isValid = true;
        cityInput.style.borderColor = ""; // Remet le champ à son aspect normal
        zipInput.style.borderColor = "";
        if (errorText) {
          errorText.textContent = "";
          errorText.classList.add('hidden');
        }
      } else {
        cityInput.style.borderColor = "red";
        zipInput.style.borderColor = "red";
        isValid = false;
        if (errorText) {
          errorText.textContent = "Le code postal et la ville ne correspondent pas à une commune valide.";
          errorText.classList.remove('hidden');
        }
      }
    } catch (error) {
      console.error("Impossible de vérifier la correspondance :", error);
      isValid = false;
    }
  }

  /**
  * Génère les éléments HTML à l'intérieur du dropdown pour afficher les suggestions trouvées.
  * * @function displayDropdown
  * @param {Array<Object>} cities - Tableau d'objets communes renvoyé par l'API fetchCities.
  */
  function displayDropdown(cities) {
    dropdown.innerHTML = '';
    if (!cities.length) { closeDropdown(); return; }

    cities.forEach(c => {
      const zip = c.codesPostaux?.[0] ?? '';
      const item = document.createElement('div');
      item.textContent = `${c.nom} (${zip})`;
      item.classList.add('autocomplete-item');

      // Sélection d'une ville
      item.addEventListener('mousedown', (e) => {
        e.preventDefault();
        cityInput.value = c.nom;
        zipInput.value = zip; // Remplit le code postal automatiquement
        validateCityZip();
        closeDropdown();
      });

      dropdown.appendChild(item);
    });

    dropdown.style.display = 'block'; // 'none' est le défaut CSS, on force block à l'ouverture
  }

  /**
  * Nettoie le contenu HTML de la boîte de suggestions et la masque visuellement.
  * * @function closeDropdown
  */
  function closeDropdown() {
    dropdown.style.display = 'none';
    dropdown.innerHTML = '';
  }

  /**
    * Événement 'submit' sur le formulaire.
    * Si 'isValid' est faux, empêche l'envoi des données vers le traitement PHP/Backend.
    */
  if (form) {
    form.addEventListener('submit', (e) => {
      if (!isValid) {
        e.preventDefault(); // Bloque l'envoi vers PHP si la ville est incorrecte
        errorText.textContent = "Le code postal et la ville ne correspondent pas à une commune valide.";
        errorText.classList.remove('hidden');
      }
    });
  }

  /**
   * Permet de fermer le menu d'autocomplétion si l'utilisateur clique 
   * n'importe où ailleurs sur la page
   */
  document.addEventListener('click', (e) => {
    if (!cityInput.contains(e.target) && !dropdown.contains(e.target)) {
      closeDropdown();
    }
  });

}