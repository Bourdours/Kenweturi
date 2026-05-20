<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

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

  <!-- Formulaire -->
  <form action="<?= site_url('profile/update') ?>" method="post" enctype="multipart/form-data" class="flex flex-col gap-6">
    <?= csrf_field() ?>

    <!-- Photo de profil -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-image text-action text-sm"></i>Photo de profil
      </h2>

      <?php $initials = strtoupper(substr((string) $user['firstname'], 0, 1) . substr((string) $user['lastname'], 0, 1)); ?>

      <div class="flex items-center gap-4 mb-4">
        <?php if (!empty($user['avatar'])): ?>
          <div class="jsAvatarOpen cursor-pointer w-14 h-14 rounded-full overflow-hidden shrink-0">
            <img src="<?= esc(base_url($user['avatar'])) ?>" alt="Avatar actuel" class="w-full h-full object-cover"
              onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
          </div>
          <div class="jsAvatarOpen cursor-pointer hidden w-14 h-14 rounded-full bg-action text-paper font-bold text-base shrink-0 items-center justify-center">
            <?= $initials ?>
          </div>
        <?php else: ?>
          <div class="jsAvatarOpen cursor-pointer flex w-14 h-14 rounded-full bg-action text-paper font-bold text-base shrink-0 items-center justify-center">
            <?= $initials ?>
          </div>
        <?php endif; ?>

        <p class="text-ink/50 text-xs">
          <?= !empty($user['avatar']) ? 'Photo actuelle — choisissez un nouveau fichier pour la remplacer.' : 'Aucune photo — choisissez un fichier pour en ajouter une.' ?>
        </p>
      </div>

      <label for="avatarProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Nouvelle photo</label>
      <input type="file" id="avatarProfile" name="avatarProfile" accept="image/*"
        class="w-full text-ink/70 text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-action/10 file:text-action hover:file:bg-action/20 file:cursor-pointer file:transition-colors">
    </div>

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

        <!-- Ville -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="relative">
            <label for="cityProfile" class="text-ink/50 text-xs font-medium mb-1.5 block ">Ville</label>
            <input type="text" id="cityProfile" name="cityName" value="<?= esc($city ?? '') ?>"
              placeholder="Votre ville..."
              autocomplete="off"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
          </div>
          <div>
            <label for="zipcodeProfile" class="text-ink/50 text-xs font-medium mb-1.5 block">Code postal</label>
            <input type="text" id="zipcodeProfile" name="postalCode" value="<?= esc($zipcode ?? '') ?>"
              placeholder="Ex: 75001"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors">
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

    <!-- Véhicules -->
    <div class="bg-surface rounded-2xl p-6 border border-action/10">
      <h2 class="text-ink text-base font-semibold font-display mb-5 flex items-center gap-2">
        <i class="fa-solid fa-car text-action text-sm"></i>Mes véhicules
      </h2>

      <?php if (!empty($cars)): ?>
        <div class="flex flex-col gap-3 mb-5">
          <?php foreach ($cars as $car): ?>
            <div class="flex items-center justify-between bg-paper border border-action/10 rounded-xl px-4 py-3">
              <div class="flex items-center gap-3">
                <i class="fa-solid fa-car-side text-action/50 text-sm"></i>
                <div>
                  <p class="text-ink text-sm font-medium"><?= esc($car['brand']) ?> <?= esc($car['model']) ?></p>
                  <p class="text-ink/40 text-xs"><?= esc($car['color']) ?> · <?= esc($car['seats']) ?> places</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <a href="<?= site_url('car/' . $car['id'] . '/edit') ?>"
                  class="text-ink/30 hover:text-action text-xs transition-colors">
                  <i class="fa-solid fa-pen"></i>
                </a>

                <button type="button"
                  class="deleteCar text-ink/30 hover:text-action text-xs transition-colors"
                  data-car-id="<?= $car['id'] ?>"
                  data-car-label="<?= esc($car['brand'] . ' ' . $car['model']) ?>">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          <?php endforeach ?>
        </div>
      <?php else: ?>
        <p class="text-ink/30 text-xs mb-5">Aucun véhicule enregistré.</p>
      <?php endif; ?>

      <p class="text-ink/50 text-xs font-medium mb-3">Ajouter un véhicule</p>
      <div id="carFieldsContainer" class="flex flex-col gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="vehicleBrand" class="text-ink/50 text-xs font-medium mb-1.5 block">Marque</label>
            <div class="relative w-full">
              <input type="text" id="vehicleBrand" data-name="brand"
                placeholder="Ex: Renault"
                autocomplete="off"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">

              <ul id="brandSuggestions" class="absolute left-0 top-full z-50 w-full bg-paper border border-action/15 rounded-b-lg shadow-lg max-h-48 overflow-y-auto hidden flex flex-col pointer-events-auto"></ul>
            </div>
          </div>
          <div>
            <label for="vehicleModel" class="text-ink/50 text-xs font-medium mb-1.5 block">Modèle</label>
            <input type="text" id="vehicleModel" data-name="model"
              placeholder="Ex: Clio"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="vehicleColor" class="text-ink/50 text-xs font-medium mb-1.5 block">Couleur</label>
            <input type="text" id="vehicleColor" data-name="color"
              placeholder="Ex: Bleu"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
          </div>
          <div>
            <label for="vehicleSeats" class="text-ink/50 text-xs font-medium mb-1.5 block">Nombre de places</label>
            <input type="number" id="vehicleSeats" data-name="seats" min="1" max="9"
              placeholder="Ex: 5"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors js-car-input">
          </div>
        </div>

        <p id="carError" class="text-action text-xs hidden mt-1">Veuillez remplir tous les champs. Le nombre de places doit être entre 1 et 9.</p>

        <div class="flex justify-end">
          <button type="button" id="addCarBtn"
            data-action="<?= site_url('car/create') ?>"
            data-csrf-name="<?= csrf_token() ?>"
            data-csrf-value="<?= csrf_hash() ?>"
            class="flex items-center gap-2 bg-action/10 hover:bg-action/20 text-action font-semibold text-sm rounded-lg px-4 py-2 transition-colors cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>Ajouter
          </button>
        </div>
      </div>

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

