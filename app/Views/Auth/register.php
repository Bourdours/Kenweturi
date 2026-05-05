<?= view('partials/head') ?>
<?= view('partials/header') ?>


    <h2>Inscription</h2>

    <form action="<?= base_url('/handleRegister') ?>" method="post">
        <?= csrf_field() ?>

        <div>
            <label>Prénom :</label>
            <input type="text" name="firstname" value="<?= old('firstname') ?>" required>
        </div>

        <div>
            <label>Nom :</label>
            <input type="text" name="lastname" value="<?= old('lastname') ?>" required>
        </div>

        <div>
            <label>Email :</label>
            <input type="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div>
            <label>Genre :</label>
            <select name="gender" required>
                <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                <option value="Femme" <?= old('gender') == 'Femme' ? 'selected' : '' ?>>Femme</option>
                <option value="Autre" <?= old('gender') == 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>
        </div>

        <div>
            <label>Date de naissance :</label>
            <input type="date" name="birth_date" max="<?= date('Y-m-d'); ?>" value="<?= old('birth_date') ?>">
        </div>

        <!-- Ville -->
        <div>
            <label>Ville :</label>
            <input type="text" name="cityName" value="<?= old('cityName') ?>" required>
        </div>

        <!-- Code Postal -->
        <div>
            <label>Code postal :</label>
            <input type="text" name="zipCode" value="<?= old('zipCode') ?>" required>
        </div>

        <div>
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirmer le mot de passe :</label>
            <input type="password" name="pass_confirm" required>
        </div>
        
        <!-- Vérification des erreurs stockées en session -->
        <?php if (session()->has('errors')): ?>
            <div >
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
        <button type="submit">Créer mon compte</button>
    </form>

<?= view('partials/footer') ?>