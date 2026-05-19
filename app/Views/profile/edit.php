<?php
/** @var array $user */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-3xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

  <!-- En-tête -->
  <div class="flex items-center justify-between">
    <h1 class="text-ink text-2xl font-bold font-display">Modifier le profil</h1>
    <a href="<?= site_url('profile') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i>Retour
    </a>
  </div>

  <!-- Erreurs -->
  <?php if (session()->has('errors')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
      <div>
        <p class="text-ink text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
        <ul class="list-disc pl-4 flex flex-col gap-1">
          <?php foreach (session('errors') as $error): ?>
            <li class="text-action text-xs"><?= esc((string) $error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <form action="<?= site_url('profile/update') ?>" method="post" enctype="multipart/form-data" class="flex flex-col gap-6">
    <?= csrf_field() ?>

    <!-- Informations personnelles -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-user text-action text-sm"></i>Informations personnelles
      </h2>
      <div class="flex flex-col gap-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="firstNameProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Prénom</label>
            <input type="text" id="firstNameProfile" name="firstNameProfile" value="<?= esc($user['firstname']) ?>" required
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
          </div>
          <div>
            <label for="lastNameProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Nom</label>
            <input type="text" id="lastNameProfile" name="lastNameProfile" value="<?= esc($user['lastname']) ?>" required
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
          </div>
        </div>

        <div>
          <label for="emailProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Email</label>
          <input type="email" id="emailProfile" name="emailProfile" value="<?= esc($user['email']) ?>" required
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="genderProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Genre</label>
            <select id="genderProfile" name="genderProfile" required
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors cursor-pointer">
              <option value="Homme" <?= $user['gender'] === 'Homme' ? 'selected' : '' ?>>Homme</option>
              <option value="Femme" <?= $user['gender'] === 'Femme' ? 'selected' : '' ?>>Femme</option>
              <option value="Autre" <?= $user['gender'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>
          </div>
          <div>
            <label for="birthDateProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Date de naissance</label>
            <input type="date" id="birthDateProfile" name="birthDateProfile" value="<?= esc($user['birth_date']) ?>" required
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
          </div>
        </div>

      </div>
    </div>

    <!-- Biographie -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-align-left text-action text-sm"></i>Biographie
      </h2>
      <label for="biographyProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">À propos de vous</label>
      <textarea id="biographyProfile" name="biographyProfile" rows="4"
        placeholder="Décrivez-vous en quelques mots..."
        class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 resize-none transition-colors"><?= esc($user['biography'] ?? '') ?></textarea>
    </div>

    <!-- Photo de profil -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-image text-action text-sm"></i>Photo de profil
      </h2>
      <?php if (!empty($user['avatar'])): ?>
        <div class="flex items-center gap-4 mb-4">
          <div style="width:3.5rem;height:3.5rem;border-radius:9999px;overflow:hidden;flex-shrink:0;">
            <img src="<?= esc($user['avatar']) ?>" alt="Avatar actuel" style="width:100%;height:100%;object-fit:cover;">
          </div>
          <p class="text-ink/50 text-xs">Photo actuelle — choisissez un nouveau fichier pour la remplacer.</p>
        </div>
      <?php endif; ?>
      <label for="avatarProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Nouvelle photo</label>
      <input type="file" id="avatarProfile" name="avatarProfile" accept="image/*"
        class="w-full text-ink/70 text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-action/10 file:text-action hover:file:bg-action/20 file:cursor-pointer file:transition-colors">
    </div>

    <!-- Mot de passe -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-1 flex items-center gap-2">
        <i class="fa-solid fa-lock text-action text-sm"></i>Mot de passe
      </h2>
      <p class="text-ink/40 text-xs mb-5">Laissez vide pour conserver votre mot de passe actuel.</p>
      <div class="flex flex-col gap-4">

        <div>
          <label for="currentPasswordProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Mot de passe actuel</label>
          <div class="relative">
            <input type="password" id="currentPasswordProfile" name="currentPasswordProfile"
              placeholder="Requis pour changer le mot de passe"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
            <button type="button" onclick="togglePassword('currentPasswordProfile', this)"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
              <i class="fa-regular fa-eye text-sm"></i>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="newPasswordProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Nouveau mot de passe</label>
            <div class="relative">
              <input type="password" id="newPasswordProfile" name="newPasswordProfile"
                placeholder="••••••••"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
              <button type="button" onclick="togglePassword('newPasswordProfile', this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
                <i class="fa-regular fa-eye text-sm"></i>
              </button>
            </div>
          </div>
          <div>
            <label for="confirmPasswordProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Confirmer le mot de passe</label>
            <div class="relative">
              <input type="password" id="confirmPasswordProfile" name="confirmPasswordProfile"
                placeholder="••••••••"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
              <button type="button" onclick="togglePassword('confirmPasswordProfile', this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
                <i class="fa-regular fa-eye text-sm"></i>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Boutons -->
    <div class="flex items-center justify-end gap-3">
      <a href="<?= site_url('profile') ?>"
        class="flex items-center gap-2 border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
        Annuler
      </a>
      <button type="submit"
        class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
        <i class="fa-solid fa-check text-xs"></i>Enregistrer
      </button>
    </div>

  </form>
</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>

<?= view('partials/footer') ?>
