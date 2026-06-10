<?php
/** @var array       $journeys */
/** @var string|null $startAddress */
/** @var string|null $endAddress */
/** @var string|null $filterDate */
/** @var string|null $filterTime */
/** @var string|null $availableSeats */
/** @var string|null $smoking */
/** @var string|null $latStart */
/** @var string|null $lngStart */
/** @var string|null $latEnd */
/** @var string|null $lngEnd */
/** @var object      $pager */
/** @var int         $page */
/** @var int         $perPage */
/** @var int         $total */
/** @var string|null $back */
?>
<?= view('partials/head', [
    'extraCss' => [
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
        base_url('css/flatpickr-theme.css'),
    ],
    'extraJs'  => [
        'https://cdn.jsdelivr.net/npm/flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js',
        base_url('js/autocomplete.js'),
        base_url('js/journeySearch.js'),
        base_url('js/datepicker.js'),
        base_url('js/timepicker.js'),
        base_url('js/swapAddresses.js'),
    ],
]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 space-y-6">

    <h1 class="text-ink text-2xl font-bold font-display">Chercher un trajet</h1>
    <!-- Formulaire de recherche -->
    <form id="addJourneyForm" action="<?= site_url('/journeys') ?>" method="GET">
        <div class="bg-surface rounded-2xl p-5 border border-action/10 space-y-4">
            <p class="text-ink/40 text-base mb-1">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>Recherche
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-3 sm:gap-x-2 sm:items-end">
                <div>
                    <label for="startAddress" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-circle-dot text-sm"></i>Départ
                    </label>
                    <input type="text" id="startAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" name="startAddress" value="<?= esc($startAddress ?? '') ?>" placeholder="Ville ou adresse">
                </div>
                <div class="flex justify-center sm:mb-1">
                    <button type="button" id="swapAddresses"
                        class="w-8 h-8 flex items-center justify-center bg-paper border border-action/20 rounded-full text-ink/50 hover:text-action hover:border-action/40 transition-colors cursor-pointer"
                        title="Inverser départ et arrivée">
                        <i class="fa-solid fa-right-left rotate-90 sm:rotate-0 text-xs"></i>
                    </button>
                </div>
                <div>
                    <label for="endAddress" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-location-dot text-sm"></i>Arrivée
                    </label>
                    <input type="text" id="endAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" name="endAddress" value="<?= esc($endAddress ?? '') ?>" placeholder="Ville ou adresse">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="date" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-regular fa-calendar text-sm"></i>Date
                    </label>
                    <input type="text" id="date" name="date" value="<?= esc($filterDate ?? '') ?>" readonly placeholder="jj/mm/aaaa" class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer">
                </div>
                <div>
                    <label for="time" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-regular fa-clock text-sm"></i>À partir de
                    </label>
                    <input type="text" id="time" name="time" value="<?= esc($filterTime ?? '') ?>" readonly placeholder="--:--" class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer">
                </div>
                <div>
                    <label for="searchingRadius" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-user text-sm"></i>Rayon en Km
                    </label>
                    <input type="number" step="0.1" min="0.1" id="searchingRadius" name="searchingRadius" value="<?= esc($searchingRadius ?? '') ?>" placeholder="10" class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50">
                </div>
                <div>
                    <label for="smoking" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-smoking text-sm"></i>Fumeur
                    </label>
                    <div class="relative">
                        <select id="smoking" name="smoking" class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none appearance-none cursor-pointer focus:border-action/50">
                            <option value="">Indifférent</option>
                            <option value="0" <?= ($smoking ?? '') === '0' ? 'selected' : '' ?>>Non-fumeur</option>
                            <option value="1" <?= ($smoking ?? '') === '1' ? 'selected' : '' ?>>Fumeur</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 text-sm pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg py-3 transition-colors cursor-pointer text-base">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>Rechercher
            </button>
        </div>
    </form>

    <!-- Résultats -->
    <?php if (empty($journeys)): ?>
        <div class="text-center py-8 space-y-3">
            <p class="text-muted">Aucun trajet trouvé.</p>
            <p class="text-muted/60 text-sm">Vous ne trouvez pas votre bonheur ?</p>
            <a href="<?= site_url('journey-requests/new') ?>"
                class="inline-flex items-center gap-2 text-action hover:text-action-dark text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus text-xs"></i>Publiez une demande de trajet
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($journeys as $journey) : ?>
                <a href="<?= site_url('/journeys/') ?><?= esc($journey['id']) ?>?&boardingCity=<?= urlencode($journey['city_boarding_name']) ?>&startAddress=<?= urlencode($startAddress ?? '') ?>&startLat=<?= esc($latStart ?? '') ?>&startLng=<?= esc($lngStart ?? '') ?>&endAddress=<?= urlencode($endAddress ?? '') ?>&endLat=<?= esc($latEnd ?? '') ?>&endLng=<?= esc($lngEnd ?? '') ?>&back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                            <div class="w-px h-2.5 bg-ink/10 my-0.5"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-ink font-semibold truncate"><?= esc($journey['city_boarding_name']) ?></p>
                            <p class="text-ink font-semibold truncate"><?= esc($journey['city_end_name']) ?></p>
                        </div>
                        <div class="text-right shrink-0 space-y-1">
                            <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                            <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($journey['start_datetime']))) ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-action/10 flex items-start justify-between text-sm text-ink/50">
                        <div class="flex items-center gap-4">
                            <?php $initials = strtoupper(substr($journey['driver_firstname'], 0, 1) . substr($journey['driver_lastname'], 0, 1)); ?>
                            <?php if (!empty($journey['driver_avatar'])): ?>
                                <div class="jsAvatarOpen cursor-pointer w-7 h-7 rounded-full overflow-hidden shrink-0">
                                    <img src="<?= site_url(esc($journey['driver_avatar'])) ?>" alt="Avatar de <?= esc($journey['driver_firstname']) ?>" class="w-full h-full object-cover"
                                    onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                </div>
                                <div class="jsAvatarOpen cursor-pointer hidden w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= esc($initials) ?>
                                </div>
                            <?php else: ?>
                                <div class="jsAvatarOpen cursor-pointer flex w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= esc($initials) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-ink font-semibold"><?= esc($journey['driver_firstname']) ?> <?= esc($journey['driver_lastname']) ?></p>
                                <p class="text-muted text-sm"><?= $journey['driver_is_student'] ? 'Étudiant' : 'Formateur' ?></p>
                            </div>
                        </div>
                        <div class="min-w-[175]">
                            <span class="flex items-baseline gap-1">
                                <i class="fa-solid fa-user text-xs"></i>
                                <span class="tabular-nums"><?= esc($journey['remaining_seats']) ?></span> place disponible<?= $journey['remaining_seats'] > 1 ? 's' : '' ?>
                            </span>
                            <span class="flex items-baseline gap-1 <?= $journey['pending_bookings'] > 0 ? '' : 'invisible' ?>">
                                <i class="fa-solid fa-hourglass-half text-xs"></i>
                                <span class="tabular-nums"><?= esc($journey['pending_bookings']) ?></span> réservation en attente<?= $journey['pending_bookings'] > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach ?>
        </div>

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>