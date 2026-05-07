<?= view('partials/head') ?>
<?= view('partials/header') ?>


    <h2>Inscription</h2>

    <form action="<?= base_url('/handleRegister') ?>" method="post">
        <?= csrf_field() ?>

        <div>
            <label for="firstName">Prénom :</label>
            <input type="text" name="firstName" id="firstName" value="<?= old('firstName') ?>" required>
        </div>

        <div>
            <label for="lastName">Nom :</label>
            <input type="text" name="lastName" id="lastName" value="<?= old('lastName') ?>" required>
        </div>

        <div>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= old('email') ?>" required>
        </div>

        <div>
            <label for="gender">Genre :</label>
            <select name="gender" id="gender" required>
                <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                <option value="Femme" <?= old('gender') == 'Femme' ? 'selected' : '' ?>>Femme</option>
                <option value="Autre" <?= old('gender') == 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>
        </div>

        <div>
            <label for="birthDate">Date de naissance :</label>
            <input type="date" name="birthDate" id="birthDate" max="<?= date('Y-m-d'); ?>" value="<?= old('birthDate') ?>">
        </div>

        <!-- Ville -->
        <div>
            <label for="cityName">Ville :</label>
            <input type="text" name="cityName" id="cityName" value="<?= old('cityName') ?>" required>
        </div>

        <!-- Code Postal -->
        <div>
            <label for="postalCode">Code postal :</label>
            <input type="text" name="postalCode" id="postalCode" value="<?= old('postalCode') ?>" required>
        </div>

        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div>
            <label for="passConfirm">Confirmer le mot de passe :</label>
            <input type="password" name="passConfirm" id="passConfirm" required>
        </div>
        
<!-- Vérification des erreurs stockées en session -->
        <?php if (session()->has('errors')): ?>
            <?php $tw = new \App\Libraries\TailwindExample(); ?>
            <?php foreach (session('errors') as $error): ?>
                <?= $tw->alert('error', $error) ?>
            <?php endforeach ?>
        <?php endif; ?>
        <button type="submit">Créer mon compte</button>
    </form>

<?= view('partials/footer') ?>