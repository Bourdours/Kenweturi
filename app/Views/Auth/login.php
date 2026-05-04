<?= view('partials/head') ?>
<?= view('partials/header') ?>

<main class="container">
    <h2>Connexion</h2>

    <form action="#" method="post">
        <?= csrf_field() ?>

        <div>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= old('email') ?>" required>
        </div>

        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-actions">
            <button type="submit">Se connecter</button>
        </div>

        <p>
            Pas encore de compte ? <a href="<?= site_url('inscription') ?>">Créer un compte</a>.
        </p>
    </form>
</main>

<?= view('partials/footer') ?>