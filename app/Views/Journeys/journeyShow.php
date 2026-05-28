<?php

/** @var array       $journey */
/** @var array       $stages */
/** @var int         $remainingSeats */
/** @var int         $availableSeats */
/** @var string|null $boardingCity */
?>
<?= view('partials/head', ['extraJs' => [base_url('js/journeyShow.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 space-y-4">

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-ink text-2xl font-bold font-display">Détails du trajet</h1>
        <a href="<?= esc($back ?? base_url('journeys')) ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>Retour
        </a>
    </div>

    <!-- Itinéraire -->
    <article class="bg-surface-card rounded-2xl p-6">
        <?php $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d MMMM'); ?>
        <p class="text-muted text-sm mb-4 capitalize"><?= $fmt->format(strtotime($journey['start_datetime'])) ?></p>

        <?php
            $boardingReached = !$boardingCity || $journey['city_start_name'] === $boardingCity;
            $isPastStart     = (bool)$boardingCity && $journey['city_start_name'] !== $boardingCity;
        ?>
        <div class="space-y-0">

            <!-- Départ -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center shrink-0 w-3">
                    <div class="<?= $isPastStart ? 'w-2 h-2 bg-ink/20 mt-0.5' : 'w-3 h-3 bg-brand' ?> rounded-full shrink-0"></div>
                    <div class="w-px flex-1 bg-ink/10 my-1"></div>
                </div>
                <div class="flex-1 min-w-0 pb-5">
                    <p class="<?= $isPastStart ? 'text-muted text-sm' : 'text-ink font-bold font-display text-lg' ?> leading-none mb-1"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                    <p class="<?= $isPastStart ? 'text-muted text-sm' : 'text-ink font-semibold' ?>"><?= esc($journey['city_start_name']) ?></p>
                    <p class="<?= $isPastStart ? 'text-muted/50 text-xs' : 'text-muted text-sm' ?>"><?= esc($journey['address_start']) ?></p>
                </div>
            </div>

            <!-- Étapes -->
            <?php foreach ($stages as $stage) : ?>
                <?php
                    $isBoarding  = (bool)$boardingCity && $stage['city_name'] === $boardingCity;
                    $isPastStage = !$boardingReached && !$isBoarding;
                    if ($isBoarding) $boardingReached = true;
                ?>
                <div class="flex gap-4">
                    <div class="flex flex-col items-center shrink-0 w-3">
                        <div class="<?= $isBoarding ? 'w-3 h-3 bg-brand' : 'w-2 h-2 bg-ink/20 mt-0.5' ?> rounded-full shrink-0"></div>
                        <div class="w-px flex-1 bg-ink/10 my-1"></div>
                    </div>
                    <div class="flex-1 min-w-0 pb-5">
                        <p class="<?= $isBoarding ? 'text-ink font-bold font-display text-lg' : 'text-muted text-sm' ?> leading-none mb-1"><?= esc(substr($stage['departure_time'], 0, 5)) ?></p>
                        <p class="<?= $isBoarding ? 'text-ink font-semibold' : 'text-muted text-sm' ?>"><?= esc($stage['city_name']) ?></p>
                        <p class="<?= $isBoarding ? 'text-muted text-sm' : 'text-muted/50 text-xs' ?>"><?= esc($stage['address']) ?></p>
                    </div>
                </div>
            <?php endforeach ?>

            <!-- Arrivée -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center shrink-0 w-3">
                    <div class="w-3 h-3 rounded-full bg-action shrink-0"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-ink font-bold font-display text-lg leading-none mb-1"><?= esc(date('H:i', strtotime($journey['end_datetime']))) ?></p>
                    <p class="text-ink font-semibold"><?= esc($journey['city_end_name']) ?></p>
                    <p class="text-muted text-sm"><?= esc($journey['address_end']) ?></p>
                </div>
            </div>

        </div>

        <p class="text-muted/60 text-xs mt-4 flex items-start gap-1.5">
            <i class="fa-solid fa-circle-info text-[10px] mt-0.5 shrink-0"></i>
            Les horaires affichés sont des estimations calculées dans des conditions de trafic normales.
        </p>
    </article>

    <!-- Conducteur -->
    <article class="bg-surface-card rounded-2xl p-6">
        <h2 class="text-muted text-xs font-semibold uppercase tracking-wider mb-4">Conducteur</h2>
        <div class="flex items-center gap-4">
            <?php $initials = strtoupper(substr($journey['driver_firstname'], 0, 1) . substr($journey['driver_lastname'], 0, 1)); ?>
            <?php if (!empty($journey['driver_avatar'])): ?>
                <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
                    <img src="<?= site_url(esc($journey['driver_avatar'])) ?>" alt="Avatar de <?= esc($journey['driver_firstname']) ?>" class="w-full h-full object-cover"
                        onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                </div>
                <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                    <?= $initials ?>
                </div>
            <?php else: ?>
                <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            <div>
                <p class="text-ink font-semibold"><?= esc($journey['driver_firstname']) ?> <?= esc($journey['driver_lastname']) ?></p>
                <p class="text-muted text-sm"><?= $journey['driver_is_student'] ? 'Étudiant' : 'Formateur' ?></p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-ink/5 flex flex-wrap gap-3 text-sm text-muted">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-car text-xs text-brand"></i>
                <?= esc($journey['car_brand']) ?> <?= esc($journey['car_model']) ?> · <?= esc($journey['car_color']) ?>
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-<?= $journey['smoking'] ? 'smoking' : 'ban-smoking' ?> text-xs text-brand"></i>
                <?= $journey['smoking'] ? 'Fumeur' : 'Non-fumeur' ?>
            </span>
        </div>
    </article>

    <!-- Passagers -->
    <article class="bg-surface-card rounded-2xl p-6">
        <h2 class="text-muted text-xs font-semibold uppercase tracking-wider mb-4">Passagers</h2>
        <?php if (empty($passengers)): ?>
            <p class="text-muted text-sm">Aucun passager pour l'instant.</p>
        <?php else: ?>
            <div class="flex flex-col gap-4">
                <?php foreach ($passengers as $passenger): ?>
                    <?php $initials = strtoupper(substr($passenger['firstname'], 0, 1) . substr($passenger['lastname'], 0, 1)); ?>
                    <div class="flex items-center gap-4">
                        <?php if (!empty($passenger['avatar'])): ?>
                            <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
                                <img src="<?= site_url(esc($passenger['avatar'])) ?>" alt="Avatar de <?= esc($passenger['firstname']) ?>" class="w-full h-full object-cover"
                                    onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                            </div>
                            <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                                <?= $initials ?>
                            </div>
                        <?php else: ?>
                            <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                                <?= $initials ?>
                            </div>
                        <?php endif ?>
                        <div>
                            <p class="text-ink font-semibold"><?= esc($passenger['firstname']) ?> <?= esc($passenger['lastname']) ?></p>
                            <p class="text-muted text-sm"><?= $passenger['is_student'] ? 'Étudiant' : 'Formateur' ?></p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </article>

    <!-- Réservation -->
    <article class="bg-surface-card rounded-2xl p-6">
        <?php if ($remainingSeats > 0) : ?>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-muted text-sm">Places disponibles</p>
                    <p class="text-ink font-bold font-display text-2xl"><?= esc($remainingSeats) ?></p>
                </div>
            </div>
            <form action="<?= site_url('journeys/' . esc($journey['id']) . '/book') ?>" method="POST" id="formBook">
                <?= csrf_field() ?>
                <?php if (session('user_id') != $journey['user_id']): ?>
                    <button type="button" id="btnOpenBookModal"
                        class="w-full bg-action text-ink font-bold font-display rounded-full py-3 hover:bg-action-dark transition-colors">
                        Réserver <?= $availableSeats > 1 ? $availableSeats . ' places' : '1 place' ?>
                    </button>
                <?php endif ?>
            </form> 

            <!-- Modal confirmation réservation -->
            <div id="modalBook" class="hidden fixed inset-0 w-full h-full bg-black/50 items-center justify-center z-[9999]">
                <div class="bg-surface rounded-2xl p-6 w-[90%] max-w-[400px]">
                    <h2 class="text-action font-semibold font-display mb-2">Confirmer la réservation</h2>
                    <p class="text-ink text-sm mb-6">Vous allez réserver <?= $availableSeats > 1 ? $availableSeats . ' places' : '1 place' ?> sur ce trajet.<br> Êtes-vous sûr ?</p>
                    <div class="flex gap-3">
                        <button type="button" id="btnCancelBook"
                            class="flex-1 border border-ink/20 text-ink rounded-lg px-4 py-2 text-sm font-semibold">
                            Annuler
                        </button>
                        <button type="button" id="btnConfirmBook"
                            class="flex-1 bg-action text-paper rounded-lg px-4 py-2 text-sm font-semibold">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <p class="text-center text-muted font-semibold py-2">Trajet complet</p>
        <?php endif ?>
    </article>

</div>
</div>

<?= view('partials/footer') ?>