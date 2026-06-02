<?= view('partials/head') ?>
<?= view('partials/header') ?>

<?php
$driverUserId  = (int) ($journey['driver_user_id'] ?? 0);
$currentUserId = (int) session()->get('user_id');
$isLoggedIn    = session()->get('isLoggedIn');
$isDriver      = $currentUserId === $driverUserId;
?>

<?php if ($isLoggedIn && !$isDriver): ?>
    <article>
        <h3>Signaler ce trajet</h3>

        <?php if (session()->getFlashdata('success')): ?>
            <p><?= session()->getFlashdata('success') ?></p>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <p><?= session()->getFlashdata('error') ?></p>
        <?php endif; ?>

        <?php if (session()->getFlashdata('validationErrors')): ?>
            <ul>
                <?php foreach (session()->getFlashdata('validationErrors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= site_url('journey/' . $journey['id'] . '/report') ?>" method="POST">
            <?= csrf_field() ?>
            <div>
                <label for="titleReport">Titre</label>
                <input type="text" name="titleReport" id="titleReport"
                    maxlength="127" required value="<?= old('titleReport') ?>"
                    placeholder="Ex : Comportement inapproprié">
            </div>
            <div>
                <label for="reasonReport">Raison du signalement</label>
                <textarea name="reasonReport" id="reasonReport"
                    rows="4" minlength="10" maxlength="500" required
                    placeholder="Décrivez le problème en détail (10 à 500 caractères)..."><?= old('reasonReport') ?></textarea>
            </div>
            <button type="submit">Envoyer le signalement</button>
        </form>
    </article>
<?php endif; ?>

<?= view('partials/footer') ?>