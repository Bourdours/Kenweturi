<?= view('partials/head') ?>
<?= view('partials/header') ?>
<h1>Mot de passe oublié</h1>
<p>Entrez votre adresse e-mail, vous recevrez un lien pour réinitialiser votre mot de passe.</p>

<form id="forgotForm" action="#" method="POST">
    <?= csrf_field() ?>

    <label for="email">Adresse e-mail</label>
    <input
        type="email"
        id="email"
        name="email"
        placeholder="exemple@email.com"
        value="<?= old('email') ?>"
        required>

    <button type="submit">Envoyer</button>
</form>

<a href="<?= site_url('login') ?>">Retour à la connexion</a>

<?= view('partials/footer') ?>