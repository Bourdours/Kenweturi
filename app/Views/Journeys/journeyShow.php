<?php

/** @var array       $journey */
/** @var array       $stages */
/** @var int         $remainingSeats */
/** @var int         $availableSeats */
/** @var string|null $boardingCity */
/** @var bool        $isBooked */
/** @var array|null  $userBooking */
?>
<?= view('partials/head', [
    'extraCss' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'],
    'extraJs'  => [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        base_url('js/journeyMap.js'),
        base_url('js/journeyShow.js'),
        base_url('js/modal.js'),
    ],
]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 space-y-4">

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-ink text-2xl font-bold font-display">Détails du trajet</h1>
        <a href="<?= esc($back ?? base_url('journeys')) ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
            <i class="fa-solid fa-arrow-left text-xs"></i>Retour
        </a>
    </div>

    <!-- Itinéraire -->
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
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

    <!-- Map du trajet -->
    <?php
        $mapWaypoints = array_merge(
            [['lat' => $journey['lat_start'], 'lng' => $journey['lng_start'], 'label' => $journey['city_start_name']]],
            array_map(fn($s) => ['lat' => $s['latitude'], 'lng' => $s['longitude'], 'label' => $s['city_name']], $stages),
            [['lat' => $journey['lat_end'],   'lng' => $journey['lng_end'],   'label' => $journey['city_end_name']]]
        );
    ?>
    <?= view('partials/journeyMap', ['waypoints' => $mapWaypoints, 'geojson' => $journey['track_geojson'] ?? null]) ?>

    <!-- Conducteur -->
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="text-muted text-xs font-semibold uppercase tracking-wider">Conducteur</h2>
            <?php if (!empty($journey['note'])): ?>
                <span class="hidden sm:block text-muted text-xs font-semibold uppercase tracking-wider">Par rapport au trajet</span>
            <?php endif ?>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <a href="<?= site_url('users/' . $journey['driver_id']) ?>?back=<?= urlencode(current_url(true)) ?>"
             class="flex items-center gap-4">
                <div class="flex items-center gap-4">
                    <?php $initials = strtoupper(substr($journey['driver_firstname'], 0, 1) . substr($journey['driver_lastname'], 0, 1)); ?>
                    <?php if (!empty($journey['driver_avatar'])): ?>
                        <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
                            <img src="<?= site_url(esc($journey['driver_avatar'])) ?>" alt="Avatar de <?= esc($journey['driver_firstname']) ?>" class="w-full h-full object-cover"
                            onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                        </div>
                        <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                            <?= esc($initials) ?>
                        </div>
                    <?php else: ?>
                        <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                            <?= esc($initials) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-ink font-semibold"><?= esc($journey['driver_firstname']) ?> <?= esc($journey['driver_lastname']) ?></p>
                        <p class="text-muted text-sm"><?= $journey['driver_is_student'] ? 'Étudiant' : 'Formateur' ?></p>
                    </div>
                </div>
            </a>

            <?php if (!empty($journey['note'])): ?>
                <?php $noteLong = mb_strlen($journey['note']) > 120; ?>
                <div class="flex flex-col gap-1.5">
                    <span class="sm:hidden text-muted text-xs font-semibold uppercase tracking-wider">Par rapport au trajet</span>
                    <blockquote class="flex-1 min-w-0 border-l-2 border-action/40 pl-3 text-muted text-sm italic max-w-xs">
                        <?php if ($noteLong): ?>
                            <span class="jsNoteShort"><?= nl2br(esc(mb_substr($journey['note'], 0, 120))) ?>…</span>
                            <span class="jsNoteFull hidden"><?= nl2br(esc($journey['note'])) ?></span>
                            <button type="button" onclick="
                                var s=this.previousElementSibling,sh=s.previousElementSibling;
                                var open=!s.classList.contains('hidden');
                                s.classList.toggle('hidden',open); sh.classList.toggle('hidden',!open);
                                this.textContent=open?'Voir plus':'Voir moins';
                            " class="block mt-1 text-action text-xs hover:underline">Voir plus</button>
                        <?php else: ?>
                            <?= nl2br(esc($journey['note'])) ?>
                        <?php endif ?>
                    </blockquote>
                </div>
            <?php endif ?>
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
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <h2 class="text-muted text-xs font-semibold uppercase tracking-wider mb-4">Passagers</h2>

        <?php if ($isBooked || session('user_id') === $journey['user_id']): ?>

            <?php if (empty($passengers)): ?>
                <p class="text-muted text-sm">Aucun passager pour l'instant.</p>
            <?php else: ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($passengers as $passenger): ?>
                        <a href="<?= site_url('users/' . $passenger['user_id']) ?>?back=<?= urlencode(current_url(true)) ?>"
                        class="flex items-center gap-4 pt-4 first:pt-0 border-t border-ink/5 first:border-t-0">

                            <?php $initials = strtoupper(substr($passenger['firstname'], 0, 1) . substr($passenger['lastname'], 0, 1)); ?>

                            <div class="flex items-center gap-4 pt-4 first:pt-0 border-t border-ink/5 first:border-t-0">
                                <?php if (!empty($passenger['avatar'])): ?>
                                    <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
                                        <img src="<?= site_url(esc($passenger['avatar'])) ?>"
                                            alt="Avatar de <?= esc($passenger['firstname']) ?>"
                                            class="w-full h-full object-cover"
                                            onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                                    </div>
                                    <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                                        <?= esc($initials) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                                        <?= esc($initials) ?>
                                    </div>
                                <?php endif ?>

                                <div>
                                    <p class="text-ink font-semibold"><?= esc($passenger['firstname']) ?> <?= esc($passenger['lastname']) ?></p>
                                    <p class="text-muted text-sm"><?= $passenger['is_student'] ? 'Étudiant' : 'Formateur' ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

        <?php else: ?>
            <p class="text-muted text-sm">
                <?php $count = count($passengers); ?>
                <?= $count === 0
                    ? 'Aucun passager pour l\'instant.'
                    : $count . ' passager' . ($count > 1 ? 's ont' : ' a') . ' réservé ce trajet.' ?>
            </p>
        <?php endif ?>

    </article>

    <!-- Réservation -->
<?php $isFuture = $journey['start_datetime'] > date('Y-m-d H:i:s'); ?>

<article class="bg-surface-card rounded-2xl p-6 border border-action/10">
    <?php if ($isBooked && $isFuture): ?>
        <form id="form-cancel" action="<?= site_url('dashboard/bookings/' . esc($userBooking['id']) . '/delete') ?>" method="POST">
            <?= csrf_field() ?>
            <button type="button" onclick="openConfirmModal('Annuler cette réservation ?', 'form-cancel')"
                class="w-full border border-danger text-danger font-bold font-display rounded-full py-3 hover:bg-danger hover:text-paper transition-colors">
                Annuler ma réservation
            </button>
        </form>

    <?php elseif ($isPending && $isFuture): ?>
        <form id="form-cancel" action="<?= site_url('dashboard/bookings/' . esc($userBooking['id']) . '/delete') ?>" method="POST">
            <?= csrf_field() ?>
            <button type="button" onclick="openConfirmModal('Annuler cette demande de réservation ?', 'form-cancel')"
                class="w-full border border-danger text-danger font-bold font-display rounded-full py-3 hover:bg-danger hover:text-paper transition-colors">
                Annuler cette demande de réservation
            </button>
        </form>

    <?php elseif (!$isFuture): ?>
        <p class="text-center text-muted font-semibold py-2">Trajet terminé</p>

    <?php elseif ($remainingSeats > 0): ?>
        <div class="flex items-center justify-between mb-2">
            <div>
                <p class="text-muted text-sm"><?= $remainingSeats > 1 ? 'Places disponibles' : 'Place disponible' ?></p>
                <p class="text-ink font-bold font-display text-2xl"><?= esc($remainingSeats) ?></p>
            </div>
        </div>
        <?php if (session('user_id') != $journey['user_id']): ?>
            <form action="<?= site_url('journeys/' . esc($journey['id']) . '/book') ?>" method="POST" id="formBook">
                <?= csrf_field() ?>
                <button type="button" id="btnOpenBookModal"
                    class="w-full bg-action text-ink font-bold font-display rounded-full py-3 hover:bg-action-dark transition-colors">
                    Réserver <?= $availableSeats > 1 ? $availableSeats . ' places' : '1 place' ?>
                </button>
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
        <?php endif ?>

    <?php else: ?>
        <p class="text-center text-muted font-semibold py-2">Trajet complet</p>
    <?php endif ?>
</article>
    
    <!-- bouton signaler -->
    <div class="deleteAccount w-full">
        <?php if (($isBooked || session()->get('user_id') == $journey['user_id']) && $journey['start_datetime'] < date('Y-m-d H:i:s')): ?>
            <a href="<?= site_url('journeys/' . $journey['id'] . '/report') ?>"
            class="inline-flex items-center gap-2 bg-danger/10 hover:bg-danger text-danger hover:text-white border border-danger/30 hover:border-danger font-semibold rounded-lg px-4 py-2 text-sm transition-colors cursor-pointer">
                <i class="fa-solid fa-flag text-xs"></i> Signaler un problème
            </a>
        <?php endif ?>
    </div>

    <!-- bouton annuler -->
    <div class="deleteAccount w-full">
        <?php if ($journey['user_id'] === session()->get('user_id') && !$journey['canceled_at'] && $journey['start_datetime'] > date('Y-m-d H:i:s')): ?>
            <form id="form-cancel-journey" action="<?= site_url('journeys/' . $journey['id'] . '/cancel') ?>" method="POST">
                <?= csrf_field() ?>
            </form>
            <button type="button" id="btnOpenCancelModal"
                class="inline-flex items-center gap-2 bg-danger/10 hover:bg-danger text-danger hover:text-white border border-danger/30 hover:border-danger font-semibold rounded-lg px-4 py-2 text-sm transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>Annuler le trajet
            </button>

            <div id="modalCancelJourney" class="hidden fixed inset-0 w-full h-full bg-black/50 items-center justify-center z-[9999]">
                <div class="bg-surface rounded-2xl p-6 w-[90%] max-w-[400px]">
                    <h2 class="text-danger font-semibold font-display mb-2">Annuler le trajet</h2>
                    <p class="text-ink text-sm mb-6">Vous allez annuler ce trajet. Les passagers acceptés seront notifiés par email.<br>Êtes-vous sûr ?</p>
                    <div class="flex gap-3">
                        <button type="button" id="btnCancelCancelModal"
                            class="flex-1 border border-ink/20 text-ink rounded-lg px-4 py-2 text-sm font-semibold">
                            Retour
                        </button>
                        <button type="button" id="btnConfirmCancelJourney"
                            class="flex-1 bg-danger text-paper rounded-lg px-4 py-2 text-sm font-semibold">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?= view('partials/footer') ?>