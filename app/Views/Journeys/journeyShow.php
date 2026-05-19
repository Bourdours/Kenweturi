<?= view('partials/head') ?>
<?= view('partials/header') ?>

<h1 style="color: white;">Détails du trajet</h1>

<article style="color: white;">
    <div>
        <p><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
    </div>
    <div>
        <h3><?= esc($journey['city_start_name']) ?>Carte</h3>
        <p><?= esc($journey['address_start']) ?></p>
    </div>
    <div>
        <h3><?= esc($journey['city_end_name']) ?>Carte</h3>
        <p><?= esc($journey['address_end']) ?></p>
    </div>
</article>
<article style="color: white;">
    <div>
        <?php $initials = strtoupper(substr($journey['driver_firstname'], 0, 1) . substr($journey['driver_lastname'], 0, 1)); ?>
        <?php if (!empty($journey['driver_avatar'])): ?>
            <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
                <img src="/<?= esc($journey['driver_avatar']) ?>" alt="Avatar de <?= esc($journey['driver_firstname']) ?>" class="w-full h-full object-cover"
                onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
            </div>
            <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action-dark text-paper font-bold text-base shrink-0 items-center justify-center">
                <?= $initials ?>
            </div>
        <?php else: ?>
            <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action-dark text-paper font-bold text-base shrink-0 items-center justify-center">
                <?= $initials ?>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <h3><?= esc($journey['driver_firstname']) ?><?= esc($journey['driver_lastname']) ?></h3>
        <p><?= $journey['driver_is_student'] ? 'Étudiant' : 'Formateur' ?></p>
        <p><?= esc($journey['car_brand']) ?> <?= esc($journey['car_model']) ?> - <?= esc($journey['car_color']) ?></p>
        <p><?= $journey['smoking'] ? 'Fuleur' : 'Non-fumeur' ?></p>
    </div>
</article>
<article style="color: white;">
    <?php $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d MMMM'); ?>
    <h3><?= $fmt->format(strtotime($journey['start_datetime'])) ?></h3>
    <div>
        <p><?= esc(date('H:i', strtotime($journey['start_datetime']))) ?></p>
    </div>
    <div>
        <h3><?= esc($journey['city_start_name']) ?>Carte</h3>
        <p><?= esc($journey['address_start']) ?></p>
    </div>
    <div>
        <h3><?= esc($journey['city_end_name']) ?>Carte</h3>
        <p><?= esc($journey['address_end']) ?></p>
    </div>
    <p><?= esc($journey['seats']) ?> siège restant</p>
    <button>Réserver</button>
</article>
<?= view('partials/footer') ?>