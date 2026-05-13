<?= view('partials/head') ?>
<?= view('partials/header') ?>

<!-- Message d'erreur -->
<div>
  <?php if (session()->has('errors')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 mb-6 flex gap-3 items-start">
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


  <form action="<?= site_url('profile/update') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Identité -->
    <div>
      <label for="firstNameProfile">Prénom</label>
      <input type="text" id="firstNameProfile" name="firstNameProfile" value="<?= esc($user['firstname']) ?>" required>
    </div>

    <div>
      <label for="lastNameProfile">Nom</label>
      <input type="text" id="lastNameProfile" name="lastNameProfile" value="<?= esc($user['lastname']) ?>" required>
    </div>

    <!-- Email -->
    <div>
      <label for="emailProfile">Email</label>
      <input type="email" id="emailProfile" name="emailProfile" value="<?= esc($user['email']) ?>" required>
    </div>

    <!-- Genre -->
    <div>
      <label for="genderProfile">Genre</label>
      <select id="genderProfile" name="genderProfile" required>
        <option value="Homme" <?= $user['gender'] === 'Homme'  ? 'selected' : '' ?>>Homme</option>
        <option value="Femme" <?= $user['gender'] === 'Femme'  ? 'selected' : '' ?>>Femme</option>
        <option value="Autre" <?= $user['gender'] === 'Autre'  ? 'selected' : '' ?>>Autre</option>
      </select>
    </div>

    <!-- Date de naissance -->
    <div>
      <label for="birthDateProfile">Date de naissance</label>
      <input type="date" id="birthDateProfile" name="birthDateProfile" value="<?= esc($user['birth_date']) ?>" required>
    </div>

    <!-- Bio -->
    <div>
      <label for="biographyProfile">Biographie</label>
      <textarea id="biographyProfile" name="biographyProfile"><?= esc($user['biography'] ?? '') ?></textarea>
    </div>

    <!-- Changement de mot de passe -->
    <div>
      <label for="currentPasswordProfile">Mot de passe actuel</label>
      <input type="password" id="currentPasswordProfile" name="currentPasswordProfile" placeholder="Requis pour changer le mot de passe">
    </div>

    <div>
      <label for="newPasswordProfile">Nouveau mot de passe</label>
      <input type="password" id="newPasswordProfile" name="newPasswordProfile" placeholder="Laisser vide pour ne pas changer">
    </div>

    <div>
      <label for="confirmPasswordProfile">Confirmer le mot de passe</label>
      <input type="password" id="confirmPasswordProfile" name="confirmPasswordProfile" placeholder="Confirmer le nouveau mot de passe">
    </div>

    <!-- Avatar -->
    <div>
      <label for="avatarProfile">Votre photo profil</label>
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= esc($user['avatar']) ?>" alt="Avatar actuel">
      <?php endif; ?>
      <input type="file" id="avatarProfile" name="avatarProfile" accept="image/*">
    </div>

    <!-- Boutons -->
    <div>
      <a href="<?= site_url('profile') ?>">Annuler</a>
      <button type="submit">Enregistrer</button>
    </div>

  </form>

</div>

<?= view('partials/footer') ?>