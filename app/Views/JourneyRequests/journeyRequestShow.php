<?php
/** @var string $title */
/** @var array $journeyRequest */
/** @var string|null $back */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

    <h1 class="font-display font-bold text-ink text-2xl"><?= esc($title) ?></h1>

    <!-- Sous-navigation -->
    <div class="flex items-center justify-end">
        <a href="<?= esc($back ?? site_url('dashboard/journey-requests')) ?>"
           class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>Retour
        </a>
    </div>

    <!-- Itinéraire -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
            <i class="fa-solid fa-route text-action text-sm"></i>Itinéraire
        </h2>
        <div class="flex flex-col">
            <div class="flex gap-4 items-start">
                <div class="flex flex-col items-center shrink-0 w-3 pt-1">
                    <div class="w-3 h-3 bg-brand rounded-full shrink-0"></div>
                    <div class="w-px flex-1 bg-ink/10 mt-1 min-h-10"></div>
                </div>
                <div class="flex-1 pb-4">
                    <p class="text-ink/50 text-xs font-medium mb-1">Départ</p>
                    <p class="text-ink font-semibold"><?= esc($journeyRequest['city_start_name']) ?></p>
                    <p class="text-ink/50 text-xs"><?= esc($journeyRequest['address_start']) ?></p>
                </div>
            </div>
            <div class="flex gap-4 items-start">
                <div class="flex flex-col items-center shrink-0 w-3 pt-1">
                    <div class="w-3 h-3 bg-action rounded-full shrink-0"></div>
                </div>
                <div class="flex-1">
                    <p class="text-ink/50 text-xs font-medium mb-1">Arrivée</p>
                    <p class="text-ink font-semibold"><?= esc($journeyRequest['city_end_name']) ?></p>
                    <p class="text-ink/50 text-xs"><?= esc($journeyRequest['address_end']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Date & heure -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
            <i class="fa-solid fa-calendar text-action text-sm"></i>Date & heure
        </h2>
        <?php if (!empty($journeyRequest['start_datetime'])): ?>
            <p class="text-ink font-semibold"><?= esc(date('d/m/Y à H:i', strtotime($journeyRequest['start_datetime']))) ?></p>
        <?php else: ?>
            <p class="text-ink/40 text-sm">Date flexible</p>
        <?php endif ?>
    </div>

    <!-- Message -->
    <?php if (!empty($journeyRequest['message'])): ?>
        <div class="bg-surface rounded-2xl p-6 border border-action/10">
            <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-action text-sm"></i>Message
            </h2>
            <p class="text-ink/70 text-sm"><?= esc($journeyRequest['message']) ?></p>
        </div>
    <?php endif ?>

    <!-- Modifier / Annuler -->
    <div class="flex justify-end gap-3">
        <a href="<?= site_url('journey-requests/' . $journeyRequest['id'] . '/edit?back=' . urlencode(current_url(true))) ?>"
        class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
            <i class="fa-solid fa-pen text-xs"></i>Modifier
        </a>
        <form action="<?= site_url('journey-requests/' . $journeyRequest['id'] . '/cancel') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit"
                class="flex items-center gap-2 border border-red-300 hover:border-red-500 text-red-400 hover:text-red-600 font-medium rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>Annuler la demande
            </button>
        </form>
    </div>

</div>

<?= view('partials/footer') ?>