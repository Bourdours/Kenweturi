<?php

/** @var array|null $nextDriverJourney */
/** @var array|null $nextPassengerJourney */
?>

<?= view('partials/head', [
  'extraCss' => [
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
    base_url('css/flatpickr-theme.css'),
  ],
  'extraJs' => [
    'https://cdn.jsdelivr.net/npm/flatpickr',
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js',
    base_url('js/autocomplete.js'),
    base_url('js/journeySearch.js'),
    base_url('js/datepicker.js'),
    base_url('js/timepicker.js'),
    base_url('js/swapAddresses.js'),
    base_url('js/rainbow-road.js'),
  ],
]) ?>
<?= view('partials/header') ?>

<div class="relative overflow-hidden">
  <?= view('partials/home_svg') ?>

  <!-- Hero -->
  <section class="bg-gradient-to-b from-paper via-surface to-paper">
    <div class="max-w-4xl mx-auto px-4 py-16 md:py-24 flex flex-col items-center gap-8 text-center">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-success/10 border-b border-r- border-success/20 px-6 py-3 text-success max-w-sm text-sm text-center mx-auto rounded-xl">

          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      <?php endif; ?>

      <div class="flex flex-col gap-3">
        <p class="text-action text-xs font-semibold uppercase tracking-widest">Covoiturage régional</p>
        <h1 class="relative z-10 text-ink text-4xl md:text-5xl font-bold font-display leading-tight">
          Partagez la route,<br>simplifiez vos trajets
        </h1>
        <p class="relative z-10 text-ink/50 text-base md:text-lg max-w-xl mx-auto">
          Trouvez ou proposez un covoiturage domicile-travail près de chez vous. Gratuit, simple, local.
        </p>
      </div>

      <!-- Formulaire de recherche -->
      <form id="addJourneyForm" action="<?= site_url('journeys') ?>" method="GET" class="w-full max-w-2xl">
        <div class="relative z-10 bg-paper rounded-2xl p-5 border border-action/10 shadow-sm space-y-4 text-left">

          <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-3 sm:gap-x-2 sm:items-end">
            <div>
              <label for="startAddress" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-circle-dot text-sm"></i>Départ
              </label>
              <input type="text" id="startAddress" name="startAddress"
                class="address w-full bg-surface border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30"
                placeholder="Ville ou adresse">
              <input type="text" class="lng" id="startAddressLng" name="startLng" hidden>
              <input type="text" class="lat" id="startAddressLat" name="startLat" hidden>
            </div>
            <div class="flex justify-center sm:mb-1">
              <button type="button" id="swapAddresses"
                class="w-8 h-8 flex items-center justify-center bg-surface border border-action/20 rounded-full text-ink/50 hover:text-action hover:border-action/40 transition-colors cursor-pointer"
                title="Inverser départ et arrivée">
                <i class="fa-solid fa-right-left rotate-90 sm:rotate-0 text-xs"></i>
              </button>
            </div>
            <div>
              <label for="endAddress" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-sm"></i>Arrivée
              </label>
              <input type="text" id="endAddress" name="endAddress"
                class="address w-full bg-surface border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30"
                placeholder="Ville ou adresse">
              <input type="text" class="lng" id="endAddressLng" name="endLng" hidden>
              <input type="text" class="lat" id="endAddressLat" name="endLat" hidden>
            </div>
          </div>

          <div class="grid grid-cols-[1fr_auto_1fr] gap-3 sm:gap-x-2">
            <div>
              <label for="date" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-regular fa-calendar text-sm"></i>Date
              </label>
              <input type="text" id="date" name="date" readonly
                placeholder="jj/mm/aaaa"
                class="w-full bg-surface border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer">
            </div>
            <div class="w-8"></div>
            <div>
              <label for="time" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-regular fa-clock text-sm"></i>À partir de
              </label>
              <input type="text" id="time" name="time" readonly placeholder="--:--"
                class="w-full bg-surface border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer">
            </div>
          </div>

          <button type="submit"
            class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg py-3 transition-colors cursor-pointer text-base">
            <i class="fa-solid fa-magnifying-glass mr-2"></i>Rechercher un trajet
          </button>

        </div>
      </form>

      <p class="relative z-10 text-ink/40 text-sm">
        Vous êtes conducteur ?
        <a href="<?= site_url('journeys/new') ?>" class="text-action hover:underline font-medium">Publiez votre trajet</a>
      </p>

    </div>
  </section>

  <?php if (!empty($nextDriverJourney) || !empty($nextPassengerJourney)): ?>
    <div class="max-w-4xl mx-auto px-4 pt-8 relative z-10">
      <div class="grid grid-cols-1 <?= (!empty($nextDriverJourney) && !empty($nextPassengerJourney)) ? 'sm:grid-cols-2' : '' ?> gap-3 mb-1">
        <?php if (!empty($nextDriverJourney)): ?>
          <a href="<?= site_url('dashboard/journeys') ?>?filter=upcoming" class="text-action text-xs font-medium hover:underline flex items-center gap-1 pl-3">
            Mes trajets conducteur <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        <?php endif; ?>
        <?php if (!empty($nextPassengerJourney)): ?>
          <a href="<?= site_url('dashboard/bookings') ?>?filter=upcoming" class="text-action text-xs font-medium hover:underline flex items-center gap-1 pl-3">
              Mes trajets passager <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-1 <?= (!empty($nextDriverJourney) && !empty($nextPassengerJourney)) ? 'sm:grid-cols-2' : '' ?> gap-3">

        <?php if (!empty($nextDriverJourney)): ?>
          <a href="<?= site_url('journeys/' . $nextDriverJourney['id']) ?>?back=<?= urlencode(current_url(true)) ?>"
            class="flex items-center gap-4 bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
              <i class="fa-solid fa-car-side text-action text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-ink/40 text-xs mb-0.5">Prochain trajet conducteur</p>
              <p class="text-ink font-semibold text-sm truncate">
                <?= esc($nextDriverJourney['city_start_name']) ?>
                <span class="text-ink/30 mx-1">→</span>
                <?= esc($nextDriverJourney['city_end_name']) ?>
              </p>
            </div>
            <div class="flex flex-col items-end gap-0.5 shrink-0">
              <p class="text-ink font-bold text-sm font-display"><?= date('H:i', strtotime($nextDriverJourney['start_datetime'])) ?></p>
              <p class="text-ink/40 text-xs"><?= date('d/m/Y', strtotime($nextDriverJourney['start_datetime'])) ?></p>
            </div>
          </a>
        <?php endif; ?>

        <?php if (!empty($nextPassengerJourney)): ?>
          <a href="<?= site_url('journeys/' . $nextPassengerJourney['id']) ?>?back=<?= urlencode(current_url(true)) ?>"
            class="flex items-center gap-4 bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center shrink-0">
              <i class="fa-solid fa-person-walking text-brand text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-ink/40 text-xs mb-0.5">Prochain trajet passager</p>
              <p class="text-ink font-semibold text-sm truncate">
                <?= esc($nextPassengerJourney['city_start_name']) ?>
                <span class="text-ink/30 mx-1">→</span>
                <?= esc($nextPassengerJourney['city_end_name']) ?>
              </p>
            </div>
            <div class="flex flex-col items-end gap-0.5 shrink-0">
              <p class="text-ink font-bold text-sm font-display"><?= date('H:i', strtotime($nextPassengerJourney['start_datetime'])) ?></p>
              <p class="text-ink/40 text-xs"><?= date('d/m/Y', strtotime($nextPassengerJourney['start_datetime'])) ?></p>
            </div>
          </a>
        <?php endif; ?>

      </div>
    </div>
  <?php endif; ?>

  <!-- Corps -->
  <div class="max-w-4xl mx-auto px-4 py-12 md:py-16 flex flex-col gap-10 relative z-10">

    <!-- Comment ça marche -->
    <div class="flex flex-col gap-6">

      <div class="flex flex-col items-center gap-2 text-center">
        <p class="text-action text-xs font-semibold uppercase tracking-widest">Simple</p>
        <h2 class="text-ink text-2xl font-bold font-display">En 3 étapes</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-3">
          <div class="w-9 h-9 rounded-full bg-action flex items-center justify-center shrink-0">
            <span class="text-ink text-sm font-bold">1</span>
          </div>
          <p class="text-ink text-sm font-semibold">Créez votre compte</p>
          <p class="text-ink/60 text-xs leading-relaxed">Inscrivez-vous gratuitement et ajoutez votre véhicule si vous conduisez.</p>
        </div>

        <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-3">
          <div class="w-9 h-9 rounded-full bg-action flex items-center justify-center shrink-0">
            <span class="text-ink text-sm font-bold">2</span>
          </div>
          <p class="text-ink text-sm font-semibold">Publiez ou réservez</p>
          <p class="text-ink/60 text-xs leading-relaxed">Proposez vos places disponibles ou trouvez un trajet qui correspond à votre itinéraire.</p>
        </div>

        <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-3">
          <div class="w-9 h-9 rounded-full bg-action flex items-center justify-center shrink-0">
            <span class="text-ink text-sm font-bold">3</span>
          </div>
          <p class="text-ink text-sm font-semibold">Partagez la route</p>
          <p class="text-ink/60 text-xs leading-relaxed">Retrouvez-vous au point convenu et partagez les frais. Régulier, économique, local.</p>
        </div>

      </div>

      <div class="text-center">
        <a href="<?= site_url('comment-ca-marche') ?>" class="text-action text-sm hover:underline font-medium">
          En savoir plus <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
        </a>
      </div>
    </div>

    <!-- Valeurs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-action/10">

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-leaf text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Moins de CO₂</p>
        <p class="text-ink/60 text-xs leading-relaxed">Moins de voitures sur les routes pour les mêmes trajets du quotidien.</p>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-handshake text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Mise en relation simple</p>
        <p class="text-ink/60 text-xs leading-relaxed">Conducteurs et passagers se retrouvent en quelques clics, sans intermédiaire.</p>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-shield-halved text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Communauté locale</p>
        <p class="text-ink/60 text-xs leading-relaxed">Conducteurs et passagers d'une même région, collègues ou étudiants.</p>
      </div>

    </div>

    <!-- CTA — uniquement pour les non-connectés -->
    <?php if (!session()->get('isLoggedIn')): ?>
      <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col sm:flex-row items-center gap-4">
        <div class="flex-1 flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Prêt à démarrer ?</p>
          <p class="text-ink/50 text-xs">Rejoignez la communauté Kenweturi et simplifiez vos trajets du quotidien.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
          <a href="<?= site_url('register') ?>"
            class="bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-5 py-2.5 text-center">
            Créer un compte
          </a>
          <a href="<?= site_url('journeys') ?>"
            class="bg-paper hover:bg-action/10 border border-action/20 transition-colors text-ink/70 font-semibold text-sm rounded-xl px-5 py-2.5 text-center">
            Voir les trajets
          </a>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<?= view('partials/footer', ['showRainbowBtn' => true]) ?>