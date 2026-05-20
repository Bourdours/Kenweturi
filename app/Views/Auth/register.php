<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8">

  <p class="text-ink/40 text-base mb-1">
    <i class="fa-solid fa-user-plus mr-2"></i>Créer un compte
  </p>
  <h1 class="text-ink text-2xl font-semibold font-display mb-6">
    Rejoignez la communauté Kenweturi
  </h1>

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
  <?php endif ?>

  <form action="<?= base_url('/register') ?>" method="post">
    <?= csrf_field() ?>

    <div class="relative lg:static pl-5 lg:pl-0">
      <!-- Grande route verticale mobile -->
      <div class="lg:hidden absolute left-0 top-0 bottom-0 border-l-2 border-dashed border-action/40"></div>

      <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto_1fr] gap-5 lg:gap-0">

        <!-- Bloc Identité -->
        <div class="bg-surface rounded-2xl p-5 border border-action/10 lg:col-start-1 lg:row-start-1">
          <p class="text-sm font-medium text-ink/50 mb-3 flex items-center gap-1.5">
            <i class="fa-regular fa-id-card"></i>Identité
          </p>

          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <label for="firstName" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-user text-sm"></i>Prénom
              </label>
              <input type="text" name="firstName" id="firstName"
                value="<?= old('firstName') ?>" required placeholder="Jean"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" />
            </div>
            <div>
              <label for="lastName" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-user text-sm"></i>Nom
              </label>
              <input type="text" name="lastName" id="lastName"
                value="<?= old('lastName') ?>" required placeholder="Dupont"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" />
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
              <i class="fa-regular fa-envelope text-sm"></i>Adresse email
            </label>
            <input type="email" name="email" id="email"
              value="<?= old('email') ?>" required placeholder="votre@email.com"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label for="gender" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-venus-mars text-sm"></i>Genre
              </label>
              <div class="relative">
                <select name="gender" id="gender" required
                  class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none appearance-none cursor-pointer focus:border-action/50">
                  <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                  <option value="Femme" <?= old('gender') == 'Femme'  ? 'selected' : '' ?>>Femme</option>
                  <option value="Autre" <?= old('gender') == 'Autre'  ? 'selected' : '' ?>>Autre</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 text-sm pointer-events-none"></i>
              </div>
            </div>
            <div>
              <label for="birthDate" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-regular fa-calendar text-sm"></i>Date de naissance
              </label>
              <input type="date" name="birthDate" id="birthDate"
                max="<?= date('Y-m-d') ?>" value="<?= old('birthDate') ?>"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink/60 text-sm px-3 py-2.5 outline-none focus:border-action/50" />
            </div>
          </div>
        </div>

        <!-- Séparateur gauche (horizontal) -->
        <div class="py-3 lg:col-start-1 lg:row-start-2">
          <div class="border-t-2 border-dashed border-action/35"></div>
        </div>

        <!-- Bloc Localisation -->
        <div class="bg-surface rounded-2xl p-5 border border-action/10 lg:col-start-1 lg:row-start-3">
          <p class="text-sm font-medium text-ink/50 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-location-dot"></i>Localisation
          </p>
          <div class="grid gap-3 mb-4 [grid-template-columns:1fr_120px]">
            <div>
              <label for="cityName" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-city text-sm"></i>Ville
              </label>
              <input type="text" name="cityName" id="cityName"
                value="<?= old('cityName') ?>" required placeholder="Paris"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" />
            </div>
            <div>
              <label for="postalCode" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-hashtag text-sm"></i>Code postal
              </label>
              <input type="text" name="postalCode" id="postalCode"
                value="<?= old('postalCode') ?>" required placeholder="75001"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30" />
            </div>
          </div>
          <!-- Message d'erreur -->
          <div>
              <p class="errorMessage text-red-600 mb-4"></p>
          </div>
          
          <div class="flex items-start gap-2.5 bg-action/5 border border-action/15 rounded-lg px-3 py-2.5">
            <i class="fa-solid fa-circle-info text-action/60 text-sm mt-0.5 shrink-0"></i>
            
            <p class="text-xs text-ink/50 leading-relaxed">
              Votre ville nous permet de vous suggérer des trajets près de chez vous et de connecter les conducteurs et passagers d'une même région.
            </p>
          </div>
        </div>

        <!-- Séparateur mobile entre les deux sections -->
        <div class="lg:hidden border-t-2 border-dashed border-action/35"></div>

        <!-- Séparateur vertical (desktop, col centrale sur 3 lignes) -->
        <div class="hidden lg:flex justify-center items-stretch w-10 lg:col-start-2 lg:row-start-1 lg:row-end-4">
          <div class="border-l-2 border-dashed border-action/35 self-stretch my-6"></div>
        </div>

        <!-- Bloc Sécurité -->
        <div class="bg-surface rounded-2xl p-5 border border-action/10 lg:col-start-3 lg:row-start-1">
          <p class="text-sm font-medium text-ink/50 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved"></i>Sécurité
          </p>

          <div class="mb-3">
            <label for="password" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
              <i class="fa-solid fa-lock text-sm"></i>Mot de passe
            </label>
            <div class="relative">
              <input type="password" name="password" id="password" required
                placeholder="••••••••"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30" />
              <button type="button" onclick="togglePassword('password', this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
                <i class="fa-regular fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          <div>
            <label for="passConfirm" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
              <i class="fa-solid fa-check text-sm"></i>Confirmer le mot de passe
            </label>
            <div class="relative">
              <input type="password" name="passConfirm" id="passConfirm" required
                placeholder="••••••••"
                class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30" />
              <button type="button" onclick="togglePassword('passConfirm', this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 hover:text-action/60 transition-colors">
                <i class="fa-regular fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Indicateur de force du mot de passe -->
          <div class="mt-4 pt-4 border-t border-action/10">
            <div class="flex gap-1.5 mb-3">
              <div class="h-1 flex-1 rounded-full transition-all duration-300" id="sb1"></div>
              <div class="h-1 flex-1 rounded-full transition-all duration-300" id="sb2"></div>
              <div class="h-1 flex-1 rounded-full transition-all duration-300" id="sb3"></div>
              <div class="h-1 flex-1 rounded-full transition-all duration-300" id="sb4"></div>
            </div>
            <div class="flex gap-4">
              <ul class="flex flex-col gap-1.5 flex-1">
                <li id="crit-length" class="flex items-center gap-2 text-xs text-ink/60 dark:text-ink/40 transition-all duration-200">
                  <i class="fa-regular fa-circle w-3 text-center"></i>8 caractères minimum
                </li>
                <li id="crit-upper" class="flex items-center gap-2 text-xs text-ink/60 dark:text-ink/40 transition-all duration-200">
                  <i class="fa-regular fa-circle w-3 text-center"></i>Une lettre majuscule
                </li>
              </ul>
              <ul class="flex flex-col gap-1.5 flex-1">
                <li id="crit-number" class="flex items-center gap-2 text-xs text-ink/60 dark:text-ink/40 transition-all duration-200">
                  <i class="fa-regular fa-circle w-3 text-center"></i>Un chiffre
                </li>
                <li id="crit-special" class="flex items-center gap-2 text-xs text-ink/60 dark:text-ink/40 transition-all duration-200">
                  <i class="fa-regular fa-circle w-3 text-center"></i>Un caractère spécial
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Séparateur droit (horizontal) -->
        <div class="py-3 lg:col-start-3 lg:row-start-2">
          <div class="border-t-2 border-dashed border-action/35"></div>
        </div>

        <!-- Bloc Conditions -->
        <div class="bg-surface rounded-2xl p-5 border border-action/10 lg:col-start-3 lg:row-start-3">
          <p class="text-sm font-medium text-ink/50 mb-3 flex items-center gap-1.5">
            <i class="fa-regular fa-file-lines"></i>Conditions
          </p>
          <label class="flex items-start gap-2 cursor-pointer mb-4">
            <input type="checkbox" required class="mt-0.5 shrink-0 accent-action" />
            <span class="text-xs text-ink/40 leading-relaxed">
              J'accepte les <a href="<?= site_url('cgu') ?>" target="_blank" class="text-action font-medium hover:text-action-dark transition-colors">conditions d'utilisation</a>
              et la <a href="<?= site_url('confidentialite') ?>" target="_blank" class="text-action font-medium hover:text-action-dark transition-colors">politique de confidentialité</a> de Kenweturi.
            </span>
          </label>
          <button type="submit"
            class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg py-3 mb-3 transition-colors cursor-pointer text-base">
            <i class="fa-solid fa-user-plus mr-2"></i>Créer mon compte
          </button>
          <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 border-t border-action/10"></div>
            <span class="text-ink/30 text-xs">ou</span>
            <div class="flex-1 border-t border-action/10"></div>
          </div>
          <p class="text-center text-sm text-ink/40">
            <span class="text-ink/30 text-xs">Déjà un compte ?&nbsp;</span>
            <a href="<?= site_url('login') ?>" class="text-action font-medium hover:text-action-dark transition-colors">Se connecter</a>
          </p>
        </div>

      </div>
    </div>
  </form>
</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>

<?= view('partials/footer') ?>