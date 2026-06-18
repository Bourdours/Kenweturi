<?php
/** @var string $title */
/** @var array $journeys */
/** @var string|null $filter */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var \CodeIgniter\Pager\Pager $pager */
?>
<?= view('partials/head', ['extraJs' => [base_url('js/journeySelection.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

    <!-- En-tête -->
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display font-bold text-ink text-2xl">Mes trajets</h1>

        <!-- Filtre pill toggle -->
        <div class="flex bg-surface border border-action/15 rounded-full p-1 gap-1" role="group" aria-label="Filtre des trajets">
            <a href="<?= site_url('dashboard/journeys') ?>?filter=upcoming"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= ($filter === 'upcoming' || $filter === null) ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                À venir
            </a>
            <a href="<?= site_url('dashboard/journeys') ?>?filter=past"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'past' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                Passés
            </a>
        </div>
    </div>

    <!-- Sous-navigation -->
    <div class="flex items-center justify-between">
        <a href="<?= site_url('journeys/new') ?>" class="inline-flex items-center gap-2 bg-action text-paper text-sm font-medium px-4 py-2 rounded-full hover:opacity-90 transition-opacity <?= $filter === 'past' ? 'invisible' : '' ?>">
            <i class="fa-solid fa-plus text-xs"></i>
            Créer un trajet
        </a>

        <div class="ml-auto flex items-center gap-4">
            <?php if ($filter !== 'past' && !empty($journeys)): ?>
                <button type="button" id="toggleSelectionBtn"
                    class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-check-double text-xs"></i>
                    Sélectionner
                </button>
            <?php endif ?>
            <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Liste -->
    <?php if (empty($journeys)) : ?>
        <p class="text-center text-muted py-12">Aucun trajet trouvé.</p>
    <?php else : ?>
        <form id="bulkCancelForm" action="<?= site_url('dashboard/journeys/cancel-bulk') ?>" method="post">
            <?= csrf_field() ?>

            <div class="space-y-3" id="journeyList">
                <?php foreach ($journeys as $journey) : ?>
                    <a href="<?= site_url('journeys/' . esc($journey['id'])) ?>?back=<?= urlencode(current_url(true)) ?>"
                       data-journey-id="<?= esc($journey['id']) ?>"
                       class="journey-card relative block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors <?= $journey['canceled_at'] ? 'pointer-events-none' : '' ?>">

                        <?php if ($journey['canceled_at']): ?>
                            <div class="absolute top-0.5 right-2 z-10">
                                <span class="text-xs text-danger font-semibold bg-danger/10 px-2 py-0.5 rounded-full">Annulé</span>
                            </div>
                        <?php elseif ($filter !== 'past'): ?>
                            <div class="journey-checkbox absolute top-1.5 right-3 z-10 hidden w-5 h-5 flex items-center justify-center">
                                <input type="checkbox" name="journey_ids[]" value="<?= esc($journey['id']) ?>"
                                    class="w-5 h-5 rounded border-action/30 text-action focus:ring-action/40 cursor-pointer">
                            </div>
                        <?php endif ?>

                        <div class="<?= $journey['canceled_at'] ? 'opacity-40' : '' ?>">
                            <div class="flex items-stretch gap-4">
                                <div class="flex flex-col items-center shrink-0 pt-0.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                                    <div class="w-px flex-1 bg-ink/10 my-1"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                    <p class="text-ink/50 truncate">
                                        <span class="text-ink font-semibold"><?= esc($journey['city_start_name']) ?></span>
                                        <?php if (!empty($journey['address_start']) && $journey['address_start'] !== $journey['city_start_name']): ?>
                                            <span class="font-normal"> - <?= esc($journey['address_start']) ?></span>
                                        <?php endif ?>
                                    </p>
                                    <p class="text-ink/50 truncate">
                                        <span class="text-ink font-semibold"><?= esc($journey['city_end_name']) ?></span>
                                        <?php if (!empty($journey['address_end']) && $journey['address_end'] !== $journey['city_end_name']): ?>
                                            <span class="font-normal"> - <?= esc($journey['address_end']) ?></span>
                                        <?php endif ?>
                                    </p>
                                </div>
                                <div class="text-right shrink-0 space-y-1 self-center">
                                    <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                                    <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($journey['start_datetime']))) ?></p>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                                <span><?= esc($journey['booked_seats']) ?> place<?= $journey['booked_seats'] > 1 ? 's' : '' ?> réservée<?= $journey['booked_seats'] > 1 ? 's' : '' ?></span>
                                <?php if ($filter === 'past'): ?>
                                    <span><?= esc($journey['seats']) ?> place<?= $journey['seats'] > 1 ? 's' : '' ?> au total</span>
                                <?php elseif ($journey['booked_seats'] >= $journey['seats']): ?>
                                    <span>Complet</span>
                                <?php else: ?>
                                    <span>
                                        <?= esc($journey['seats'] - $journey['booked_seats']) ?>
                                        place<?= ($journey['seats'] - $journey['booked_seats']) > 1 ? 's' : '' ?>
                                        restante<?= ($journey['seats'] - $journey['booked_seats']) > 1 ? 's' : '' ?>
                                    </span>
                                <?php endif ?>
                            </div>
                        </div>

                    </a>
                <?php endforeach ?>
            </div>
        </form>

        <!-- Barre d'action flottante (mode sélection) -->
        <div class="grid items-center -mt-4">
            <div id="selectionBar" class="hidden sticky bottom-4 z-50 flex justify-end pointer-events-none col-start-1 row-start-1">
                <div class="bg-ink text-paper rounded-full px-5 py-3 shadow-xl flex items-center gap-4 pointer-events-auto">
                    <span id="selectionCount" class="text-sm font-medium">0 sélectionné</span>
                    <button type="button" id="cancelSelectedBtn"
                        class="bg-danger text-paper text-sm font-semibold rounded-full px-4 py-1.5 hover:opacity-90 transition-opacity">
                        Annuler
                    </button>
                    <button type="button" id="exitSelectionBtn" class="text-paper/60 hover:text-paper text-sm transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="col-start-1 row-start-1 pb-8">
                <?= $pager->makeLinks($page, $perPage, $total) ?>
            </div>
        </div>
    <?php endif ?>

    

</div>

<?= view('partials/footer') ?>