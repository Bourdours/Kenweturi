<?= view('partials/head') ?>
<?= view('partials/header') ?>

    <?php if (empty($nextJourneys)) : ?>
        <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="space-y-3">
                <?php foreach ($nextJourneys as $journey) : ?>
                    <a href="/journeys/<?= esc($journey['id']) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col items-center shrink-0">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                                <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-ink font-semibold truncate"><?= esc($journey['city_start_name']) ?></p>
                                <p class="text-ink font-semibold truncate"><?= esc($journey['city_end_name']) ?></p>
                            </div>
                            <div class="text-right shrink-0 space-y-1">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($journey['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <span><?= esc($journey['booked_seats']) ?> place<?= $journey['booked_seats'] > 1 ? 's' : '' ?> réservée<?= $journey['booked_seats'] > 1 ? 's' : '' ?></span>
                            <?php if ($journey['booked_seats'] >= $journey['seats']): ?>
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
        <?php endif ?>

    <?php if (empty($lastJourneys)) : ?>
        <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="space-y-3">
                <?php foreach ($lastJourneys as $journey) : ?>
                    <a href="/journeys/<?= esc($journey['id']) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col items-center shrink-0">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                                <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-ink font-semibold truncate"><?= esc($journey['city_start_name']) ?></p>
                                <p class="text-ink font-semibold truncate"><?= esc($journey['city_end_name']) ?></p>
                            </div>
                            <div class="text-right shrink-0 space-y-1">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($journey['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <span><?= esc($journey['booked_seats']) ?> place<?= $journey['booked_seats'] > 1 ? 's' : '' ?> réservée<?= $journey['booked_seats'] > 1 ? 's' : '' ?></span>
                            <span><?= esc($journey['seats']) ?> place<?= $journey['seats'] > 1 ? 's' : '' ?> au total</span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
    <?php endif ?>

    <?php if (empty($nextBookings)) : ?>
        <p class="text-center text-muted py-8">Aucune réservation trouvée.</p>
        <?php else : ?>
            <div class="space-y-3">
                <?php foreach ($nextBookings as $booking) : ?>
                    <a href="/journeys/<?= esc($booking['journey_id']) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col items-center shrink-0">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                                <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-ink font-semibold truncate"><?= esc($booking['pickup_city_name']) ?></p>
                                <p class="text-ink font-semibold truncate"><?= esc($booking['dropoff_city_name']) ?></p>
                            </div>
                            <div class="text-right shrink-0 space-y-1">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <div class="flex items-center gap-2">
                                <?php if ($booking['passenger_avatar']): ?>
                                    <img src="<?= esc($booking['passenger_avatar']) ?>" class="w-5 h-5 rounded-full object-cover">
                                <?php endif ?>
                                <span><?= esc($booking['passenger_firstname']) ?> <?= esc($booking['passenger_lastname']) ?></span>
                            </div>
                            <span><?= $booking['passenger_is_student'] ? 'Élève' : 'Formateur' ?></span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
    <?php endif ?>
    
    <?php if (empty($lastReport)) : ?>
        <p class="text-center text-muted py-8">Aucun signalement trouvé.</p>
        <?php else : ?>
            <a href="/dashboard/reports/<?= esc($lastReport['id']) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-2.5 h-2.5 rounded-full bg-brand"></div>
                        <div class="w-px h-6 bg-ink/10 my-0.5"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-action"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-ink font-semibold truncate"><?= esc($lastReport['city_start_name']) ?></p>
                        <p class="text-ink font-semibold truncate"><?= esc($lastReport['city_end_name']) ?></p>
                    </div>
                    <div class="text-right shrink-0 space-y-1">
                        <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($lastReport['start_datetime']))) ?></p>
                        <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($lastReport['start_datetime']))) ?></p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                    <span><?= esc($lastReport['title']) ?></span>
                    <span><?= esc(date('d/m/Y', strtotime($lastReport['created_at']))) ?></span>
                </div>
            </a>
    <?php endif ?>

<?= view('partials/footer') ?>