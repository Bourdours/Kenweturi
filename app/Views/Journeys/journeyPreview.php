<?php

/** @var bool   $isRecurring */
/** @var array  $journey */
/** @var array  $stages */
/** @var int    $remainingSeats */
/** @var array  $recurringDates */
/** @var array  $recurringDatesByDay */
/** @var int    $recurringCount */
/** @var string $startTime */
?>
<?= view('partials/head', [
    'extraCss' => ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'],
    'extraJs'  => [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        base_url('js/journeyMap.js'),
    ],
]) ?>
<?= view('partials/header') ?>

<style>
    html {
        scroll-behavior: smooth;
    }
</style>

<div class="max-w-4xl mx-auto py-10 px-4 space-y-4">

    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-ink text-2xl font-bold font-display">Confirmer le trajet</h1>
            <div class="flex gap-2 shrink-0">
                <a href="#actions"
                    class="inline-flex items-center gap-1.5 bg-action hover:bg-action-dark text-ink font-bold font-display text-sm rounded-full px-4 py-1.5 transition-colors">
                    <i class="fa-solid fa-check text-xs"></i>Valider
                </a>
                <a href="#actions"
                    class="inline-flex items-center gap-1.5 border border-action/30 hover:border-action/60 text-ink/70 hover:text-ink font-semibold font-display text-sm rounded-full px-4 py-1.5 transition-colors">
                    <i class="fa-solid fa-pen text-xs"></i>Modifier
                </a>
            </div>
        </div>
        <p class="text-muted text-sm mt-1">Vérifiez les informations avant de publier.</p>
    </div>

    <!-- Itinéraire -->
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <?php if ($isRecurring): ?>
            <p class="text-muted text-sm mb-4">Horaires du trajet · <?= esc($startTime) ?></p>
        <?php else: ?>
            <?php $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d MMMM'); ?>
            <p class="text-muted text-sm mb-4 capitalize"><?= $fmt->format(strtotime($journey['start_datetime'])) ?></p>
        <?php endif ?>

        <div class="space-y-0">

            <!-- Départ -->
            <div class="flex gap-4">
                <div class="flex flex-col items-center shrink-0 w-3">
                    <div class="w-3 h-3 bg-brand rounded-full shrink-0"></div>
                    <div class="w-px flex-1 bg-ink/10 my-1"></div>
                </div>
                <div class="flex-1 min-w-0 pb-5">
                    <p class="text-ink font-bold font-display text-lg leading-none mb-1"><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
                    <p class="text-ink font-semibold"><?= esc($journey['city_start_name']) ?></p>
                    <p class="text-muted text-sm"><?= esc($journey['address_start']) ?></p>
                </div>
            </div>

            <!-- Étapes -->
            <?php foreach ($stages as $stage) : ?>
                <div class="flex gap-4">
                    <div class="flex flex-col items-center shrink-0 w-3">
                        <div class="w-2 h-2 bg-ink/20 mt-0.5 rounded-full shrink-0"></div>
                        <div class="w-px flex-1 bg-ink/10 my-1"></div>
                    </div>
                    <div class="flex-1 min-w-0 pb-5">
                        <p class="text-muted text-sm leading-none mb-1"><?= esc(substr($stage['departure_time'], 0, 5)) ?></p>
                        <p class="text-muted text-sm"><?= esc($stage['city_name']) ?></p>
                        <p class="text-muted/50 text-xs"><?= esc($stage['address']) ?></p>
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

    <?php if ($isRecurring): ?>
        <!-- Résumé des dates récurrentes -->
        <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
            <h2 class="text-ink text-base font-semibold font-display mb-4 flex items-center gap-2">
                <i class="fa-solid fa-rotate text-action text-sm"></i>
                <?= $recurringCount ?> trajet<?= $recurringCount > 1 ? 's' : '' ?> seront créés
            </h2>
            <?php
            $dayLabels = ['lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi', 'vendredi' => 'Vendredi'];
            ?>
            <div class="flex flex-wrap gap-6">
                <?php foreach ($recurringDatesByDay as $day => $dates): ?>
                    <div class="flex-1 min-w-[130px]">
                        <p class="text-action text-xs font-bold uppercase tracking-wider mb-2"><?= $dayLabels[$day] ?></p>
                        <ul class="flex flex-col gap-1.5">
                            <?php foreach ($dates as $date): ?>
                                <li class="flex items-center gap-1.5 text-sm text-ink/70">
                                    <i class="fa-solid fa-calendar-day text-action/50 text-xs shrink-0"></i>
                                    <?= esc($date) ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endforeach ?>
            </div>
            <p class="text-muted/60 text-xs mt-4 flex items-center gap-1.5">
                <i class="fa-solid fa-clock text-[10px] shrink-0"></i>
                Tous les trajets partent à <?= esc($startTime) ?>
            </p>
        </article>
    <?php endif ?>

    <!-- Conducteur -->
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="text-muted text-xs font-semibold uppercase tracking-wider">Conducteur</h2>
            <?php if (!empty($journey['note'])): ?>
                <span class="hidden sm:block text-muted text-xs font-semibold uppercase tracking-wider">Par rapport au trajet</span>
            <?php endif ?>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <?php $initials = strtoupper(substr($journey['driver_firstname'], 0, 1) . substr($journey['driver_lastname'], 0, 1)); ?>
                <?php if (!empty($journey['driver_avatar'])): ?>
                    <div class="w-14 h-14 rounded-full overflow-hidden shrink-0">
                        <img src="<?= site_url(esc($journey['driver_avatar'])) ?>" alt="Avatar de <?= esc($journey['driver_firstname']) ?>" class="w-full h-full object-cover"
                            onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                    </div>
                    <div class="hidden w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                        <?= esc($initials) ?>
                    </div>
                <?php else: ?>
                    <div class="flex w-14 h-14 rounded-full bg-action-dark text-ink font-bold text-base shrink-0 items-center justify-center">
                        <?= esc($initials) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="text-ink font-semibold"><?= esc($journey['driver_firstname']) ?> <?= esc($journey['driver_lastname']) ?></p>
                    <p class="text-muted text-sm"><?= $journey['driver_is_student'] ? 'Étudiant' : 'Formateur' ?></p>
                </div>
            </div>

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

    <!-- Places disponibles -->
    <article class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-muted text-sm"><?= $remainingSeats > 1 ? 'Places disponibles' : 'Place disponible' ?></p>
                <p class="text-ink font-bold font-display text-2xl"><?= esc($remainingSeats) ?></p>
            </div>
        </div>
    </article>

    <!-- Actions -->
    <article id="actions" class="bg-surface-card rounded-2xl p-6 border border-action/10">
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="<?= site_url('journeys/preview/confirm') ?>" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit"
                    class="w-full bg-action hover:bg-action-dark text-ink font-bold font-display rounded-full py-3 transition-colors cursor-pointer">
                    <i class="fa-solid fa-check text-xs mr-1.5"></i>Publier le<?= $isRecurring && $recurringCount > 1 ? 's ' . $recurringCount . ' trajets' : ' trajet' ?>
                </button>
            </form>

            <form action="<?= site_url('journeys/preview/modify') ?>" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit"
                    class="w-full border border-action/30 hover:border-action/60 text-ink/70 hover:text-ink font-semibold font-display rounded-full py-3 transition-colors cursor-pointer">
                    <i class="fa-solid fa-pen text-xs mr-1.5"></i>Modifier
                </button>
            </form>
        </div>
    </article>

</div>

<?= view('partials/footer') ?>
