<?php

/** @var string $title */
/** @var array $reports */
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
    <div class="flex items-start justify-between gap-4">
        <h1 class="font-display font-bold text-ink text-2xl flex-1 min-w-0"><?= esc($title) ?></h1>

        <!-- Filtre pill toggle -->
        <div class="flex shrink-0 bg-surface border border-action/15 rounded-full p-1 gap-1" role="group" aria-label="Filtre des signalements">
            <a href="<?= site_url('dashboard/reports') ?>?filter=driver"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'driver' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                Conducteur
            </a>
            <a href="<?= site_url('dashboard/reports') ?>?filter=passenger"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'passenger' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                Passager
            </a>
        </div>
    </div>

    <!-- Sous-navigation -->
    <div class="flex items-center justify-end">
        <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    <!-- Liste -->
    <?php if (empty($reports)) : ?>
        <?php
        $emptyMessages = [
            'driver'    => "Vous n'avez effectué aucun signalement en tant que conducteur.",
            'passenger' => "Vous n'avez effectué aucun signalement en tant que passager.",
        ];
        $emptyMsg = $emptyMessages[$filter] ?? "Vous n'avez effectué aucun signalement.";
        ?>
        <p class="text-center text-muted py-12"><?= $emptyMsg ?></p>
    <?php else : ?>
        <div class="space-y-3">
            <?php foreach ($reports as $report) : ?>
                <?php
                $statusClasses = [
                    'open'       => 'bg-action/10 text-action',
                    'processing' => 'bg-ink/10 text-ink/60',
                    'closed'     => 'bg-success/10 text-success',
                ];
                $statusLabels = [
                    'open'       => 'En attente',
                    'processing' => 'En cours',
                    'closed'     => 'Clôturé',
                ];
                $status = $report['status'] ?? 'open';
                ?>
                <div class="bg-surface rounded-2xl p-5 border border-action/10">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0 space-y-1">
                            <p class="font-semibold text-ink font-display truncate"><?= esc($report['title']) ?></p>
                            <p class="text-sm text-ink/50">
                                <?= esc($report['city_start_name']) ?> → <?= esc($report['city_end_name']) ?>
                                <span class="mx-1">·</span>
                                <?= esc(date('d/m/Y', strtotime($report['start_datetime']))) ?>
                            </p>
                        </div>
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full <?= $statusClasses[$status] ?? 'bg-ink/10 text-ink/50' ?>">
                            <?= $statusLabels[$status] ?? esc($status) ?>
                        </span>
                    </div>

                    <?php if (!empty($report['admin_comment'])) : ?>
                        <div class="mt-3 pt-3 border-t border-action/10 text-xs text-ink/50 flex items-start gap-1.5">
                            <i class="fa-solid fa-comment-dots mt-0.5 text-ink/30 shrink-0"></i>
                            <span><?= esc($report['admin_comment']) ?></span>
                        </div>
                    <?php endif ?>

                    <div class="mt-3 pt-3 border-t border-action/10 text-xs text-ink/40">
                        <?php
                        $date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
                        $date->setTimezone(new DateTimeZone('Europe/Paris'));
                        echo 'Signalement envoyé le ' . $date->format('d/m/Y à H:i');
                        ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>