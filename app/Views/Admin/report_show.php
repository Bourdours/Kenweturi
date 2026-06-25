<?php

/** @var array $report */
$date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Europe/Paris'));
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-3xl mx-auto py-10 px-4 flex flex-col gap-6">

  <div>
    <a href="<?= site_url('admin?tab=reports') ?>"
      class="inline-flex items-center gap-1.5 text-ink/40 hover:text-ink text-xs font-medium transition-colors mb-4">
      <i class="fa-solid fa-arrow-left text-xs"></i>Retour aux signalements
    </a>
    <div class="flex items-start justify-between gap-4">
      <h1 class="text-ink text-2xl font-bold font-display"><?= esc($report['title']) ?></h1>
      <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full
        <?= $report['status'] === 'open' ? 'bg-danger/10 text-danger' : 'bg-ink/10 text-ink/50' ?>">
        <?= $report['status'] === 'open' ? 'En attente' : 'Clôturé' ?>
      </span>
    </div>
    <p class="text-ink/40 text-xs mt-1">Signalé le <?= $date->format('d/m/Y à H:i') ?></p>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
      <p class="text-ink text-sm"><?= esc(session()->getFlashdata('error')) ?></p>
    </div>
  <?php endif; ?>

  <!-- Parties concernées -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-1">
      <p class="text-ink/40 text-xs font-medium uppercase tracking-wide mb-1">Signalé par</p>
      <p class="text-ink font-semibold text-sm"><?= esc($report['reporter_firstname']) ?> <?= esc($report['reporter_lastname']) ?></p>
      <p class="text-ink/50 text-xs"><?= esc($report['reporter_email']) ?></p>
    </div>

    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-1">
      <p class="text-ink/40 text-xs font-medium uppercase tracking-wide mb-1">Utilisateur signalé</p>
      <p class="text-ink font-semibold text-sm"><?= esc($report['reported_firstname']) ?> <?= esc($report['reported_lastname']) ?></p>
      <p class="text-ink/50 text-xs"><?= esc($report['reported_email']) ?></p>
    </div>

  </div>

  <!-- Trajet concerné -->
  <?php if (!empty($report['journey_date'])): ?>
    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-route text-action text-sm shrink-0"></i>
        <div>
          <p class="text-ink/40 text-xs font-medium uppercase tracking-wide">Trajet concerné</p>
          <p class="text-ink text-sm font-medium mt-0.5">
            <?= (new DateTime($report['journey_date']))->format('d/m/Y à H:i') ?>
          </p>
        </div>
      </div>
      <a href="<?= site_url('journeys/' . $report['journey_id']) ?>?back=<?= urlencode(current_url()) ?>"
        class="text-action hover:text-action-dark text-xs font-medium transition-colors shrink-0">
        Voir le trajet <i class="fa-solid fa-arrow-right text-xs ml-0.5"></i>
      </a>
    </div>
  <?php endif; ?>

  <!-- Description -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10">
    <h2 class="text-ink text-base font-semibold font-display mb-3 flex items-center gap-2">
      <i class="fa-solid fa-align-left text-action text-sm"></i>Description
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed whitespace-pre-line"><?= esc($report['description']) ?></p>
  </div>

  <!-- Actions (uniquement si ouvert) -->
  <?php if ($report['status'] === 'open'): ?>
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-4 flex items-center gap-2">
        <i class="fa-solid fa-gavel text-action text-sm"></i>Action administrative
      </h2>
      <form action="<?= site_url('admin/reports/' . $report['id'] . '/resolve') ?>" method="POST"
        class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div>
          <label for="commentAdmin" class="text-ink/50 text-xs font-medium mb-1.5 block">
            Commentaire <span class="text-ink/30 font-normal">(obligatoire)</span>
          </label>
          <textarea id="commentAdmin" name="commentAdmin" rows="3"
            placeholder="Expliquez votre décision..."
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 resize-none transition-colors"></textarea>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="submit" name="actionAdmin" value="warn"
            class="flex items-center gap-1.5 bg-action/10 hover:bg-action text-action hover:text-ink font-medium text-sm rounded-lg px-4 py-2.5 transition-colors">
            <i class="fa-solid fa-triangle-exclamation text-xs"></i>Avertir
          </button>
          <button type="submit" name="actionAdmin" value="ban"
            class="flex items-center gap-1.5 bg-danger/10 hover:bg-danger text-danger hover:text-ink font-medium text-sm rounded-lg px-4 py-2.5 transition-colors">
            <i class="fa-solid fa-ban text-xs"></i>Bannir
          </button>
          <button type="submit" name="actionAdmin" value="close"
            class="flex items-center gap-1.5 bg-ink/5 hover:bg-ink/10 text-ink/50 hover:text-ink font-medium text-sm rounded-lg px-4 py-2.5 transition-colors">
            <i class="fa-solid fa-xmark text-xs"></i>Clôturer sans action
          </button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex items-center gap-3">
      <i class="fa-solid fa-circle-check text-success text-base shrink-0"></i>
      <div>
        <p class="text-ink text-sm font-medium">Signalement traité</p>
        <?php if (!empty($report['admin_comment'])): ?>
          <p class="text-ink/50 text-xs mt-0.5"><?= esc($report['admin_comment']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?= view('partials/footer') ?>