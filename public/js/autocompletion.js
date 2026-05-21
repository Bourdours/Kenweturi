/** autocompletion.js
 * 
 * Ce script affiche des suggestions d'adresses à l'utilisateur, en récupérant sa saisie dans chaque input
 * 
 * Chaque valeur d'input est envoyée à l'API https://data.geopf.fr/geocodage/completion/ afin de créer une liste
 * de 5 suggestions à afficher à l'utilisateur.
 * 
 * Les données suivantes de la réponse json sont utilisées :
 *  results['x'] (longitude)
 *  results['y'] (latitude)
 *  results['fulltext'] (adresse complète)
 *  results['city'] (nom de la ville)
 *  results['zipcode'] (code postal)
 * 
 * Lorsque que l'utilisateur choisie l'une des suggestions (click), ses données sont affectées à la valeur de l'input concernée (fulltext)
 * ainsi qu'à des inputs chachés (pour x et y).
 * 
 */

const DEBOUNCE_DELAY = 300;
const MIN_QUERY_LENGTH = 3;
const MAX_RESULTS = 5;

const addressInputs = document.querySelectorAll('#addJourneyForm .address');

if (addressInputs.length) {

    addressInputs.forEach(input => {
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
            if (value.length < MIN_QUERY_LENGTH) {
                closeList(list);
                return;
            }
            debounceTimer = setTimeout(() => autocomplete(input, list, value), DEBOUNCE_DELAY);
        });

        // Fermeture de la liste si click en dehors
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !list.contains(e.target)) {
                closeList(list);
            }
        });
    });

    /** autocomplete
     * 
     * Requête l'API avec le début d'adresse renseigné par l'utilisateur puis
     * transmet la réponse à renderSuggestions pour afficher la liste de suggestion
     * 
     * @param {HTMLInputElement} input 
     * @param {HTMLUListElement} list
     * @param {string} query 
     */
    function autocomplete(input, list, query) {
        const url = `https://data.geopf.fr/geocodage/completion/?text=${encodeURIComponent(query)}&maximumResponses=${MAX_RESULTS}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                renderSuggestions(data.results, input, list); // Création des éléments de la liste et affichage
            })
            .catch(error => {
                console.error(error);
                closeList(list);
            });
    }

    /** renderSuggestions
     * 
     * Vide la liste puis crée un élément cliquable pour chaque suggestion via
     * createSuggestionItem et l'ajoute à la liste. Ferme la liste si aucune
     * suggestion n'est disponible.
     * 
     * @param {Object[]} suggestions 
     * @param {HTMLInputElement} input 
     * @param {HTMLUListElement} list 
     */
    function renderSuggestions(suggestions, input, list) {
        list.innerHTML = '';
        if (!suggestions || !suggestions.length) {
            closeList(list);
            return;
        }
        suggestions.forEach(suggestion => {
            const item = createSuggestionItem(suggestion);
            item.addEventListener('click', () => selectSuggestion(input, list, item));
            list.appendChild(item);
        });
        list.style.display = 'block';
    }

    /** createSuggestionItem
     * 
     * Crée un élément <li> à partir d'une suggestion en y stockant les coordonnées
     * (longitude, latitude) dans les data-attributes et en affichant le texte complet.
     * 
     * @param {Object} suggestion 
     * @returns {HTMLLIElement}
     */
    function createSuggestionItem(suggestion) {
        const item = document.createElement('li');
        item.classList.add('autocomplete-item');
        item.dataset.lng = suggestion.x;
        item.dataset.lat = suggestion.y;
        item.dataset.city = suggestion.city;
        item.dataset.zipcode = suggestion.zipcode;
        item.textContent = suggestion.fulltext;
        return item;
    }

    /** selectSuggestion
     * 
     * Renseigne le champ de saisie avec le texte de la suggestion sélectionnée et
     * remplit les champs cachés .lng et .lat avec les coordonnées associées, puis
     * ferme la liste via closeList.
     * 
     * @param {HTMLInputElement} input 
     * @param {HTMLUListElement} list 
     * @param {HTMLLIElement} item 
     */
    function selectSuggestion(input, list, item) {
        input.value = item.textContent;
        input.parentElement.querySelector('.lng').value = item.dataset.lng;
        input.parentElement.querySelector('.lat').value = item.dataset.lat;
        input.parentElement.querySelector('.city').value = item.dataset.city;
        input.parentElement.querySelector('.zipcode').value = item.dataset.zipcode;
        closeList(list);
    }

    /** closeList
     * 
     * Vide le contenu de la liste de suggestions et la masque.
     * 
     * @param {HTMLUListElement} list 
     */
    function closeList(list) {
        list.innerHTML = '';
        list.style.display = 'none';
    }
}