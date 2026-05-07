<?= view('partials/head') ?>
<?= view('partials/header') ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="bg-success/10 border-b border-success/20 px-6 py-3 text-success text-sm text-center">
    <?= session()->getFlashdata('success') ?>
  </div>
<?php endif; ?>

<div>
    <h1>Accueil</h1>
</div>

<?= view('partials/footer') ?>