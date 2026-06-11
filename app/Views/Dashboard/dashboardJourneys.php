<?php
/** @var string $title */
/** @var array $journeys */
/** @var string|null $filter */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var \CodeIgniter\Pager\Pager $pager */
?>
<?= view('partials/head') ?>
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
        <a href="<?= site_url('dashboard') ?>" class="ml-auto text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    <!-- Liste -->
    <?php if (empty($journeys)) : ?>
        <p class="text-center text-muted py-12">Aucun trajet trouvé.</p>
    <?php else : ?>
        <div class="space-y-3">
            <?php foreach ($journeys as $journey) : ?>
                <?php if ($journey['canceled_at']): ?>
                    <div class="flex justify-end mb-2">
                        <span class="text-xs text-danger font-semibold bg-danger/10 px-2 py-0.5 rounded-full">Annulé</span>
                    </div>
                <?php endif ?>
                <a href="<?= site_url('journeys/' . esc($journey['id'])) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors <?= $journey['canceled_at'] ? 'opacity-40 pointer-events-none' : '' ?>">
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
                </a>
            <?php endforeach ?>
        </div>

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>
