/** addJourneyForm.js
 *
 * Ce script gère les interactions du formulaire d'ajout de trajet :
 *
 *  1. Autocomplétion d'adresses sur tous les inputs .address (départ, arrivée, étapes)
 *     via l'API https://data.geopf.fr/geocodage/completion/. La fonction setupAddressInput
 *     est exposée pour pouvoir être appliquée également aux étapes ajoutées dynamiquement.
 *
 *  2. Restriction de saisie sur l'input #seats (nombre de places) : seuls les chiffres
 *     et les touches de navigation/édition sont autorisés.
 *
 *  3. Ajout et suppression dynamique d'étapes intermédiaires via le bouton #addStageBtn.
 *     Chaque nouvelle étape est numérotée automatiquement et reçoit l'autocomplétion.
 *     La suppression d'une étape déclenche une renumérotation cohérente.
 *
 *  4. Reprise des étapes dynamiques pré-rendues côté serveur via old() lorsqu'un
 *     retour au formulaire est effectué suite à une erreur de validation. Les
 *     lignes .dynamic-stage présentes dans le DOM au chargement sont câblées
 *     comme si elles avaient été ajoutées par le JS.
 *
 * Les données suivantes de la réponse json de l'API sont utilisées :
 *  results['x'] (longitude)
 *  results['y'] (latitude)
 *  results['fulltext'] (adresse complète)
 *
 * Lorsque l'utilisateur choisit l'une des suggestions (click), ses données sont
 * affectées à la valeur de l'input concernée (fulltext) ainsi qu'à des inputs cachés
 * (pour x et y).
 *
 */

/** setupAddressInput
 *
 * Attache la logique d'autocomplétion à un input adresse donné : création de la liste
 * de suggestions, gestion du debounce sur la saisie, fermeture au clic extérieur,
 * et remplissage des inputs cachés .lng et .lat lors de la sélection d'une suggestion.
 *
 * Cette fonction est appelée à l'initialisation pour les inputs déjà présents,
 * et également pour chaque nouvelle étape ajoutée dynamiquement.
 *
 * @param {HTMLInputElement} input
 */
function setupAddressInput(input) {
    const DEBOUNCE_DELAY = 300;
    const MIN_QUERY_LENGTH = 3;
    const MAX_RESULTS = 5;
    let debounceTimer;

    // Parent positionné pour permettre le placement absolu de la liste == > A migrer dans le CSS ? < ==
    input.parentElement.style.position = 'relative';

    // Création de la liste ul pour accueillir les suggestions li
    const list = document.createElement('ul');
    list.classList.add('autocomplete-dropdown');
    input.parentElement.appendChild(list); // Parent qui englobe input et label

    // A chaque frappe dans un input, on lance un timer avant d'executer la requête
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer); // Chaque frappe remet à zéro le compteur de l'input concerné
        const value = input.value.trim();
        if (value.length < MIN_QUERY_LENGTH) { hideList(); return; }
        debounceTimer = setTimeout(() => fetchSuggestions(value), DEBOUNCE_DELAY);
    });

    // Fermeture de la liste si click en dehors
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !list.contains(e.target)) hideList();
    });

    /** fetchSuggestions
     *
     * Requête l'API avec le début d'adresse renseigné par l'utilisateur puis
     * transmet la réponse à showSuggestions pour afficher la liste de suggestion.
     *
     * @param {string} query
     */
    function fetchSuggestions(query) {
        fetch(`https://data.geopf.fr/geocodage/completion/?text=${encodeURIComponent(query)}&maximumResponses=${MAX_RESULTS}`)
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(data => showSuggestions(data.results))
            .catch(() => hideList());
    }

    /** showSuggestions
     *
     * Vide la liste puis crée un élément <li> cliquable pour chaque suggestion et
     * l'ajoute à la liste. Au clic sur une suggestion, l'input est rempli avec le
     * texte complet et les inputs cachés .lng et .lat reçoivent les coordonnées.
     * Ferme la liste si aucune suggestion n'est disponible.
     *
     * @param {Object[]} suggestions
     */
    function showSuggestions(suggestions) {
        list.innerHTML = '';
        if (!suggestions?.length) { hideList(); return; }
        suggestions.forEach(s => {
            const item = document.createElement('li');
            item.classList.add('autocomplete-item');
            item.textContent = s.fulltext;
            item.addEventListener('click', () => {
                input.value = s.fulltext;
                // input.parentElement.querySelector('.lng').value = s.x;
                // input.parentElement.querySelector('.lat').value = s.y;
                hideList();
            });
            list.appendChild(item);
        });
        list.style.display = 'block';
    }

    /** hideList
     *
     * Vide le contenu de la liste de suggestions et la masque.
     */
    function hideList() {
        list.innerHTML = '';
        list.style.display = 'none';
    }
}

// Application de l'autocomplétion à tous les inputs adresse déjà présents dans le formulaire
// (départ, arrivée, étape 1 statique, et éventuelles étapes dynamiques pré-rendues par PHP via old()).
document.querySelectorAll('#addJourneyForm .address').forEach(input => setupAddressInput(input));


