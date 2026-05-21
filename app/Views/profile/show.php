<?php

/** @var array $user */
/** @var string $city */
/** @var string $memberSince */
/** @var bool $isOwnProfile */
?>
<?= view('partials/head', ['extraJs' => [base_url('js/auth.js'), base_url('js/user.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

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
        <div class="jsAvatarOpen ring-2 ring-action/20 flex w-20 h-20 rounded-full bg-action text-paper font-bold text-2xl shrink-0 items-center justify-center">
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

  <!-- Véhicules -->
  <?php if (!empty($cars)): ?>
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-4 flex items-center gap-2">
        <i class="fa-solid fa-car text-action text-sm"></i>Véhicules
      </h2>
      <div class="flex flex-col divide-y divide-action/10">
        <?php foreach ($cars as $car): ?>
          <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
            <div class="w-8 h-8 rounded-lg bg-action/10 flex items-center justify-center shrink-0">
              <i class="fa-solid fa-car-side text-action text-xs"></i>
            </div>
            <div>
              <p class="text-ink text-sm font-medium"><?= esc($car['brand']) ?> <?= esc($car['model']) ?></p>
              <p class="text-ink/40 text-xs"><?= esc($car['color']) ?> · <?= esc($car['seats']) ?> places</p>
            </div>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Zone danger -->
  <?php if ($isOwnProfile): ?>
    <form action="<?= site_url('profile/delete') ?>" method="post" class="deleteAccount" id="formDeleteAccount">
      <?= csrf_field() ?>
      <button type="button" onclick="document.getElementById('modalSupprimer').style.display='flex'"
        class="flex items-center gap-2 bg-danger/10 hover:bg-danger text-danger hover:text-white border border-danger/30 hover:border-danger font-semibold rounded-lg px-4 py-2 text-sm transition-colors cursor-pointer">
        <i class="fa-solid fa-trash text-xs"></i>Supprimer mon compte
      </button>

      <!-- Modal -->
      <div id="modalSupprimer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
        <div class="bg-surface rounded-2xl p-6" style="max-width:400px; width:90%;">
          <h2 class="text-danger font-semibold font-display mb-2">Supprimer mon compte</h2>
          <p class="text-ink text-sm mb-6">La suppression de votre compte est irréversible. Êtes-vous sûr ?</p>
          <div class="mb-5">
            <label for="deleteAccountPassword" class="text-ink/50 text-xs font-medium mb-1.5 block">
              Confirmez votre mot de passe
            </label>
            <div class="relative">
              <input type="password" name="deleteAccountPassword" id="deleteAccountPassword" required
                placeholder="••••••••"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30" />
              <button type="button" onclick="togglePassword('deleteAccountPassword', this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
                <i class="fa-regular fa-eye text-xs"></i>
              </button>
            </div>
            <p id="deletePasswordError" class="text-danger text-xs mt-1.5 hidden">Veuillez saisir votre mot de passe.</p>
          </div>
          <div class="flex gap-3">
            <button type="button" onclick="document.getElementById('modalSupprimer').style.display='none'"
              class="flex-1 border border-ink/20 text-ink rounded-lg px-4 py-2 text-sm font-semibold">
              Annuler
            </button>
            <button type="button" onclick="document.getElementById('formDeleteAccount').submit()"
              class="flex-1 bg-danger text-white rounded-lg px-4 py-2 text-sm font-semibold">
              Confirmer la suppression
            </button>
          </div>
        </div>
      </div>
    </form>

  <?php endif; ?>

</div>

<?= view('partials/avatar_modal', ['avatarSrc' => base_url($user['avatar'] ?? ''), 'firstname' => $user['firstname'], 'initials' => $initials]) ?>


<?= view('partials/footer') ?>