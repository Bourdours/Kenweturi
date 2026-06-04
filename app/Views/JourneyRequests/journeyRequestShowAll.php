<?= view('partials/head', [
    'extraCss' => [
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
        base_url('css/flatpickr-theme.css'),
    ],
    'extraJs' => [
        'https://cdn.jsdelivr.net/npm/flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js',
        base_url('js/datepicker.js'),
        base_url('js/requestMessage.js'),
    ],
]) ?>

<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-ink text-2xl font-bold font-display">Demander un trajet</h1>
        <a href="<?= site_url('journey-requests/new') ?>"
           class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-4 py-2 text-sm transition-colors">
            <i class="fa-solid fa-plus text-xs"></i>Publier une demande
        </a>
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
        <p class="text-center text-muted py-8">Aucune demande de trajet pour l'instant.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($journeyRequests as $request): ?>
                <div class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">

                    <div class="flex items-center gap-4">
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                            <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-ink font-semibold truncate"><?= esc($request['city_start_name'] ?? '—') ?></p>
                            <p class="text-ink font-semibold truncate"><?= esc($request['city_end_name'] ?? '—') ?></p>
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

                    <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                        <div class="flex items-center gap-2">
                            <?php $initials = strtoupper(substr($request['firstname'] ?? '?', 0, 1) . substr($request['lastname'] ?? '?', 0, 1)); ?>
                            <?php if (!empty($request['avatar'])): ?>
                                <div class="w-7 h-7 rounded-full overflow-hidden shrink-0">
                                    <img src="<?= site_url(esc($request['avatar'])) ?>" alt="Avatar"
                                         class="w-full h-full object-cover"
                                         onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                </div>
                                <div class="hidden w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= $initials ?>
                                </div>
                            <?php else: ?>
                                <div class="flex w-7 h-7 rounded-full bg-action-dark text-ink font-bold text-xs shrink-0 items-center justify-center">
                                    <?= $initials ?>
                                </div>
                            <?php endif ?>
                            <div>
                                <p class="text-ink font-semibold"><?= esc(($request['firstname'] ?? '') . ' ' . ($request['lastname'] ?? '')) ?></p>
                                <p class="text-muted text-xs"><?= ($request['is_student'] ?? false) ? 'Étudiant' : 'Formateur' ?></p>
                            </div>
                        </div>
                        <?php if (!empty($request['seats'])): ?>
                            <span class="flex items-baseline gap-1">
                                <i class="fa-solid fa-user text-xs"></i>
                                <?= esc($request['seats']) ?> place demandée<?= $request['seats'] > 1 ? 's' : '' ?>
                            </span>
                        <?php endif ?>
                    </div>

                    <?php if (!empty($request['message'])): ?>
                        <div>
                            <button onclick="toggleMessage(this)" class="text-action text-xs mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform"></i>
                                <span>Voir le message</span>
                            </button>
                            <p class="hidden mt-2 text-muted text-sm">"<?= esc($request['message']) ?>"</p>
                        </div>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>