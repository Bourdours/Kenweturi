</main>

<footer class="bg-paper border-t border-action/10 px-6 md:px-12 py-6 md:py-10">
  <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-6 md:gap-10 justify-between">

    <!-- Logo + description -->
    <div class="md:max-w-sm">
      <a href="<?= site_url('/') ?>" class="flex items-center gap-2 mb-3">
        <img src="<?= base_url('data/images/logo.png') ?>" alt="Kenweturi" class="h-9 w-auto shrink-0">
        <svg width="240" height="36" viewBox="0 0 240 36" xmlns="http://www.w3.org/2000/svg" class="text-ink">
          <defs>
            <clipPath id="cw-f">
              <polygon points="0,0 76,0 56,36 0,36" />
            </clipPath>
            <clipPath id="co-f">
              <polygon points="76,0 240,0 240,36 56,36" />
            </clipPath>
          </defs>
          <text clip-path="url(#cw-f)" x="0" y="28" class="font-display text-2xl font-bold" fill="currentColor" letter-spacing="3">KENWETURI</text>
          <text clip-path="url(#co-f)" x="0" y="28" class="font-display text-2xl font-bold" fill="#D9663F" letter-spacing="3">KENWETURI</text>
        </svg>
      </a>
      <p class="text-ink/40 text-base leading-relaxed">
        Le covoiturage régional pensé pour les trajets domicile-travail et domicile-formation.
      </p>
    </div>

    <!-- Colonnes de liens -->
    <div class="grid grid-cols-3 md:flex md:flex-row gap-6 md:gap-16">

      <div>
        <p class="text-ink/30 text-xs uppercase tracking-widest font-medium mb-3">Service</p>
        <ul class="flex flex-col gap-2">
          <li><a href="<?= site_url('journeys') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Chercher un trajet</a></li>
          <li><a href="<?= site_url('journeys/new') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Publier un trajet</a></li>
          <li><a href="<?= site_url('comment-ca-marche') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Comment ça marche</a></li>
        </ul>
      </div>

      <div>
        <p class="text-ink/30 text-xs uppercase tracking-widest font-medium mb-3">Entreprise</p>
        <ul class="flex flex-col gap-2">
          <li><a href="<?= site_url('a-propos') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">À propos</a></li>
          <li><a href="<?= site_url('blog') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Blog</a></li>
          <li><a href="<?= site_url('contact') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Contact</a></li>
        </ul>
      </div>

      <div>
        <p class="text-ink/30 text-xs uppercase tracking-widest font-medium mb-3">Légal</p>
        <ul class="flex flex-col gap-2">
          <li><a href="<?= site_url('cgu') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">CGU</a></li>
          <li><a href="<?= site_url('confidentialite') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Confidentialité</a></li>
          <li><a href="<?= site_url('mentions-legales') ?>" class="text-ink/60 text-sm hover:text-action transition-colors">Mentions légales</a></li>
        </ul>
      </div>

    </div>
  </div>

  <!-- Bas de footer : copyright + toggle thème -->
  <div class="max-w-6xl mx-auto mt-8 pt-6 border-t border-action/10 flex items-center justify-between">
    <span class="text-ink/30 text-xs">&copy; <?= date('Y') ?> Kenweturi</span>
    <div class="flex items-center gap-4">
      <button id="car-toggle" class="flex items-center gap-1.5 text-ink/40 hover:text-action transition-colors text-xs" aria-label="Activer/désactiver le curseur voiture">
        <i class="fa-solid fa-car-side"></i>
        <span id="car-toggle-label"></span>
      </button>
      <button id="car-model-toggle" class="hidden items-center gap-1.5 text-ink/40 hover:text-action transition-colors text-xs" aria-label="Changer de modèle">
        <i class="fa-solid fa-car"></i>
        <span id="car-model-label"></span>
      </button>
      <?php if ($showRainbowBtn ?? false): ?>
      <button id="rainbow-road-toggle" class="hidden md:flex items-center gap-1.5 text-ink/40 hover:text-action transition-colors text-xs" aria-label="Activer/désactiver Rainbow Road">
        <i class="fa-solid fa-rainbow"></i>
        <span id="rainbow-road-label"></span>
      </button>
      <?php endif; ?>
      <button id="theme-toggle" class="flex items-center gap-1.5 text-ink/40 hover:text-action transition-colors text-xs" aria-label="Changer de thème">
        <i id="theme-icon" class="fa-solid"></i>
        <span id="theme-label"></span>
      </button>
    </div>
  </div>
</footer>
</body>

</html>