/* -----------------------------------------------------------------------------
 * Restriction de saisie sur le champ "nombre de places"
 * -------------------------------------------------------------------------- */

const seatsInput = document.getElementById('seats');

// On bloque toute touche qui n'est ni un chiffre ni une touche de navigation/édition
// afin d'empêcher la saisie de caractères non numériques dans le champ.
if (seatsInput) {
    seatsInput.addEventListener('keydown', (e) => {
        const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
        if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) e.preventDefault();
    });
}


/* -----------------------------------------------------------------------------
 * Ajout et suppression dynamique d'étapes intermédiaires
 * -------------------------------------------------------------------------- */
const carBtn    = document.getElementById('carDropdownBtn');
const carLabel  = document.getElementById('carDropdownLabel');
const carArrow  = document.getElementById('carDropdownArrow');
const carList   = document.getElementById('carDropdownList');
const carHidden = document.getElementById('carHidden');

if (carBtn && carList) {
    carBtn.addEventListener('click', () => {
        const isOpen = carList.style.display === 'block';
        carList.style.display = isOpen ? 'none' : 'block';
        carArrow.style.transform = isOpen ? '' : 'rotate(180deg)';
    });

    carList.querySelectorAll('.autocomplete-item').forEach(item => {
        item.addEventListener('click', () => {
            const value = item.dataset.value;
            carHidden.value = value;
            carLabel.textContent = item.textContent.trim();
            carLabel.classList.remove('text-ink/30', 'text-ink');
            carLabel.classList.add(value ? 'text-ink' : 'text-ink/30');
            carList.style.display = 'none';
            carArrow.style.transform = '';
        });
    });

    document.addEventListener('click', (e) => {
        if (!carBtn.contains(e.target) && !carList.contains(e.target)) {
            carList.style.display = 'none';
            carArrow.style.transform = '';
        }
    });
}

const stagesContainer = document.getElementById('stagesContainer');
const addStageBtn     = document.getElementById('addStageBtn');

if (addStageBtn && stagesContainer) {

    // Compteur global utilisé pour la numérotation des étapes ajoutées.
    // Initialisé à 1 (étape 1 statique) + le nombre d'étapes dynamiques éventuellement
    // pré-rendues par PHP via old() lors d'un retour suite à une erreur de validation.
    let count = 1 + stagesContainer.querySelectorAll('.dynamic-stage').length;

    // Attache le handler de suppression aux étapes dynamiques pré-rendues côté serveur.
    // L'autocomplétion est déjà branchée sur ces inputs par le querySelectorAll initial
    // en haut du fichier, puisque les .address sont présentes dans le DOM dès le chargement.
    stagesContainer.querySelectorAll('.dynamic-stage .remove-stage').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.dynamic-stage').remove();
            renumber();
        });
    });

    // Au clic sur le bouton "Ajouter une étape", on insère une nouvelle ligne dans
    // le conteneur, on lui attache l'autocomplétion et on gère son bouton de suppression.
    addStageBtn.addEventListener('click', () => {
        count++;

        const row = document.createElement('div');
        row.className = 'flex gap-4 items-start dynamic-stage';
        row.innerHTML = `
            <div class="flex flex-col items-center shrink-0 w-3 pt-8">
                <div class="w-2 h-2 bg-ink/20 rounded-full shrink-0 mt-0.5"></div>
                <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
            </div>
            <div class="flex-1 pb-4 flex items-start gap-2">
                <div class="flex-1">
                    <label class="text-ink/50 text-xs font-medium mb-1.5 block">Étape ${count} <span class="text-ink/30 font-normal">(optionnel)</span></label>
                    <input class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="stagesAddresses[]" type="text" placeholder="Adresse de l'étape...">
                    <input type="hidden" class="lng">
                    <input type="hidden" class="lat">
                </div>
                <button type="button" class="remove-stage mt-6 text-ink/30 hover:text-action text-sm transition-colors cursor-pointer shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;

        // Active l'autocomplétion sur le nouvel input adresse
        setupAddressInput(row.querySelector('.address'));

        // Suppression de la ligne et renumérotation des étapes restantes
        row.querySelector('.remove-stage').addEventListener('click', () => {
            row.remove();
            renumber();
        });

        stagesContainer.appendChild(row);
    });

    /** renumber
     *
     * Met à jour les labels de toutes les étapes dynamiques pour garantir une
     * numérotation continue après la suppression d'une étape. La numérotation
     * démarre à 2 car l'étape 1 (statique) n'est pas gérée ici.
     * Recale également le compteur global pour le prochain ajout.
     */
    function renumber() {
        stagesContainer.querySelectorAll('.dynamic-stage label').forEach((label, i) => {
            label.innerHTML = `Étape ${i + 2} <span class="text-ink/30 font-normal">(optionnel)</span>`;
        });
        count = 1 + stagesContainer.querySelectorAll('.dynamic-stage').length;
    }
}