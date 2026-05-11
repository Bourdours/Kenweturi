<?= view('partials/head') ?>
<?= view('partials/header') ?>
<h1>Mot de passe oublié</h1>
<p>Entrez votre adresse e-mail, vous recevrez un lien pour réinitialiser votre mot de passe.</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
        <i class="fa-solid fa-triangle-exclamation text-action text-sm shrink-0"></i>
        <span class="text-action text-xs"><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border border-success/30 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
        <i class="fa-solid fa-circle-check text-success text-sm shrink-0"></i>
        <span class="text-success text-xs"><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif ?>

<form id="forgotForm" action="<?= base_url('forgotPassword') ?>" method="POST">
    <?= csrf_field() ?>

    <label for="email">Adresse e-mail</label>
    <input
        type="email"
        id="email"
        name="email"
        placeholder="exemple@email.com"
        required>

    <button type="submit">Envoyer</button>
</form>



<a href="<?= site_url('login') ?>">Retour à la connexion</a>

<?= view('partials/footer') ?>