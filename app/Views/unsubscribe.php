<?php

/** @var bool   $success */
/** @var string $label */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="min-h-[70vh] flex items-center justify-center px-4 py-16">
  <div class="bg-surface rounded-2xl border border-action/10 w-full max-w-sm p-8 flex flex-col items-center text-center gap-6">

    <?php if ($success): ?>

      <div class="w-16 h-16 rounded-2xl bg-success/10 border border-success/20 flex items-center justify-center">
        <i class="fa-solid fa-bell-slash text-success text-2xl"></i>
      </div>

      <div class="flex flex-col gap-2">
        <h1 class="text-ink text-xl font-bold font-display">Désabonnement effectué</h1>
        <p class="text-ink/50 text-sm leading-relaxed">
          Vous ne recevrez plus les emails<br>
          <span class="text-ink/80 font-medium">« <?= esc($label) ?> »</span>
        </p>
      </div>

      <div class="w-full border-t border-action/10 pt-5 flex flex-col gap-3">
        <a href="<?= site_url('profile/notifications') ?>"
          class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors text-center">
          Gérer toutes mes préférences
        </a>
        <a href="<?= site_url('/') ?>"
          class="text-ink/40 hover:text-ink/70 text-xs transition-colors">
          Retour à l'accueil
        </a>
      </div>

    <?php else: ?>

      <div class="w-16 h-16 rounded-2xl bg-action/10 border border-action/20 flex items-center justify-center">
        <i class="fa-solid fa-link-slash text-action text-2xl"></i>
      </div>

      <div class="flex flex-col gap-2">
        <h1 class="text-ink text-xl font-bold font-display">Lien invalide</h1>
        <p class="text-ink/50 text-sm leading-relaxed">
          Ce lien de désabonnement est invalide ou a expiré.<br>
          Connectez-vous pour gérer vos préférences manuellement.
        </p>
      </div>

      <div class="w-full border-t border-action/10 pt-5 flex flex-col gap-3">
        <a href="<?= site_url('profile/notifications') ?>"
          class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors text-center">
          Gérer mes préférences
        </a>
        <a href="<?= site_url('/') ?>"
          class="text-ink/40 hover:text-ink/70 text-xs transition-colors">
          Retour à l'accueil
        </a>
      </div>

    <?php endif; ?>

  </div>
</div>

<?= view('partials/footer') ?>