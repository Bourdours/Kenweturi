<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-5xl mx-auto py-12 px-6 md:px-8 flex flex-col md:flex-row items-center md:items-start gap-12">

  <!-- Panneau branding (desktop uniquement) -->
  <div class="hidden md:flex flex-col flex-1">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 bg-action rounded-xl flex items-center justify-center shrink-0">
        <i class="fa-solid fa-car-side text-ink text-lg"></i>
      </div>
      <span class="text-ink text-xl font-bold font-display">Kenweturi</span>
    </div>
    <h1 class="text-ink text-xl font-semibold font-display mb-2 leading-snug">
      Retrouvez l'accès<br>à votre compte.
    </h1>
    <p class="text-ink/50 text-sm mb-6 leading-relaxed">
      Pas de panique, ça arrive. Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
    </p>
    <div class="flex flex-col gap-3">
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-action/15 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-envelope text-action text-xs"></i>
        </div>
        <span class="text-ink/60 text-sm">Un lien vous sera envoyé par email</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-brand/15 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-clock-rotate-left text-brand text-xs"></i>
        </div>
        <span class="text-ink/60 text-sm">Le lien expire après 1 heure</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-brand/15 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-shield-halved text-brand text-xs"></i>
        </div>
        <span class="text-ink/60 text-sm">Votre compte reste sécurisé</span>
      </div>
    </div>
  </div>

  <!-- Carte formulaire -->
  <div class="bg-surface rounded-2xl p-7 border border-action/10 w-full md:w-auto md:shrink-0" style="min-width:320px; max-width:360px;">

    <div class="mb-5">
      <h3 class="text-ink text-xl font-semibold font-display mb-1">Mot de passe oublié ?</h3>
      <p class="text-ink/40 text-sm">
        <i class="fa-solid fa-lock mr-1.5"></i>Réinitialisez votre mot de passe
      </p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="bg-action/10 border border-action/30 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
        <i class="fa-solid fa-triangle-exclamation text-action text-sm shrink-0"></i>
        <span class="text-action text-xs"><?= session()->getFlashdata('error') ?></span>
      </div>
    <?php endif ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="bg-success/10 border border-success/30 rounded-xl p-3 mb-4 flex gap-2.5 items-center">
        <i class="fa-solid fa-circle-check text-success text-sm shrink-0"></i>
        <span class="text-success text-xs"><?= session()->getFlashdata('success') ?></span>
      </div>
    <?php endif ?>

    <form id="forgotForm" action="<?= base_url('forgotPassword') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="flex flex-col gap-3.5 mb-5">
        <div>
          <label for="email" class="text-ink/50 text-sm mb-1.5 flex items-center gap-1">
            <i class="fa-regular fa-envelope text-sm"></i>Adresse email
          </label>
          <div class="relative">
            <input type="email" name="email" id="email"
              required placeholder="exemple@email.com"
              class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 pr-9 outline-none focus:border-action/50 placeholder:text-ink/30" />
            <i class="fa-regular fa-envelope absolute right-3 top-1/2 -translate-y-1/2 text-ink/30 text-sm pointer-events-none"></i>
          </div>
        </div>
      </div>

      <button type="submit"
        class="w-full bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg py-3 text-sm mb-4 transition-colors cursor-pointer">
        <i class="fa-solid fa-paper-plane mr-2"></i>Envoyer le lien
      </button>

    </form>

    <div class="flex items-center gap-3 mb-4">
      <div class="flex-1 border-t border-action/10"></div>
      <span class="text-ink/30 text-xs">ou</span>
      <div class="flex-1 border-t border-action/10"></div>
    </div>

    <p class="text-center text-xs text-ink/40">
      <a href="<?= site_url('login') ?>" class="text-action font-medium hover:text-action-dark transition-colors">
        <i class="fa-solid fa-arrow-left mr-1"></i>Retour à la connexion
      </a>
    </p>

  </div>

</div>

<script src="<?= base_url('js/auth.js') ?>" defer></script>

<?= view('partials/footer') ?>