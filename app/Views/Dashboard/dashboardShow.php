<?= view('partials/head', ['extraJs' => [base_url('js/dashboard.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto px-4 py-6 space-y-8">

    <!-- En-tête -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-ink text-2xl">Bonjour, <?= esc(session()->get('firstname')) ?></h1>
            <p class="text-muted text-sm mt-1">Voici un résumé de votre activité.</p>
        </div>
        <div class="flex shrink-0 bg-surface border border-action/15 rounded-full p-1 gap-1 mt-1" role="group" aria-label="Mode d'affichage">
            <button id="toggle-passenger" type="button" onclick="setDashboardRole('passenger')"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors">
                Passager
            </button>
            <button id="toggle-driver" type="button" onclick="setDashboardRole('driver')"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors">
                Conducteur
            </button>
        </div>
    </div>

    <!-- Prochains trajets -->
    <div data-role="driver">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-car text-brand text-sm"></i>
                Mes prochains trajets
            </h2>
            <?php if (!empty($nextJourneys)) : ?>
                <a href="<?= site_url('dashboard/journeys') ?>?filter=upcoming" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($nextJourneys)) : ?>
            <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($nextJourneys as $journey) : ?>
                    <a href="<?= site_url('journeys/' . esc($journey['id'])) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
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
    </div>

    <!-- Réservations reçues -->
    <div data-role="driver">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-inbox text-brand text-sm"></i>
                Mes demandes de réservation reçues
            </h2>
            <?php if (!empty($nextBookings)) : ?>
                <a href="<?= site_url('dashboard/bookings') ?>" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($nextBookings)) : ?>
            <p class="text-center text-muted py-8">Aucune réservation trouvée.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($nextBookings as $booking) : ?>
                    <a href="<?= site_url('dashboard/bookings/' . $booking['id']) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center shrink-0 pt-0.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                                <div class="w-px flex-1 bg-ink/10 my-1"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_start_name']) ?></span>
                                    <?php if (!empty($booking['address_start']) && $booking['address_start'] !== $booking['city_start_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_start']) ?></span>
                                    <?php endif ?>
                                </p>
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_end_name']) ?></span>
                                    <?php if (!empty($booking['address_end']) && $booking['address_end'] !== $booking['city_end_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_end']) ?></span>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="text-right shrink-0 space-y-1 self-center">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <div class="flex items-center gap-2">
                                <?php $initials = strtoupper(substr($booking['person_firstname'], 0, 1) . substr($booking['person_lastname'], 0, 1)); ?>

                                <?php if (!empty($booking['person_avatar'])): ?>
                                    <div class="w-6 h-6 rounded-full overflow-hidden shrink-0">
                                        <img src="<?= site_url(esc($booking['person_avatar'])) ?>" alt="Avatar de <?= esc($booking['person_firstname']) ?>" class="w-full h-full object-cover"
                                            onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                    </div>
                                    <div class="hidden w-6 h-6 rounded-full bg-action-dark text-ink text-xs shrink-0 items-center justify-center font-bold">
                                        <?= esc($initials) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="flex w-6 h-6 rounded-full bg-action-dark text-ink text-xs shrink-0 items-center justify-center font-bold">
                                        <?= esc($initials) ?>
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
    </div>

    <!-- Trajets passés -->
    <div data-role="driver">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-brand text-sm"></i>
                Trajets passés
            </h2>
            <?php if (!empty($lastJourneys)) : ?>
                <a href="<?= site_url('dashboard/journeys') ?>?filter=past" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($lastJourneys)) : ?>
            <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($lastJourneys as $journey) : ?>
                    <a href="<?= site_url('journeys/' . esc($journey['id'])) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
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
                            <span><?= esc($journey['seats']) ?> place<?= $journey['seats'] > 1 ? 's' : '' ?> au total</span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <!-- Mes prochains trajets (passager) -->
    <div data-role="passenger">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-route text-brand text-sm"></i>
                Mes prochains trajets
            </h2>
            <?php if (!empty($myNextJourneys)) : ?>
                <a href="<?= site_url('dashboard/bookings') ?>?filter=upcoming" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($myNextJourneys)) : ?>
            <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($myNextJourneys as $booking) : ?>
                    <a href="<?= site_url('journeys/' . esc($booking['journey_id'])) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center shrink-0 pt-0.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                                <div class="w-px flex-1 bg-ink/10 my-1"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_start_name']) ?></span>
                                    <?php if (!empty($booking['address_start']) && $booking['address_start'] !== $booking['city_start_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_start']) ?></span>
                                    <?php endif ?>
                                </p>
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_end_name']) ?></span>
                                    <?php if (!empty($booking['address_end']) && $booking['address_end'] !== $booking['city_end_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_end']) ?></span>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="text-right shrink-0 space-y-1 self-center">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <span><?= esc($booking['seat_numbers']) ?> place<?= $booking['seat_numbers'] > 1 ? 's' : '' ?> réservée<?= $booking['seat_numbers'] > 1 ? 's' : '' ?></span>
                            <span><?= esc($booking['driver_firstname']) ?> <?= esc($booking['driver_lastname']) ?></span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <!-- Mes demandes de réservation -->
    <div data-role="passenger">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-brand text-sm"></i>
                Mes demandes de réservation
            </h2>
            <?php if (!empty($myBookings)) : ?>
                <a href="<?= site_url('dashboard/bookings') ?>?filter=mine" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($myBookings)) : ?>
            <p class="text-center text-muted py-8">Aucune réservation trouvée.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($myBookings as $booking) : ?>
                    <a href="<?= site_url('dashboard/bookings/' . $booking['id']) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center shrink-0 pt-0.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                                <div class="w-px flex-1 bg-ink/10 my-1"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_start_name']) ?></span>
                                    <?php if (!empty($booking['address_start']) && $booking['address_start'] !== $booking['city_start_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_start']) ?></span>
                                    <?php endif ?>
                                </p>
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_end_name']) ?></span>
                                    <?php if (!empty($booking['address_end']) && $booking['address_end'] !== $booking['city_end_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_end']) ?></span>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="text-right shrink-0 space-y-1 self-center">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <span><?= esc($booking['seat_numbers']) ?> place<?= $booking['seat_numbers'] > 1 ? 's' : '' ?> réservée<?= $booking['seat_numbers'] > 1 ? 's' : '' ?></span>
                            <span><?= esc(date('d/m/Y', strtotime($booking['sent_at']))) ?></span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <!-- Trajets passés (passager) -->
    <div data-role="passenger">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-brand text-sm"></i>
                Trajets passés
            </h2>
            <?php if (!empty($lastPassengerJourneys)) : ?>
                <a href="<?= site_url('dashboard/bookings') ?>?filter=past" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
            <?php endif ?>
        </div>
        <?php if (empty($lastPassengerJourneys)) : ?>
            <p class="text-center text-muted py-8">Aucun trajet trouvé.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 gap-3 overflow-y-auto max-h-[19.5rem] pr-2 scrollbar-hover sm:flex sm:flex-row sm:overflow-x-auto sm:overflow-y-hidden sm:max-h-none sm:pr-0 sm:pb-2 sm:snap-x sm:snap-mandatory">
                <?php foreach ($lastPassengerJourneys as $booking) : ?>
                    <a href="<?= site_url('journeys/' . esc($booking['journey_id'])) ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors sm:shrink-0 sm:w-[calc(50%-6px)] sm:snap-start sm:[&:only-child]:w-full">
                        <div class="flex items-stretch gap-4">
                            <div class="flex flex-col items-center shrink-0 pt-0.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                                <div class="w-px flex-1 bg-ink/10 my-1"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_start_name']) ?></span>
                                    <?php if (!empty($booking['address_start']) && $booking['address_start'] !== $booking['city_start_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_start']) ?></span>
                                    <?php endif ?>
                                </p>
                                <p class="text-ink/50 truncate">
                                    <span class="text-ink font-semibold"><?= esc($booking['city_end_name']) ?></span>
                                    <?php if (!empty($booking['address_end']) && $booking['address_end'] !== $booking['city_end_name']): ?>
                                        <span class="font-normal"> - <?= esc($booking['address_end']) ?></span>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="text-right shrink-0 space-y-1 self-center">
                                <p class="text-ink font-bold font-display"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                                <p class="text-ink/40 text-xs"><?= esc(date('d/m', strtotime($booking['start_datetime']))) ?></p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-action/10 flex items-center justify-between text-sm text-ink/50">
                            <span><?= esc($booking['seat_numbers']) ?> place<?= $booking['seat_numbers'] > 1 ? 's' : '' ?> réservée<?= $booking['seat_numbers'] > 1 ? 's' : '' ?></span>
                            <span><?= esc($booking['driver_firstname']) ?> <?= esc($booking['driver_lastname']) ?></span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>

    <!-- Signalements -->
    <div class="flex items-center justify-between">
        <h2 class="font-display font-bold text-ink text-base flex items-center gap-2">
            <i class="fa-solid fa-flag text-brand text-sm"></i>
            Mes signalements
        </h2>
        <a data-role="driver" href="<?= site_url('dashboard/reports') ?>?filter=driver" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
        <a data-role="passenger" href="<?= site_url('dashboard/reports') ?>?filter=passenger" class="text-brand text-sm font-medium hover:text-action transition-colors">Voir tous</a>
    </div>

</div>

<?= view('partials/footer') ?>
