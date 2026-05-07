<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-5xl mx-auto py-8 px-4 md:px-8">

    <p class="text-primary-label text-sm mb-1">
      <i class="fa-solid fa-user-plus mr-2"></i>Créer un compte
    </p>
    <h1 class="text-white text-2xl font-semibold font-display mb-6">
      Rejoignez la communauté Kenweturi
    </h1>

    <?php if (session()->has('errors')): ?>
    <div class="bg-accent/10 border border-accent/50 rounded-xl p-4 mb-6 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-accent text-base flex-shrink-0 mt-0.5"></i>
      <div>
        <p class="text-white text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
        <ul class="list-disc pl-4 flex flex-col gap-1">
          <?php foreach (session('errors') as $error): ?>
          <li class="text-accent text-xs"><?= esc((string) $error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
    <?php endif ?>

    <form action="<?= base_url('/handleRegister') ?>" method="post">
      <?= csrf_field() ?>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Colonne gauche : Identité + Localisation -->
        <div class="flex flex-col gap-4">

          <div class="bg-surface-card rounded-2xl p-5 border border-white/[0.07]">
            <p class="text-xs font-medium text-primary-label mb-3 flex items-center gap-1.5">
              <i class="fa-regular fa-id-card"></i>Identité
            </p>

            <div class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <label for="firstName" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-user text-[9px]"></i>Prénom
                </label>
                <input type="text" name="firstName" id="firstName"
                  value="<?= old('firstName') ?>" required placeholder="Jean"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60 placeholder:text-muted" />
              </div>
              <div>
                <label for="lastName" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-user text-[9px]"></i>Nom
                </label>
                <input type="text" name="lastName" id="lastName"
                  value="<?= old('lastName') ?>" required placeholder="Dupont"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60 placeholder:text-muted" />
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                <i class="fa-regular fa-envelope text-[9px]"></i>Adresse email
              </label>
              <input type="email" name="email" id="email"
                value="<?= old('email') ?>" required placeholder="votre@email.com"
                class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60 placeholder:text-muted" />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label for="gender" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-venus-mars text-[9px]"></i>Genre
                </label>
                <div class="relative">
                  <select name="gender" id="gender" required
                    class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none appearance-none cursor-pointer">
                    <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                    <option value="Femme"  <?= old('gender') == 'Femme'  ? 'selected' : '' ?>>Femme</option>
                    <option value="Autre"  <?= old('gender') == 'Autre'  ? 'selected' : '' ?>>Autre</option>
                  </select>
                  <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-muted text-[10px] pointer-events-none"></i>
                </div>
              </div>
              <div>
                <label for="birthDate" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-regular fa-calendar text-[9px]"></i>Date de naissance
                </label>
                <input type="date" name="birthDate" id="birthDate"
                  max="<?= date('Y-m-d') ?>" value="<?= old('birthDate') ?>"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60" />
              </div>
            </div>
          </div>

          <div class="bg-surface-card rounded-2xl p-5 border border-white/[0.07]">
            <p class="text-xs font-medium text-primary-label mb-3 flex items-center gap-1.5">
              <i class="fa-solid fa-location-dot"></i>Localisation
            </p>
            <div class="grid gap-3" style="grid-template-columns:1fr 120px;">
              <div>
                <label for="cityName" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-city text-[9px]"></i>Ville
                </label>
                <input type="text" name="cityName" id="cityName"
                  value="<?= old('cityName') ?>" required placeholder="Paris"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60 placeholder:text-muted" />
              </div>
              <div>
                <label for="postalCode" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-hashtag text-[9px]"></i>Code postal
                </label>
                <input type="text" name="postalCode" id="postalCode"
                  value="<?= old('postalCode') ?>" required placeholder="75001"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-accent/60 placeholder:text-muted" />
              </div>
            </div>
          </div>

        </div>

        <!-- Colonne droite : Sécurité + Conditions -->
        <div class="flex flex-col gap-4">

          <div class="bg-surface-card rounded-2xl p-5 border border-white/[0.07]">
            <p class="text-xs font-medium text-primary-label mb-3 flex items-center gap-1.5">
              <i class="fa-solid fa-shield-halved"></i>Sécurité
            </p>

            <div class="mb-3">
              <label for="password" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-lock text-[9px]"></i>Mot de passe
              </label>
              <div class="relative">
                <input type="password" name="password" id="password" required
                  placeholder="••••••••"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 pr-9 outline-none focus:border-accent/60 placeholder:text-muted" />
                <button type="button" onclick="togglePassword('password', this)"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-primary-subtle transition-colors">
                  <i class="fa-regular fa-eye text-xs"></i>
                </button>
              </div>
            </div>

            <div>
              <label for="passConfirm" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-lock text-[9px]"></i>Confirmer le mot de passe
              </label>
              <div class="relative">
                <input type="password" name="passConfirm" id="passConfirm" required
                  placeholder="••••••••"
                  class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 pr-9 outline-none focus:border-accent/60 placeholder:text-muted" />
                <button type="button" onclick="togglePassword('passConfirm', this)"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-primary-subtle transition-colors">
                  <i class="fa-regular fa-eye text-xs"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="bg-surface-card rounded-2xl p-5 border border-white/[0.07]">
            <p class="text-xs font-medium text-primary-label mb-3 flex items-center gap-1.5">
              <i class="fa-regular fa-file-lines"></i>Conditions
            </p>
            <label class="flex items-start gap-2 cursor-pointer mb-4">
              <input type="checkbox" required class="mt-0.5 flex-shrink-0 accent-accent" />
              <span class="text-[11px] text-muted leading-relaxed">
                J'accepte les <a href="#" class="text-accent font-medium">conditions d'utilisation</a>
                et la <a href="#" class="text-accent font-medium">politique de confidentialité</a> de Kenweturi.
              </span>
            </label>
            <button type="submit"
              class="w-full bg-accent hover:bg-accent-dark text-surface-card font-semibold font-display rounded-lg py-3 text-sm mb-3 transition-colors cursor-pointer">
              <i class="fa-solid fa-user-plus mr-2"></i>Créer mon compte
            </button>
            <p class="text-center text-xs text-muted">
              Déjà un compte ?
              <a href="<?= site_url('login') ?>" class="text-accent font-medium">Se connecter</a>
            </p>
          </div>

        </div>
      </div>
    </form>
</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>

<?= view('partials/footer') ?>
