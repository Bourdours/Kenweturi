<?= view('partials/head', [
  'extraCss' => [
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
    base_url('css/flatpickr-theme.css'),
  ],
  'extraJs' => [
    'https://cdn.jsdelivr.net/npm/flatpickr',
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js',
    base_url('js/datepicker.js'),
    base_url('js/timepicker.js'),
    base_url('js/newJourney.js'),
  ],
]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <h1 class="text-ink text-2xl font-bold font-display">Publier un trajet</h1>

  <?php if (session()->has('errors')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
      <div>
        <p class="text-ink text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
        <ul class="list-disc pl-4 flex flex-col gap-1">
          <?php foreach (session('errors') as $error): ?>
            <li class="text-action text-xs"><?= esc((string) $error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <form id="addJourneyForm" action="<?= base_url('/journeys/new') ?>" method="post" class="flex flex-col gap-6">
    <?= csrf_field() ?>

    <!-- Itinéraire -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-route text-action text-sm"></i>Itinéraire
      </h2>

      <div class="flex flex-col">

        <!-- Départ -->
        <div class="flex gap-4 items-start">
          <div class="flex flex-col items-center shrink-0 w-3 pt-8">
            <div class="w-3 h-3 bg-brand rounded-full shrink-0"></div>
            <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
          </div>
          <div class="flex-1 pb-4">
            <label for="startAddress" class="text-ink/50 text-xs font-medium mb-1.5 block">Départ</label>
            <input id="startAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="startAddress" type="text" placeholder="Adresse de départ..." value="<?= esc(old('startAddress')) ?>">
          </div>
        </div>

        <!-- Étapes -->
        <div id="stagesContainer">
          <?php
          // Récupère les étapes saisies précédemment ; au minimum une étape vide (Étape 1 statique).
          // Les éventuelles étapes supplémentaires sont rendues comme des .dynamic-stage afin
          // d'être traitées comme si elles avaient été ajoutées par le JS.
          $oldStages = old('stagesAddresses');
          if (!is_array($oldStages) || $oldStages === []) {
            $oldStages = [''];
          }
          ?>
          <?php foreach ($oldStages as $index => $stageAddress): ?>
            <?php if ($index === 0): ?>
              <!-- Étape 1 : statique, non supprimable -->
              <div class="flex gap-4 items-start">
                <div class="flex flex-col items-center shrink-0 w-3 pt-8">
                  <div class="w-2 h-2 bg-ink/20 rounded-full shrink-0 mt-0.5"></div>
                  <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
                </div>
                <div class="flex-1 pb-4">
                  <label for="stage1" class="text-ink/50 text-xs font-medium mb-1.5 block">Étape 1 <span class="text-ink/30 font-normal">(optionnel)</span></label>
                  <input id="stage1" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="stagesAddresses[]" type="text" placeholder="Adresse de l'étape..." value="<?= esc($stageAddress) ?>">
                </div>
              </div>
            <?php else: ?>
              <!-- Étape dynamique restaurée depuis old() -->
              <div class="flex gap-4 items-start dynamic-stage">
                <div class="flex flex-col items-center shrink-0 w-3 pt-8">
                  <div class="w-2 h-2 bg-ink/20 rounded-full shrink-0 mt-0.5"></div>
                  <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
                </div>
                <div class="flex-1 pb-4 flex items-start gap-2">
                  <div class="flex-1">
                    <label class="text-ink/50 text-xs font-medium mb-1.5 block">Étape <?= esc($index + 1) ?> <span class="text-ink/30 font-normal">(optionnel)</span></label>
                    <input class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="stagesAddresses[]" type="text" placeholder="Adresse de l'étape..." value="<?= esc($stageAddress) ?>">
                    <input type="hidden" class="lng">
                    <input type="hidden" class="lat">
                  </div>
                  <button type="button" class="remove-stage mt-6 text-ink/30 hover:text-action text-sm transition-colors cursor-pointer shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <!-- Ajouter une étape -->
        <div class="flex gap-4 items-center">
          <div class="flex flex-col items-center shrink-0 w-3 self-stretch">
            <div class="w-px flex-1 bg-ink/10"></div>
          </div>
          <div class="pt-0 pb-4">
            <button type="button" id="addStageBtn"
              class="flex items-center gap-1.5 text-action/50 hover:text-action text-xs font-medium transition-colors cursor-pointer">
              <i class="fa-solid fa-plus text-xs"></i>Ajouter une étape
            </button>
          </div>
        </div>

        <!-- Arrivée -->
        <div class="flex gap-4 items-start">
          <div class="flex flex-col items-center shrink-0 w-3 pt-8">
            <div class="w-3 h-3 bg-action rounded-full shrink-0"></div>
          </div>
          <div class="flex-1">
            <label for="endAddress" class="text-ink/50 text-xs font-medium mb-1.5 block">Arrivée</label>
            <input id="endAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors" name="endAddress" type="text" placeholder="Adresse d'arrivée..." value="<?= esc(old('endAddress')) ?>">
          </div>
        </div>

      </div>
    </div>

    <!-- Date & heure -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-calendar text-action text-sm"></i>Date & heure
      </h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="date" class="text-ink/50 text-xs font-medium mb-1.5 block">Date de départ</label>
          <input id="date" name="startDate" type="text" readonly placeholder="jj/mm/aaaa" value="<?= esc(old('startDate')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer transition-colors">
        </div>
        <div>
          <label for="time" class="text-ink/50 text-xs font-medium mb-1.5 block">Heure de départ</label>
          <input id="time" name="startTime" type="text" readonly placeholder="--:--" value="<?= esc(old('startTime')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer transition-colors">
        </div>
      </div>
    </div>

    <!-- Préférences -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-sliders text-action text-sm"></i>Préférences
      </h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="seats" class="text-ink/50 text-xs font-medium mb-1.5 block">Nombre de places</label>
          <input id="seats" name="seats" type="number" min="1" max="9" placeholder="Ex: 3" value="<?= esc(old('seats')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
        </div>
        <div>
          <label class="text-ink/50 text-xs font-medium mb-1.5 block">Fumeur</label>
          <div class="flex gap-2">
            <label class="flex-1 flex items-center justify-center gap-2 bg-paper border border-action/15 rounded-lg px-3 py-2.5 text-ink text-sm font-medium cursor-pointer has-[:checked]:bg-action/10 has-[:checked]:border-action/50 transition-colors">
              <input type="radio" name="smoking" value="1" class="hidden" <?= old('smoking') === '1' ? 'checked' : '' ?>>
              <i class="fa-solid fa-smoking text-xs"></i>Oui
            </label>
            <label class="flex-1 flex items-center justify-center gap-2 bg-paper border border-action/15 rounded-lg px-3 py-2.5 text-ink text-sm font-medium cursor-pointer has-[:checked]:bg-action/10 has-[:checked]:border-action/50 transition-colors">
              <input type="radio" name="smoking" value="0" class="hidden" <?= old('smoking') === '0' ? 'checked' : '' ?>>
              <i class="fa-solid fa-ban-smoking text-xs"></i>Non
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Voitures -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
                <i class="fa-solid fa-car text-action text-sm"></i>Voiture
            </h2>
          <a href="<?= base_url('profile/edit') ?>#vehicles"
            class="text-action text-xs hover:underline inline-flex items-center gap-1">
              <i class="fa-solid fa-plus text-xs"></i> Ajouter une voiture
          </a>
        </div>

        <label class="text-ink/50 text-xs font-medium mb-1.5 block">Quelle voiture allez-vous conduire ?</label>
        <div class="relative" id="carDropdownWrapper">
            <button type="button" id="carDropdownBtn"
                class="w-full bg-paper border border-action/15 rounded-lg text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors cursor-pointer text-left flex items-center justify-between">
                <span id="carDropdownLabel" class="text-ink/30">-- Choisir une voiture --</span>
                <i id="carDropdownArrow" class="fa-solid fa-chevron-down text-xs text-ink/30 transition-transform"></i>
            </button>
            <input type="hidden" name="car" id="carHidden" value="<?= esc(old('car'), 'attr') ?>">
            <ul class="autocomplete-dropdown" id="carDropdownList">
                <?php if (!empty($cars)) : ?>
                    <?php foreach ($cars as $car) : ?>
                        <li class="autocomplete-item" data-value="<?= esc($car['id']) ?>">
                            <?= esc($car['brand']) ?> <?= esc($car['model']) ?> <?= esc($car['color']) ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Mini formulaire ajout voiture -->
        <div id="addCarForm" class="hidden mt-4 border-t border-action/10 pt-4 flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="vehicleBrand" class="text-ink/50 text-xs font-medium mb-1.5 block">Marque</label>
                    <div class="relative w-full">
                        <input type="text" id="vehicleBrand" data-name="brand"
                            placeholder="Ex: Renault" autocomplete="off"
                            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
                        <ul id="brandSuggestions" class="absolute left-0 top-full z-50 w-full bg-paper border border-action/15 rounded-b-lg shadow-lg max-h-48 overflow-y-auto hidden flex flex-col pointer-events-auto"></ul>
                    </div>
                </div>
                <div>
                    <label for="vehicleModel" class="text-ink/50 text-xs font-medium mb-1.5 block">Modèle</label>
                    <input type="text" id="vehicleModel" data-name="model"
                        placeholder="Ex: Clio"
                        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
                </div>
                <div>
                    <label for="vehicleColor" class="text-ink/50 text-xs font-medium mb-1.5 block">Couleur</label>
                    <input type="text" id="vehicleColor" data-name="color"
                        placeholder="Ex: Bleu"
                        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
                </div>
                <div>
                    <label for="vehicleSeats" class="text-ink/50 text-xs font-medium mb-1.5 block">Nombre de places</label>
                    <input type="number" id="vehicleSeats" data-name="seats" min="1" max="9"
                        placeholder="Ex: 5"
                        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
                </div>
            </div>
            <p id="carError" class="text-action text-xs hidden mt-1">Veuillez remplir tous les champs. Le nombre de places doit être entre 1 et 9.</p>
            <div class="flex justify-end">
                <button type="button" id="addCarBtn"
                    data-action="<?= site_url('car/create') ?>"
                    data-csrf-name="<?= csrf_token() ?>"
                    data-csrf-value="<?= csrf_hash() ?>"
                    class="flex items-center gap-2 bg-action/10 hover:bg-action/20 text-action font-semibold text-sm rounded-lg px-4 py-2 transition-colors cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>Ajouter
                </button>
            </div>
        </div>
    </div>

    <!-- Note -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-align-left text-action text-sm"></i>Note
      </h2>
      <label for="note" class="text-ink/50 text-xs font-medium mb-1.5 block">Message aux passagers <span class="text-ink/30 font-normal">(optionnel)</span></label>
      <textarea id="note" name="note" rows="4"
        placeholder="Informations utiles pour les passagers..."
        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 resize-none transition-colors"><?= esc(old('note')) ?></textarea>
    </div>

    <!-- Boutons -->
    <div class="flex items-center justify-end gap-3">
      <a href="<?= site_url('dashboard') ?>"
        class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
        Annuler
      </a>
      <button type="submit"
        class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
        <i class="fa-solid fa-check text-xs"></i>Publier le trajet
      </button>
    </div>

  </form>
</div>

<?= view('partials/footer') ?>