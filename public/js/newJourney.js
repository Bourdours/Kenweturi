function setupAddressInput(input) {
    const DEBOUNCE_DELAY = 300;
    const MIN_QUERY_LENGTH = 3;
    const MAX_RESULTS = 5;
    let debounceTimer;

    input.parentElement.style.position = 'relative';

    const list = document.createElement('ul');
    list.classList.add('autocomplete-dropdown');
    input.parentElement.appendChild(list);

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const value = input.value.trim();
        if (value.length < MIN_QUERY_LENGTH) { hideList(); return; }
        debounceTimer = setTimeout(() => fetchSuggestions(value), DEBOUNCE_DELAY);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !list.contains(e.target)) hideList();
    });

    function fetchSuggestions(query) {
        fetch(`https://data.geopf.fr/geocodage/completion/?text=${encodeURIComponent(query)}&maximumResponses=${MAX_RESULTS}`)
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(data => showSuggestions(data.results))
            .catch(() => hideList());
    }

    function showSuggestions(suggestions) {
        list.innerHTML = '';
        if (!suggestions?.length) { hideList(); return; }
        suggestions.forEach(s => {
            const item = document.createElement('li');
            item.classList.add('autocomplete-item');
            item.textContent = s.fulltext;
            item.addEventListener('click', () => {
                input.value = s.fulltext;
                input.parentElement.querySelector('.lng').value = s.x;
                input.parentElement.querySelector('.lat').value = s.y;
                hideList();
            });
            list.appendChild(item);
        });
        list.style.display = 'block';
    }

    function hideList() {
        list.innerHTML = '';
        list.style.display = 'none';
    }
}

document.querySelectorAll('#addJourneyForm .address').forEach(input => setupAddressInput(input));

const seatsInput = document.getElementById('seats');
if (seatsInput) {
    seatsInput.addEventListener('keydown', (e) => {
        const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
        if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) e.preventDefault();
    });
}

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
    let count = 1;

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

        setupAddressInput(row.querySelector('.address'));

        row.querySelector('.remove-stage').addEventListener('click', () => {
            row.remove();
            renumber();
        });

        stagesContainer.appendChild(row);
    });

    function renumber() {
        stagesContainer.querySelectorAll('.dynamic-stage label').forEach((label, i) => {
            label.innerHTML = `Étape ${i + 2} <span class="text-ink/30 font-normal">(optionnel)</span>`;
        });
        count = 1 + stagesContainer.querySelectorAll('.dynamic-stage').length;
    }
}
