<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-12 px-4 md:px-8 flex flex-col md:flex-row items-center gap-12">

  <!-- Panneau branding (desktop uniquement) -->
  <div class="hidden md:flex flex-col flex-1">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-car-side text-surface-page text-lg"></i>
      </div>
      <span class="text-white text-xl font-bold font-display">Kenweturi</span>
    </div>
    <h2 class="text-white text-xl font-semibold font-display mb-2 leading-snug">
      Voyagez ensemble,<br>dépensez moins.
    </h2>
    <p class="text-primary-subtle text-sm mb-6 leading-relaxed">
      Rejoignez des milliers de voyageurs qui partagent leurs trajets et réduisent leur empreinte carbone.
    </p>
    <div class="flex flex-col gap-3">
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-accent/15 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-piggy-bank text-accent text-xs"></i>
        </div>
        <span class="text-white/70 text-sm">Économisez sur vos trajets du quotidien</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-primary-label/15 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-leaf text-primary-label text-xs"></i>
        </div>
        <span class="text-white/70 text-sm">Réduisez votre impact environnemental</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-primary-label/15 flex items-center justify-center flex-shrink-0">
          <i class="fa-solid fa-shield-halved text-primary-label text-xs"></i>
        </div>
        <span class="text-white/70 text-sm">Voyagez avec des membres vérifiés</span>
      </div>
    </div>
  </div>

  <!-- Carte formulaire -->
  <div class="bg-surface-card rounded-2xl p-7 border border-white/[0.07] w-full md:w-auto md:flex-shrink-0" style="min-width:320px; max-width:360px;">

    <div class="mb-5">
      <h3 class="text-white text-lg font-semibold font-display mb-1">Bon retour parmi nous !</h3>
      <p class="text-primary-label text-xs">
        <i class="fa-regular fa-user mr-1.5"></i>Connectez-vous à votre compte
      </p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-accent/10 border border-accent/50 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
      <i class="fa-solid fa-triangle-exclamation text-accent text-sm flex-shrink-0"></i>
      <span class="text-accent text-xs"><?= session()->getFlashdata('error') ?></span>
    </div>
    <?php endif ?>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-primary-subtle/10 border border-primary-subtle/40 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
      <i class="fa-solid fa-circle-check text-primary-subtle text-sm flex-shrink-0"></i>
      <span class="text-primary-subtle text-xs"><?= session()->getFlashdata('success') ?></span>
    </div>
    <?php endif ?>

    <form action="<?= base_url('index.php/login/check') ?>" method="post">
      <?= csrf_field() ?>

      <div class="flex flex-col gap-3.5 mb-5">

        <div>
          <label for="email" class="text-[10px] text-primary-label mb-1.5 flex items-center gap-1">
            <i class="fa-regular fa-envelope text-[9px]"></i>Adresse email
          </label>
          <div class="relative">
            <input type="email" name="email" id="email"
              value="<?= old('email') ?>" required placeholder="votre@email.com"
              class="w-full bg-surface-input border border-white/15 rounded-lg text-white text-sm px-3 py-2.5 pr-9 outline-none focus:border-accent/60 placeholder:text-muted" />
            <i class="fa-regular fa-envelope absolute right-3 top-1/2 -translate-y-1/2 text-muted text-[11px] pointer-events-none"></i>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label for="password" class="text-[10px] text-primary-label flex items-center gap-1">
              <i class="fa-solid fa-lock text-[9px]"></i>Mot de passe
            </label>
            <a href="#" class="text-[11px] text-accent">Mot de passe oublié ?</a>
          </div>
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

      </div>

      <button type="submit"
        class="w-full bg-accent hover:bg-accent-dark text-surface-card font-semibold font-display rounded-lg py-3 text-sm mb-4 transition-colors cursor-pointer">
        <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>Se connecter
      </button>

    </form>

    <div class="flex items-center gap-3 mb-4">
      <div class="flex-1 border-t border-white/10"></div>
      <span class="text-muted text-xs">ou</span>
      <div class="flex-1 border-t border-white/10"></div>
    </div>

    <p class="text-center text-xs text-muted">
      Pas encore de compte ?
      <a href="<?= site_url('register') ?>" class="text-accent font-medium">Créer un compte gratuit</a>
    </p>

  </div>

</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>

<?= view('partials/footer') ?>
