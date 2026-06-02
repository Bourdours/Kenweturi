<?php

/** @var string $tab            Onglet actif ('registrations' ou 'reports') */
/** @var array  $reports        Liste des signalements ouverts */
/** @var array  $closedReports  Liste des signalements clôturés */
/** @var int    $nOpenReports   Nombre de signalements ouverts */
/** @var array  $pendingUsers   Liste des utilisateurs en attente de validation */
/** @var int    $nPendingUsers  Nombre d'inscriptions en attente */
/** @var array  $allUsers       Liste de tous les utilisateurs (superadmin uniquement) */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>
<div class="max-w-5xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
    <div class="w-10 h-10 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
      <i class="fa-solid fa-shield-halved text-action text-base"></i>
    </div>
    <div>
      <h1 class="text-ink text-2xl font-bold font-display">Administration</h1>
      <p class="text-ink/40 text-sm">Modération et gestion de la plateforme</p>
    </div>
  </div>

  <!-- Message de succès  -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border border-success/20 rounded-xl px-5 py-3 text-success text-sm flex items-center gap-2">
      <i class="fa-solid fa-circle-check shrink-0"></i>
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>

  <!-- Message d'erreur -->
  <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-danger/10 border border-danger/20 rounded-xl px-5 py-3 text-danger text-sm flex items-center gap-2">
      <i class="fa-solid fa-circle-exclamation shrink-0"></i>
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <!-- Inscriptions -->
  <div class="flex flex-col sm:flex-row gap-0 border-b border-action/10 pb-0">
    <a href="<?= site_url('admin?tab=registrations') ?>"
      class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors
            <?= ($tab === 'registrations') ? 'bg-surface border border-b-surface border-action/10 text-ink sm:-mb-px' : 'text-ink/40 hover:text-ink' ?>">
      <i class="fa-solid fa-user-plus text-xs"></i>
      Inscriptions
      <?php if ($nPendingUsers > 0): ?>
        <span class="bg-action text-ink text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">
          <?= $nPendingUsers ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- Signalements -->
    <a href="<?= site_url('admin?tab=reports') ?>"
      class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors
            <?= ($tab === 'reports') ? 'bg-surface border border-b-surface border-action/10 text-ink sm:-mb-px' : 'text-ink/40 hover:text-ink' ?>">
      <i class="fa-solid fa-flag text-xs"></i>
      Signalements
      <?php if ($nOpenReports > 0): ?>
        <span class="bg-danger text-ink text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">
          <?= $nOpenReports ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- Gestion des admins (superadmin uniquement) -->
    <?php if (session()->get('role') === 'superadmin'): ?>
      <a href="<?= site_url('admin?tab=admins') ?>"
        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold font-display rounded-t-lg transition-colors
                  <?= ($tab === 'admins') ? 'bg-surface border border-b-surface border-action/10 text-ink sm:-mb-px' : 'text-ink/40 hover:text-ink' ?>">
        <i class="fa-solid fa-user-shield text-xs"></i>
        Admins
      </a>
    <?php endif; ?>

  </div>

  <!-- Contenu de l'onglet actif  -->
  <div class="bg-surface rounded-2xl border border-action/10 overflow-hidden">
    <?php if ($tab === 'registrations'): ?>
      <?= view('Admin/registrations_tab', ['pendingUsers' => $pendingUsers]) ?>
    <?php elseif ($tab === 'reports'): ?>
      <?= view('Admin/reports_tab', ['reports' => $reports]) ?>
    <?php elseif ($tab === 'admins' && session()->get('role') === 'superadmin'): ?>
      <?= view('Admin/admins_tab', ['allUsers' => $allUsers]) ?>
    <?php endif; ?>
  </div>
</div>
<?= view('partials/footer') ?>