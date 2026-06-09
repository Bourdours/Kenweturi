<?php
/** @var string $title */
/** @var array $journeyRequests */
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
        <h1 class="font-display font-bold text-ink text-2xl"><?= esc($title) ?></h1>
    </div>

    <!-- Sous-navigation -->
    <div class="flex items-center justify-end">
        <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    <!-- Liste -->
    <?php if (empty($journeyRequests)) : ?>
        <p class="text-center text-muted py-12">Vous n'avez effectué aucune demande de trajet.</p>
    <?php else : ?>
        <div class="space-y-3">
            <?php foreach ($journeyRequests as $request) : ?>
                <a href="<?= site_url('dashboard/journey-requests/' . $request['id'] . '?back=' . urlencode(current_url(true))) ?>"
                   class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">

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
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m/Y', strtotime($request['start_datetime']))) ?></p>
                            <?php else: ?>
                                <p class="text-ink/40 text-xs">Date flexible</p>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-action/10 text-xs text-ink/40">
                        <?php
                        $date = new DateTime($request['created_at'], new DateTimeZone('UTC'));
                        $date->setTimezone(new DateTimeZone('Europe/Paris'));
                        echo 'Demande envoyée le ' . $date->format('d/m/Y à H:i');
                        ?>
                    </div>

                </a>
            <?php endforeach ?>
        </div>

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>