<!-- Modal avatar -->
<div id="avatarModal" class="hidden fixed inset-0 bg-black/80 z-[9999] items-center justify-center cursor-zoom-out">
  <?php if (!empty($user['avatar'])): ?>
    <img src="<?= esc(base_url($user['avatar'])) ?>" alt="Avatar de <?= esc($user['firstname']) ?>" class="max-w-[90vw] max-h-[90vh] rounded-lg object-contain shadow-[0_0_40px_rgba(0,0,0,0.5)]"
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

<!-- Modale suppression voiture -->
<div id="deleteCarModal" class="hidden fixed inset-0 bg-black/60 z-[9999] items-center justify-center">
  <div class="bg-surface rounded-2xl p-6 w-full max-w-sm mx-4 shadow-xl">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-9 h-9 rounded-full bg-action/10 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-trash text-action text-sm"></i>
      </div>
      <h3 class="text-ink font-semibold font-display text-base">Supprimer le véhicule</h3>
    </div>
    <p class="text-ink/50 text-sm mb-5">
      Voulez-vous vraiment supprimer <span id="deleteCarLabel" class="text-ink font-medium"></span> ? Cette action est irréversible.
    </p>
    <form id="deleteCarForm" method="post" action="">
      <?= csrf_field() ?>
      <div class="flex gap-3 justify-end">
        <button type="button" id="cancelDeleteCar"
          class="border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-4 py-2 text-sm transition-colors">
          Annuler
        </button>
        <button type="submit"
          class="bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-4 py-2 text-sm transition-colors cursor-pointer flex items-center gap-2">
          <i class="fa-solid fa-trash text-xs"></i>Supprimer
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>
<script src="<?= base_url('js/user.js') ?>"></script>
<script>
  window.baseUrl = "<?= base_url() ?>";
</script>
<?= view('partials/footer') ?>