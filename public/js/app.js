// Champs du formulaire
const inputVille = document.querySelector('input[name="cityName"]');
const inputCp    = document.querySelector('input[name="zipCode"]');

if (inputVille && inputCp) {

  // Création du dropdown
  const dropdown = document.createElement('div');
  dropdown.style.cssText = `
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    width: 100%;
  `;
  inputVille.parentElement.style.position = 'relative';
  inputVille.parentElement.appendChild(dropdown);

  let debounceTimer = null;

  // Attendre 250ms après la dernière frappe avant d'appeler l'API
  inputVille.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const val = inputVille.value.trim();
    if (val.length < 2) { fermerDropdown(); return; }
    debounceTimer = setTimeout(() => fetchCommunes(val), 250);
  });

  // Appel API
  async function fetchCommunes(nom) {
    try {
      const url = `https://geo.api.gouv.fr/communes?nom=${encodeURIComponent(nom)}&fields=nom,codesPostaux,codeDepartement&boost=population&limit=8`;
      const res      = await fetch(url);
      const communes = await res.json();
      afficherDropdown(communes);
    } catch (e) {
      fermerDropdown();
    }
  }

  // Affiche les suggestions
  function afficherDropdown(communes) {
    dropdown.innerHTML = '';
    if (!communes.length) { fermerDropdown(); return; }

    communes.forEach(c => {
      const cp   = c.codesPostaux?.[0] ?? '';
      const item = document.createElement('div');
      item.textContent = `${c.nom} (${cp})`;
      item.style.cssText = 'padding: 8px 12px; cursor: pointer; font-size: 14px;';

      // Survol
      item.addEventListener('mouseenter', () => item.style.background = '#f0f0f0');
      item.addEventListener('mouseleave', () => item.style.background = 'white');

      // Sélection d'une ville
      item.addEventListener('mousedown', (e) => {
        e.preventDefault();
        inputVille.value = c.nom;
        inputCp.value    = cp; // Remplit le code postal automatiquement
        fermerDropdown();
      });

      dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
  }

  // Vide et cache le dropdown
  function fermerDropdown() {
    dropdown.style.display = 'none';
    dropdown.innerHTML = '';
  }

  // Ferme le dropdown si clic en dehors
  document.addEventListener('click', (e) => {
    if (!inputVille.contains(e.target) && !dropdown.contains(e.target)) {
      fermerDropdown();
    }
  });

}