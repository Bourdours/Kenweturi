<?php
/** @var array       $journeyRequests */
/** @var string|null $filterCityStart */
/** @var string|null $filterCityEnd */
/** @var string|null $filterDate */
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
        base_url('js/autocomplete.js'),
        base_url('js/requestSearch.js'),
        base_url('js/requestMessage.js'),
    ],
]) ?>

<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 space-y-6">

    <?php if (session()->has('success')): ?>
      <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 flex gap-3 items-start">
        <i class="fa-solid fa-circle-check text-green-500 text-base shrink-0 mt-0.5"></i>
        <p class="text-ink text-sm"><?= esc(session('success')) ?></p>
      </div>
    <?php endif ?>

    <?php if (session()->has('error')): ?>
      <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
        <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
        <p class="text-ink text-sm"><?= esc(session('error')) ?></p>
      </div>
    <?php endif ?>

    <div class="flex items-center justify-between">
        <h1 class="text-ink text-2xl font-bold font-display">Demander un trajet</h1>
        <div class="flex items-center gap-2">
            <?php if (!empty($userRequestsCount)): ?>
            <a href="<?= site_url('dashboard/journey-requests') ?>"
               class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium font-display rounded-lg px-4 py-1.5 text-sm transition-colors">
                <i class="fa-solid fa-list text-xs"></i>Mes demandes
            </a>
            <?php endif ?>
            <a href="<?= site_url('journey-requests/new') ?>"
               class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-4 py-1.5 text-sm transition-colors">
                <i class="fa-solid fa-plus text-xs"></i>Publier une demande
            </a>
        </div>
    </div>

    <!-- Formulaire de recherche -->
    <form action="<?= site_url('journey-requests') ?>" method="GET">
        <div class="bg-surface rounded-2xl p-5 border border-action/10 space-y-4">
            <p class="text-ink/40 text-base mb-1">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>Filtrer
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="cityStart" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-circle-dot text-sm"></i>Départ
                    </label>
                    <input type="text" id="cityStart" name="cityStart"
                           value="<?= esc($filterCityStart ?? '') ?>"
                           placeholder="Ville de départ"
                           class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30">
                </div>
                <div>
                    <label for="cityEnd" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-solid fa-location-dot text-sm"></i>Arrivée
                    </label>
                    <input type="text" id="cityEnd" name="cityEnd"
                           value="<?= esc($filterCityEnd ?? '') ?>"
                           placeholder="Ville d'arrivée"
                           class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30">
                </div>
                <div>
                    <label for="date" class="text-ink/50 text-sm mb-1.5 flex items-baseline gap-1">
                        <i class="fa-regular fa-calendar text-sm"></i>Date
                    </label>
                    <input type="text" id="date" name="date" readonly
                           value="<?= esc($filterDate ?? '') ?>"
                           placeholder="jj/mm/aaaa"
                           class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50 cursor-pointer">
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg py-3 transition-colors cursor-pointer text-base">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>Rechercher
            </button>
        </div>
    </form>

    <!-- Résultats -->
    <?php if (empty($journeyRequests)): ?>
        <div class="text-center py-8 space-y-3">
            <p class="text-muted">Aucune demande de trajet pour l'instant.</p>
            <p class="text-muted/60 text-sm">Vous ne trouvez pas de conducteur ?</p>
            <a href="<?= site_url('journey-requests/new') ?>"
                class="inline-flex items-center gap-2 text-action hover:text-action-dark text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus text-xs"></i>Publiez une demande de trajet
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($journeyRequests as $request): ?>
                <div class="bg-surface rounded-2xl p-5 border border-action/10">

                    <div class="flex items-start gap-4">
                        <div class="grid grid-cols-[10px_1fr] gap-x-4 flex-1 min-w-0 items-center">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand justify-self-center"></div>
                            <p class="text-ink font-semibold truncate"><?= esc($request['city_start_name'] ?? '—') ?></p>
                            <div class="w-px self-stretch bg-ink/10 justify-self-center"></div>
                            <p class="text-ink/40 text-xs truncate py-0.5"><?= esc(explode(',', $request['address_start'] ?? '')[0]) ?></p>
                            <div class="w-2.5 h-2.5 rounded-full bg-action justify-self-center"></div>
                            <p class="text-ink font-semibold truncate"><?= esc($request['city_end_name'] ?? '—') ?></p>
                            <div></div>
                            <p class="text-ink/40 text-xs truncate pt-0.5"><?= esc(explode(',', $request['address_end'] ?? '')[0]) ?></p>
                        </div>
                        <div class="text-right shrink-0 space-y-1">
                            <?php if (!empty($request['start_datetime'])): ?>
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($request['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($request['start_datetime']))) ?></p>
                            <?php else: ?>
                                <p class="text-ink/40 text-xs">Date flexible</p>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-action/10 flex items-start justify-between text-sm text-ink/50">
                        <div class="flex items-center gap-4">
                            <?php $initials = strtoupper(substr($request['firstname'] ?? '?', 0, 1) . substr($request['lastname'] ?? '?', 0, 1)); ?>
                            <?php if (!empty($request['avatar'])): ?>
                                <div class="jsAvatarOpen cursor-pointer w-7 h-7 rounded-full overflow-hidden shrink-0">
                                    <img src="<?= site_url(esc($request['avatar'])) ?>" alt="Avatar"
                                         class="w-full h-full object-cover"
                                         onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                </div>
                                <div class="jsAvatarOpen cursor-pointer hidden w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= $initials ?>
                                </div>
                            <?php else: ?>
                                <div class="jsAvatarOpen cursor-pointer flex w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= $initials ?>
                                </div>
                            <?php endif ?>
                            <div>
                                <p class="text-ink font-semibold"><?= esc(($request['firstname'] ?? '') . ' ' . ($request['lastname'] ?? '')) ?></p>
                                <p class="text-muted text-sm"><?= ($request['is_student'] ?? false) ? 'Étudiant' : 'Formateur' ?></p>
                            </div>
                        </div>
                        <?php if (!empty($request['seats'])): ?>
                            <div class="min-w-[175px]">
                                <span class="flex items-baseline gap-1">
                                    <i class="fa-solid fa-user text-xs"></i>
                                    <span class="tabular-nums"><?= esc($request['seats']) ?></span> place demandée<?= $request['seats'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                        <?php endif ?>
                    </div>

                    <?php if (!empty($request['message'])): ?>
                        <div>
                            <button onclick="toggleMessage(this)" class="text-action text-xs mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform"></i>
                                <span>Voir le message</span>
                            </button>
                            <p class="hidden mt-2 text-muted text-sm"><?= esc($request['message']) ?></p>
                        </div>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>