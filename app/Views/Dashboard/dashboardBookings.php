<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 space-y-4">

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-ink text-2xl font-bold font-display"><?= esc($title) ?></h1>
        <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>Retour
        </a>
    </div>

    <?php if (empty($bookings)) : ?>
        <p class="text-center text-muted py-8">Aucune réservation trouvée.</p>
    <?php else : ?>
        <div class="space-y-3">
        <?php foreach ($bookings as $booking) : ?>
            <a href="<?= site_url('dashboard/bookings/' . $booking['id']) ?>" class="block bg-surface-card rounded-2xl p-5 border border-ink/5 hover:border-action/30 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-center shrink-0 w-3">
                        <div class="w-3 h-3 rounded-full bg-brand shrink-0"></div>
                        <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                        <div class="w-3 h-3 rounded-full bg-action shrink-0"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-ink font-semibold truncate"><?= esc($booking['pickup_city_name'] ?? $booking['city_start_name']) ?></p>
                        <p class="text-ink font-semibold truncate"><?= esc($booking['dropoff_city_name'] ?? $booking['city_end_name']) ?></p>
                    </div>
                    <div class="text-right shrink-0 space-y-1">
                        <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                        <p class="text-ink/40 text-xs capitalize">
                            <?php $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'd MMM'); ?>
                            <?= $fmt->format(strtotime($booking['start_datetime'])) ?>
                        </p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-ink/5 flex items-center justify-between text-sm text-muted">
                    <div class="flex items-center gap-2">
                        <?php if (!empty($booking['passenger_avatar'])): ?>
                            <img src="/<?= esc($booking['passenger_avatar']) ?>" class="w-5 h-5 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-5 h-5 rounded-full bg-action-dark text-paper text-xs flex items-center justify-center font-bold">
                                <?= strtoupper(substr($booking['passenger_firstname'], 0, 1)) ?>
                            </div>
                        <?php endif ?>
                        <span><?= esc($booking['passenger_firstname']) ?> <?= esc($booking['passenger_lastname']) ?></span>
                    </div>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-user text-xs text-brand"></i>
                        <?= $booking['passenger_is_student'] ? 'Étudiant' : 'Formateur' ?>
                    </span>
                </div>
            </a>
        <?php endforeach ?>
        </div>

        <!-- Pagination -->
        <?php if ($total > $perPage) : ?>
            <div class="pt-4">
                <?= $pager->makeLinks($page, $perPage, $total) ?>
            </div>
        <?php endif ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>