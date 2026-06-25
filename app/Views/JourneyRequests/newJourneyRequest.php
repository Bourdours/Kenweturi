<?php

/** @var string $title */
?>
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
    base_url('js/autocomplete.js'),
    base_url('js/newJourneyRequest.js'),
  ],
]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <h1 class="text-ink text-2xl font-bold font-display"><?= esc($title) ?></h1>

  <?php if (session()->has('errors')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
      <div>
        <p class="text-ink text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
        <ul class="list-disc pl-4 flex flex-col gap-1">
          <?php foreach (array_unique(session('errors')) as $error): ?>
            <li class="text-action text-xs"><?= esc((string) $error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <form id="addJourneyRequestForm" action="<?= base_url('/journey-requests/new') ?>" method="post" class="flex flex-col gap-6">
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
            <input id="startAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors"
              name="startAddress" type="text" placeholder="Adresse de départ..." value="<?= esc(old('startAddress')) ?>">
            <input type="hidden" name="startLat" id="startLat" value="<?= esc(old('startLat')) ?>">
            <input type="hidden" name="startLng" id="startLng" value="<?= esc(old('startLng')) ?>">
            <input type="hidden" name="startCity" id="startCity" value="<?= esc(old('startCity')) ?>">
            <input type="hidden" name="startZipcode" id="startZipcode" value="<?= esc(old('startZipcode')) ?>">
          </div>
        </div>

        <!-- Arrivée -->
        <div class="flex gap-4 items-start">
          <div class="flex flex-col items-center shrink-0 w-3 pt-8">
            <div class="w-3 h-3 bg-action rounded-full shrink-0"></div>
          </div>
          <div class="flex-1">
            <label for="endAddress" class="text-ink/50 text-xs font-medium mb-1.5 block">Arrivée</label>
            <input id="endAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors"
              name="endAddress" type="text" placeholder="Adresse d'arrivée..." value="<?= esc(old('endAddress')) ?>">
            <input type="hidden" name="endLat" id="endLat" value="<?= esc(old('endLat')) ?>">
            <input type="hidden" name="endLng" id="endLng" value="<?= esc(old('endLng')) ?>">
            <input type="hidden" name="endCity" id="endCity" value="<?= esc(old('endCity')) ?>">
            <input type="hidden" name="endZipcode" id="endZipcode" value="<?= esc(old('endZipcode')) ?>">
          </div>
        </div>

      </div>

      <!-- Rayon -->
      <?php
      $initRadius = (float) old('radius_km', 1);
      $presets    = [1, 5, 10];
      $isCustom   = !in_array($initRadius, $presets);
      ?>
      <div class="mt-5 pt-5 border-t border-action/10">
        <label class="text-ink/50 text-xs font-medium mb-2 block">Rayon de recherche autour de mes adresses</label>
        <div class="flex gap-2 flex-wrap items-center">
          <?php foreach ($presets as $km): ?>
            <button type="button" data-radius="<?= $km ?>"
              class="radius-pill border rounded-lg px-4 py-2 text-sm font-medium transition-colors <?= !$isCustom && $initRadius == $km ? 'bg-action text-ink border-action' : 'border-action/20 text-ink/50 hover:border-action/50 hover:text-ink' ?>">
              <?= $km ?> km
            </button>
          <?php endforeach ?>
          <div class="radius-custom-pill flex items-center border rounded-lg px-3 py-2 gap-1 transition-colors <?= $isCustom ? 'bg-action border-action' : 'border-action/20 hover:border-action/50' ?>">
            <input type="number" id="customRadiusInput" min="0.1" max="200" step="0.1" placeholder="—"
              value="<?= $isCustom ? $initRadius : '' ?>"
              class="w-10 bg-transparent text-sm font-medium outline-none placeholder:text-ink/30 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none <?= $isCustom ? 'text-ink' : 'text-ink/50' ?>">
            <span class="text-sm font-medium transition-colors <?= $isCustom ? 'text-ink' : 'text-ink/50' ?>">km</span>
          </div>
          <input type="hidden" name="radius_km" id="radiusKmInput" value="<?= esc($initRadius) ?>">
        </div>
        <p class="mt-2 text-ink/30 text-xs">Astuce : 0.5 = 500 m, 0.1 = 100 m</p>
      </div>
      <script>
        (function() {
          const pills = document.querySelectorAll('.radius-pill');
          const cpill = document.querySelector('.radius-custom-pill');
          const cinput = document.getElementById('customRadiusInput');
          const hidden = document.getElementById('radiusKmInput');
          const on = ['bg-action', 'text-ink', 'border-action'];
          const off = ['border-action/20', 'text-ink/50'];

          function activatePreset(btn) {
            pills.forEach(p => {
              p.classList.remove(...on);
              p.classList.add(...off);
            });
            btn.classList.add(...on);
            btn.classList.remove(...off);
            cpill.classList.remove(...on);
            cpill.classList.add('border-action/20');
            cinput.classList.remove('text-ink');
            cinput.classList.add('text-ink/50');
            cinput.value = '';
          }

          function activateCustom() {
            pills.forEach(p => {
              p.classList.remove(...on);
              p.classList.add(...off);
            });
            cpill.classList.add(...on);
            cpill.classList.remove('border-action/20');
            cinput.classList.add('text-ink');
            cinput.classList.remove('text-ink/50');
          }

          pills.forEach(p => p.addEventListener('click', () => {
            activatePreset(p);
            hidden.value = p.dataset.radius;
          }));

          cinput.addEventListener('focus', activateCustom);
          cinput.addEventListener('input', () => {
            activateCustom();
            if (cinput.value) hidden.value = cinput.value;
          });
        })();
      </script>

    </div>

    <!-- Date & heure -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-calendar text-action text-sm"></i>Date & heure
      </h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="date" class="text-ink/50 text-xs font-medium mb-1.5 block">Date souhaitée</label>
          <input id="date" name="startDate" type="text" readonly placeholder="jj/mm/aaaa" value="<?= esc(old('startDate')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer transition-colors">
        </div>
        <div>
          <label for="time" class="text-ink/50 text-xs font-medium mb-1.5 block">Heure souhaitée</label>
          <input id="time" name="startTime" type="text" readonly placeholder="--:--" value="<?= esc(old('startTime')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer transition-colors">
        </div>
      </div>
    </div>

    <!-- Places -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-user text-action text-sm"></i>Places
      </h2>
      <div>
        <label for="seats" class="text-ink/50 text-xs font-medium mb-1.5 block">Nombre de places nécessaires</label>
        <input id="seats" name="seats" type="number" min="1" max="8" placeholder="Ex: 1" value="<?= esc(old('seats')) ?>"
          class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
      </div>
    </div>

    <!-- Message -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-align-left text-action text-sm"></i>Message
      </h2>
      <label for="message" class="text-ink/50 text-xs font-medium mb-1.5 block">Informations complémentaires <span class="text-ink/30 font-normal">(optionnel)</span></label>
      <textarea id="message" name="message" rows="4"
        placeholder="Précisez vos besoins, contraintes horaires..."
        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 resize-none transition-colors"><?= esc(old('message')) ?></textarea>
    </div>

    <!-- Boutons -->
    <div class="flex items-center justify-end gap-3">
      <a href="<?= site_url('journey-requests') ?>"
        class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
        Annuler
      </a>
      <button type="submit"
        class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
        <i class="fa-solid fa-check text-xs"></i>Publier la demande
      </button>
    </div>

  </form>
</div>

<?= view('partials/footer') ?>