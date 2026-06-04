<?php
/** @var array  $journey */
/** @var array  $reportableUsers */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<?php
$driverUserId  = (int) ($journey['driver_user_id'] ?? 0);
$currentUserId = (int) session()->get('user_id');
$isLoggedIn    = session()->get('isLoggedIn');
$isDriver      = $currentUserId === $driverUserId;
$oldReported   = (int) old('reportedUserId');
?>

<div class="max-w-2xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

  <div>
    <a href="<?= site_url('journeys/' . $journey['id']) ?>"
      class="inline-flex items-center gap-1.5 text-ink/40 hover:text-ink text-xs font-medium transition-colors mb-4">
      <i class="fa-solid fa-arrow-left text-xs"></i>Retour au trajet
    </a>
    <h1 class="text-ink text-2xl font-bold font-display">Signaler un trajet</h1>
    <p class="text-ink/50 text-sm mt-1">Votre signalement sera examiné par notre équipe sous 48h.</p>
  </div>

  <?php if ($isLoggedIn && !$isDriver): ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
        <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
        <p class="text-ink text-sm"><?= esc(session()->getFlashdata('error')) ?></p>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('validationErrors')): ?>
      <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
        <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
        <div>
          <p class="text-ink text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
          <ul class="list-disc pl-4 flex flex-col gap-1">
            <?php foreach (session()->getFlashdata('validationErrors') as $err): ?>
              <li class="text-action text-xs"><?= esc((string) $err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="bg-brand/10 border border-brand/30 rounded-xl p-4 flex gap-3 items-start">
        <i class="fa-solid fa-circle-check text-brand text-base shrink-0 mt-0.5"></i>
        <p class="text-ink text-sm"><?= esc(session()->getFlashdata('success')) ?></p>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('journeys/' . $journey['id'] . '/report') ?>" method="POST"
      novalidate class="flex flex-col gap-6">
      <?= csrf_field() ?>

      <!-- Utilisateur à signaler -->
      <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <h2 class="text-ink text-base font-semibold font-display mb-4 flex items-center gap-2">
          <i class="fa-solid fa-user-xmark text-action text-sm"></i>Utilisateur à signaler
        </h2>
        <?php if (empty($reportableUsers)): ?>
          <p class="text-ink/40 text-sm">Aucun autre participant à signaler sur ce trajet.</p>
        <?php else: ?>
          <div class="flex flex-col gap-2">
            <?php foreach ($reportableUsers as $u): ?>
              <label class="group flex items-center gap-3 bg-paper border border-action/15 rounded-lg px-4 py-3 cursor-pointer
                            has-[:checked]:border-action/50 has-[:checked]:bg-action/5 transition-colors">
                <input type="radio" name="reportedUserId" value="<?= esc($u['id']) ?>"
                  class="hidden"
                  <?= $oldReported === (int) $u['id'] ? 'checked' : '' ?>>
                <span class="w-4 h-4 rounded-full border-2 border-action/30 flex items-center justify-center shrink-0
                              group-has-[:checked]:border-action transition-colors">
                  <span class="w-2 h-2 rounded-full bg-action opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></span>
                </span>
                <div class="flex-1 min-w-0">
                  <span class="text-ink text-sm font-medium"><?= esc($u['firstname']) ?> <?= esc($u['lastname']) ?></span>
                  <span class="ml-2 text-ink/40 text-xs"><?= esc($u['role']) ?></span>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Objet du signalement -->
      <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
          <i class="fa-solid fa-flag text-action text-sm"></i>Objet du signalement
        </h2>
        <div>
          <label for="titleReport" class="text-ink/50 text-xs font-medium mb-1.5 block">Titre</label>
          <input id="titleReport" name="titleReport" type="text"
            maxlength="127" required
            placeholder="Ex : Comportement inapproprié"
            value="<?= esc(old('titleReport')) ?>"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
        </div>
      </div>

      <!-- Description -->
      <div class="bg-surface rounded-2xl p-6 border border-action/10">
        <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
          <i class="fa-solid fa-align-left text-action text-sm"></i>Description
        </h2>
        <div>
          <label for="reasonReport" class="text-ink/50 text-xs font-medium mb-1.5 block">
            Décrivez le problème rencontré
            <span class="text-ink/30 font-normal">(10 à 500 caractères)</span>
          </label>
          <textarea id="reasonReport" name="reasonReport" rows="5"
            minlength="10" maxlength="500" required
            placeholder="Décrivez le problème en détail..."
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 resize-none transition-colors"><?= esc(old('reasonReport')) ?></textarea>
        </div>
      </div>

      <!-- Boutons -->
      <div class="flex items-center justify-end gap-3">
        <a href="<?= site_url('journeys/' . $journey['id']) ?>"
          class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
          Annuler
        </a>
        <button type="submit"
          class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
          <i class="fa-solid fa-flag text-xs"></i>Envoyer le signalement
        </button>
      </div>

    </form>

  <?php else: ?>

    <div class="bg-surface rounded-2xl p-8 border border-action/10 text-center flex flex-col items-center gap-3">
      <i class="fa-solid fa-lock text-ink/20 text-3xl"></i>
      <p class="text-ink/50 text-sm">
        <?= $isLoggedIn
          ? 'Vous ne pouvez pas signaler votre propre trajet.'
          : 'Connectez-vous pour signaler un trajet.' ?>
      </p>
      <?php if (!$isLoggedIn): ?>
        <a href="<?= site_url('login') ?>"
          class="mt-1 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors">
          Se connecter
        </a>
      <?php endif; ?>
    </div>

  <?php endif; ?>

</div>

<?= view('partials/footer') ?>
