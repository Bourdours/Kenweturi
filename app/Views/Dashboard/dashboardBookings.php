<?= view('partials/head') ?>
<?= view('partials/header') ?>

        <div class="flex items-center justify-between mb-2">
            <h1 class="text-ink font-bold text-xl mb-4"><?= esc($title) ?></h1>
            <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-left text-xs"></i>Retour
            </a>
        </div>
            <?php if (empty($bookings)) : ?>
                <p class="text-center text-muted py-8">Aucune réservation trouvée.</p>
                <?php else : ?>
                    <div class="space-y-3">
                        <?php foreach ($bookings as $booking) : ?>
                            <a href="<?= site_url('dashboard/bookings/' . $booking['id']) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-center shrink-0">
                                        <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                                        <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-ink font-semibold truncate"><?= esc($booking['pickup_city_name'] ?? $booking['city_start_name']) ?></p>
                                        <p class="text-ink font-semibold truncate"><?= esc($booking['dropoff_city_name'] ?? $booking['city_end_name']) ?></p>
                                    </div>
                                    <div class="text-right shrink-0 space-y-1">
                                        <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                        <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                                    <div class="flex items-center gap-2">
                                        <?php if (!empty($booking['person_avatar'])): ?>
                                            <img src="/<?= esc($booking['person_avatar']) ?>" class="w-5 h-5 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="w-5 h-5 rounded-full bg-action-dark text-paper text-xs flex items-center justify-center font-bold">
                                                <?= strtoupper(substr($booking['person_firstname'], 0, 1)) ?>
                                            </div>
                                        <?php endif ?>
                                        <span><?= esc($booking['person_firstname']) ?> <?= esc($booking['person_lastname']) ?></span>
                                    </div>
                                    <span><?= $booking['person_is_student'] ? 'Élève' : 'Formateur' ?></span>
                                </div>
                            </a>
                        <?php endforeach ?>
                    </div>
            <?php endif ?>

<?= view('partials/footer') ?>