<?php
/** @var array $booking  */
/** @var bool  $isDriver */

$person_firstname  = $isDriver ? $booking['passenger_firstname']  : $booking['driver_firstname'];
$person_lastname   = $isDriver ? $booking['passenger_lastname']   : $booking['driver_lastname'];
$person_avatar     = $isDriver ? $booking['passenger_avatar']     : $booking['driver_avatar'];
$person_is_student = $isDriver ? $booking['passenger_is_student'] : $booking['driver_is_student'];
$person_label      = $isDriver ? 'Passager' : 'Conducteur';
?>
<?= view('partials/head', ['extraJs' => [base_url('js/modal.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 space-y-4">

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-ink text-2xl font-bold font-display">Détail de la réservation</h1>
        <a href="<?= esc($back ?? base_url('dashboard')) ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>Retour
        </a>
    </div>

    <!-- Itinéraire -->
    <article class="bg-surface-card rounded-2xl p-6">
        <?php $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d MMMM'); ?>
        <p class="text-muted text-sm mb-4 capitalize"><?= $fmt->format(strtotime($booking['start_datetime'])) ?></p>

        <div class="space-y-0">

            <!-- Départ / Pickup -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center shrink-0 w-3">
                    <div class="w-3 h-3 bg-brand rounded-full shrink-0"></div>
                    <div class="w-px flex-1 bg-ink/10 my-1"></div>
                </div>
                <div class="flex-1 min-w-0 pb-5">
                    <p class="text-ink font-bold font-display text-lg leading-none mb-1"><?= esc(date('H:i', strtotime($booking['start_datetime']))) ?></p>
                    <p class="text-ink font-semibold"><?= esc($booking['pickup_city_name'] ?? $booking['city_start_name']) ?></p>
                    <?php $pickup_addr = $booking['pickup_address'] ?? $booking['start_address']; ?>
                    <?php $pickup_city = $booking['pickup_city_name'] ?? $booking['city_start_name']; ?>
                    <?php if (!empty($pickup_addr) && $pickup_addr !== $pickup_city): ?>
                        <p class="text-muted text-sm"><?= esc($pickup_addr) ?></p>
                    <?php endif ?>
                </div>
            </div>

            <!-- Arrivée / Dropoff -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center shrink-0 w-3">
                    <div class="w-3 h-3 rounded-full bg-action shrink-0"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-ink font-semibold"><?= esc($booking['dropoff_city_name'] ?? $booking['city_end_name']) ?></p>
                    <?php $dropoff_addr = $booking['dropoff_address'] ?? $booking['end_address']; ?>
                    <?php $dropoff_city = $booking['dropoff_city_name'] ?? $booking['city_end_name']; ?>
                    <?php if (!empty($dropoff_addr) && $dropoff_addr !== $dropoff_city): ?>
                        <p class="text-muted text-sm"><?= esc($dropoff_addr) ?></p>
                    <?php endif ?>
                </div>
            </div>

        </div>
    </article>

    <!-- Conducteur / Passager -->
    <article class="bg-surface-card rounded-2xl p-6">
        <h2 class="text-muted text-xs font-semibold uppercase tracking-wider mb-4"><?= $person_label ?></h2>
        <div class="flex items-center gap-4">
            <?php $initials = strtoupper(substr($person_firstname, 0, 1) . substr($person_lastname, 0, 1)); ?>
            <?php if (!empty($person_avatar)): ?>
                <div class="w-14 h-14 rounded-full overflow-hidden shrink-0">
                    <img src="<?= site_url(esc($person_avatar)) ?>" alt="Avatar" class="w-full h-full object-cover"
                        onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                </div>
                <div class="hidden w-14 h-14 rounded-full bg-action-dark text-paper font-bold text-base shrink-0 items-center justify-center">
                    <?= $initials ?>
                </div>
            <?php else: ?>
                <div class="flex w-14 h-14 rounded-full bg-action-dark text-paper font-bold text-base shrink-0 items-center justify-center">
                    <?= $initials ?>
                </div>
            <?php endif ?>
            <div>
                <p class="text-ink font-semibold"><?= esc($person_firstname) ?> <?= esc($person_lastname) ?></p>
                <p class="text-muted text-sm"><?= $person_is_student ? 'Étudiant' : 'Formateur' ?></p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-ink/5 text-sm text-muted">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-user text-xs text-brand"></i>
                <?= esc($booking['seat_numbers']) ?> place<?= $booking['seat_numbers'] > 1 ? 's' : '' ?> réservée<?= $booking['seat_numbers'] > 1 ? 's' : '' ?>
            </span>
        </div>
    </article>

    <!-- Actions -->
    <article class="bg-surface-card rounded-2xl p-6">
        <?php if ($isDriver): ?>
            <form action="<?= site_url('dashboard/bookings/' . $booking['id'] . '/accept') ?>" method="post" class="mb-3">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-action text-ink font-bold font-display rounded-full py-3 hover:bg-action-dark transition-colors">
                    Accepter la réservation
                </button>
            </form>
            <form id="form-confirm" action="<?= site_url('dashboard/bookings/' . $booking['id'] . '/reject') ?>" method="post">
                <?= csrf_field() ?>
                <button type="button" onclick="openConfirmModal('Refuser cette réservation ?', 'form-confirm')"
                        class="w-full border border-danger text-danger font-bold font-display rounded-full py-3 hover:bg-danger hover:text-paper transition-colors">
                    Refuser la réservation
                </button>
            </form>
        <?php else: ?>
            <form id="form-confirm" action="<?= site_url('dashboard/bookings/' . $booking['id'] . '/delete') ?>" method="post">
                <?= csrf_field() ?>
                <button type="button" onclick="openConfirmModal('Annuler cette réservation ?', 'form-confirm')"
                        class="w-full border border-danger text-danger font-bold font-display rounded-full py-3 hover:bg-danger hover:text-paper transition-colors">
                    Annuler ma réservation
                </button>
            </form>
        <?php endif ?>
    </article>

</div>

<?= view('partials/footer') ?>