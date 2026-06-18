<?php
/** @var string $title */
/** @var array $bookings */
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
        <h1 class="font-display font-bold text-ink text-2xl"><?= esc($title) ?></h1>

        <!-- Filtre pill toggle -->
        <?php if (in_array($filter, ['upcoming', 'past'])): ?>
            <div class="flex bg-surface border border-action/15 rounded-full p-1 gap-1" role="group" aria-label="Filtre des trajets">
                <a href="<?= site_url('dashboard/bookings') ?>?filter=upcoming"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'upcoming' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                    À&nbsp;venir
                </a>
                <a href="<?= site_url('dashboard/bookings') ?>?filter=past"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'past' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                    Passés
                </a>
            </div>
        <?php elseif (in_array($filter, ['mine', 'mine-past'])): ?>
            <div class="flex bg-surface border border-action/15 rounded-full p-1 gap-1" role="group" aria-label="Filtre des demandes envoyées">
                <a href="<?= site_url('dashboard/bookings') ?>?filter=mine"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'mine' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                    À&nbsp;venir
                </a>
                <a href="<?= site_url('dashboard/bookings') ?>?filter=mine-past"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $filter === 'mine-past' ? 'bg-action text-ink' : 'text-ink/60 hover:text-ink' ?>">
                    Passées
                </a>
            </div>
        <?php else: ?>
            <div class="flex bg-surface border border-action/15 rounded-full p-1" role="group" aria-label="Filtre des réservations">
                <span class="px-4 py-1.5 rounded-full text-sm font-medium bg-action text-ink">
                    Reçues
                </span>
            </div>
        <?php endif ?>
    </div>

    <!-- Sous-navigation -->
    <div class="flex items-center justify-between">
        <?php if (in_array($filter, ['upcoming', 'past', 'mine', 'mine-past'])): ?>
            <a href="<?= site_url('journeys') ?>" class="inline-flex items-center gap-2 bg-action text-ink text-sm font-medium px-4 py-2 rounded-full hover:opacity-90 transition-opacity">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                Trouver un trajet
            </a>
        <?php else: ?>
            <div class="invisible px-4 py-2 text-sm">placeholder</div>
        <?php endif ?>
        <a href="<?= site_url('dashboard') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    <!-- Liste -->
    <?php if (empty($bookings)) : ?>
        <p class="text-center text-muted py-12">Aucune réservation trouvée.</p>
    <?php else : ?>
        <div class="space-y-3">
            <?php foreach ($bookings as $booking) : ?>
                <?php $cardUrl = in_array($filter, ['upcoming', 'past'])
                    ? site_url('journeys/' . esc($booking['journey_id']))
                    : site_url('dashboard/bookings/' . $booking['id']); ?>
                <a href="<?= $cardUrl ?>?back=<?= urlencode(current_url(true)) ?>" class="block bg-surface rounded-2xl p-5 border border-action/10 hover:border-action/30 transition-colors">
                    <div class="flex items-stretch gap-4">
                        <div class="flex flex-col items-center shrink-0 pt-0.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand shrink-0"></div>
                            <div class="w-px flex-1 bg-ink/10 my-1"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-action shrink-0"></div>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                            <p class="text-ink/50 truncate">
                                <span class="text-ink font-semibold"><?= esc($booking['pickup_city_name'] ?? $booking['city_start_name']) ?></span>
                                <?php $pickupAddr = $booking['pickup_address'] ?? $booking['address_start'] ?? null; ?>
                                <?php if (!empty($pickupAddr) && $pickupAddr !== ($booking['pickup_city_name'] ?? $booking['city_start_name'])): ?>
                                    <span class="font-normal"> - <?= esc($pickupAddr) ?></span>
                                <?php endif ?>
                            </p>
                            <p class="text-ink/50 truncate">
                                <span class="text-ink font-semibold"><?= esc($booking['dropoff_city_name'] ?? $booking['city_end_name']) ?></span>
                                <?php $dropoffAddr = $booking['dropoff_address'] ?? $booking['address_end'] ?? null; ?>
                                <?php if (!empty($dropoffAddr) && $dropoffAddr !== ($booking['dropoff_city_name'] ?? $booking['city_end_name'])): ?>
                                    <span class="font-normal"> - <?= esc($dropoffAddr) ?></span>
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
                                <div class="w-5 h-5 rounded-full overflow-hidden shrink-0">
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

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>

</div>

<?= view('partials/footer') ?>
