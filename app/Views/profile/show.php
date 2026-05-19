<?php
/** @var array $user */
/** @var string $city */
/** @var string $memberSince */
/** @var bool $isOwnProfile */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-3xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border border-success/20 rounded-xl px-5 py-3 text-success text-sm flex items-center gap-2">
      <i class="fa-solid fa-circle-check shrink-0"></i>
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>

  <!-- Carte identité -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col sm:flex-row items-center sm:items-start gap-5">

    <!-- Avatar -->
    <div class="shrink-0">
      <?php $initials = strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
      <?php if (!empty($user['avatar'])): ?>
        <div class="jsAvatarOpen ring-2 ring-action/20 hover:ring-action/50 transition-all cursor-pointer w-20 h-20 rounded-full overflow-hidden shrink-0">
          <img src="<?= esc($user['avatar']) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" class="w-full h-full object-cover"
            onerror="this.parentElement.classList.add('hidden'); this.parentElement.nextElementSibling.classList.remove('hidden'); this.parentElement.nextElementSibling.classList.add('flex');">
        </div>
        <div class="jsAvatarOpen ring-2 ring-action/20 hover:ring-action/50 transition-all cursor-pointer hidden w-20 h-20 rounded-full bg-action-dark text-paper font-bold text-2xl shrink-0 items-center justify-center">
          <?= $initials ?>
        </div>
      <?php else: ?>
        <div class="jsAvatarOpen ring-2 ring-action/20 flex w-20 h-20 rounded-full bg-action-dark text-paper font-bold text-2xl shrink-0 items-center justify-center">
          <?= $initials ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Nom + badge + meta + actions -->
    <div class="flex-1 text-center sm:text-left">
      <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
        <h1 class="text-ink text-2xl font-bold font-display"><?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?></h1>
        <?php if ($user['is_student']): ?>
          <span class="bg-action/10 text-action text-xs font-semibold px-2.5 py-0.5 rounded-full">Étudiant</span>
        <?php endif; ?>
      </div>

      <p class="text-ink/50 text-sm flex flex-wrap items-center justify-center sm:justify-start gap-x-2 gap-y-1">
        <?php if (!empty($city)): ?>
          <span><i class="fa-solid fa-location-dot mr-1"></i><?= esc($city) ?></span>
          <span class="text-ink/20">·</span>
        <?php endif; ?>
        <span><i class="fa-regular fa-calendar mr-1"></i>Membre depuis <?= esc($memberSince) ?></span>
      </p>

      <?php if ($isOwnProfile): ?>
        <div class="mt-4 flex flex-wrap justify-center sm:justify-start gap-3">
          <a href="<?= site_url('profile/update') ?>"
            class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-4 py-2 text-sm transition-colors">
            <i class="fa-solid fa-pen text-xs"></i>Modifier le profil
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Bio -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10">
    <h2 class="text-ink text-base font-semibold font-display mb-3 flex items-center gap-2">
      <i class="fa-solid fa-align-left text-action text-sm"></i>À propos
    </h2>
    <?php if (!empty($user['biography'])): ?>
      <p class="text-ink/70 text-sm leading-relaxed"><?= esc($user['biography']) ?></p>
    <?php else: ?>
      <p class="text-ink/30 text-sm italic">
        <?= $isOwnProfile ? 'Vous n\'avez pas encore ajouté de bio.' : 'Aucune bio renseignée.' ?>
      </p>
    <?php endif; ?>
  </div>

  <!-- Infos personnelles -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10">
    <h2 class="text-ink text-base font-semibold font-display mb-4 flex items-center gap-2">
      <i class="fa-solid fa-circle-info text-action text-sm"></i>Informations
    </h2>
    <dl class="flex flex-col divide-y divide-action/10">

      <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
        <div class="w-8 h-8 rounded-lg bg-action/10 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-envelope text-action text-xs"></i>
        </div>
        <div>
          <dt class="text-ink/40 text-xs mb-0.5">Email</dt>
          <dd class="text-ink text-sm font-medium"><?= esc($user['email']) ?></dd>
        </div>
      </div>

      <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
        <div class="w-8 h-8 rounded-lg bg-action/10 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-venus-mars text-action text-xs"></i>
        </div>
        <div>
          <dt class="text-ink/40 text-xs mb-0.5">Genre</dt>
          <dd class="text-ink text-sm font-medium"><?= esc($user['gender']) ?></dd>
        </div>
      </div>

      <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
        <div class="w-8 h-8 rounded-lg bg-action/10 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-cake-candles text-action text-xs"></i>
        </div>
        <div>
          <dt class="text-ink/40 text-xs mb-0.5">Âge</dt>
          <dd class="text-ink text-sm font-medium">
            <?php
            $birth = new DateTime($user['birth_date']);
            $age   = (new DateTime())->diff($birth)->y;
            echo $age . ' ans';
            ?>
          </dd>
        </div>
      </div>

      <?php if (!empty($city)): ?>
        <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
          <div class="w-8 h-8 rounded-lg bg-action/10 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-location-dot text-action text-xs"></i>
          </div>
          <div>
            <dt class="text-ink/40 text-xs mb-0.5">Ville</dt>
            <dd class="text-ink text-sm font-medium"><?= esc($city) ?></dd>
          </div>
        </div>
      <?php endif; ?>

    </dl>
  </div>

  <!-- Zone danger -->
  <?php if ($isOwnProfile): ?>
    <div class="bg-surface rounded-2xl p-6 border border-danger/20">
      <h2 class="text-danger text-base font-semibold font-display mb-1 flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-sm"></i>Zone de danger
      </h2>
      <p class="text-ink/50 text-sm mb-4">La suppression de votre compte est irréversible.</p>
      <form action="<?= site_url('profile/delete') ?>" method="post" class="deleteAccount">
        <?= csrf_field() ?>
        <button type="submit"
          class="flex items-center gap-2 bg-danger/10 hover:bg-danger text-danger hover:text-white border border-danger/30 hover:border-danger font-semibold rounded-lg px-4 py-2 text-sm transition-colors cursor-pointer">
          <i class="fa-solid fa-trash text-xs"></i>Supprimer mon compte
        </button>
      </form>
    </div>
  <?php endif; ?>

</div>

<!-- Modal avatar -->
<div id="avatarModal" class="hidden fixed inset-0 bg-black/80 z-[9999] items-center justify-center cursor-zoom-out">
  <?php if (!empty($user['avatar'])): ?>
    <img src="<?= esc($user['avatar']) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" class="max-w-[90vw] max-h-[90vh] rounded-lg object-contain shadow-[0_0_40px_rgba(0,0,0,0.5)]"
      onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
    <div class="hidden w-64 h-64 rounded-full bg-action-dark text-paper font-bold text-[5rem] items-center justify-center shadow-[0_0_40px_rgba(0,0,0,0.5)]">
      <?= $initials ?>
    </div>
  <?php else: ?>
    <div class="flex w-64 h-64 rounded-full bg-action-dark text-paper font-bold text-[5rem] items-center justify-center shadow-[0_0_40px_rgba(0,0,0,0.5)]">
      <?= $initials ?>
    </div>
  <?php endif; ?>
</div>

<script src="<?= base_url('js/user.js') ?>"></script>

<?= view('partials/footer') ?>
