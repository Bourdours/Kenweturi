<?php
/** @var bool   $tokenValid */
/** @var string $token */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>
<h1>Nouveau mot de passe</h1>

<?php if (session('error')): ?>
  <div><?= session('error') ?></div>
<?php endif; ?>

<?php if (session('success')): ?>
  <div><?= session('success') ?></div>
<?php endif; ?>

<?php if (isset($tokenValid) && $tokenValid): ?>

  <form action="<?= base_url('resetPassword') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">

    <label for="password">Nouveau mot de passe</label>
    <input
      type="password"
      id="password"
      name="password"
      placeholder="Minimum 8 caractères"
      minlength="8"
      required>

    <label for="confirmPassword">Confirmer le mot de passe</label>
    <input
      type="password"
      id="confirmPassword"
      name="confirmPassword"
      placeholder="Répétez le mot de passe"
      minlength="8"
      required>

    <button type="submit">Réinitialiser le mot de passe</button>
  </form>

<?php else: ?>

  <p>Ce lien est invalide ou a expiré.</p>
  <a href="<?= base_url('forgotPassword') ?>">Faire une nouvelle demande</a>

<?php endif; ?>

<?= view('partials/footer') ?>