</main>

<footer class="bg-paper border-t border-action/10 px-6 md:px-12 py-6 md:py-10">
  <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-6 md:gap-10 justify-between">

    <!-- Logo + description -->
    <div class="md:max-w-sm">
      <a href="<?= site_url('/') ?>" class="flex items-center gap-2 mb-3">
        <div class="w-6 h-6 rounded-xl bg-action flex items-center justify-center shrink-0">
          <i class="fa-solid fa-car-side text-ink text-xs"></i>
        </div>
        <span class="font-display text-ink font-bold text-xl">Kenweturi</span>
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
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">Comment ça marche</a></li>
        </ul>
      </div>

      <div>
        <p class="text-ink/30 text-xs uppercase tracking-widest font-medium mb-3">Entreprise</p>
        <ul class="flex flex-col gap-2">
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">À propos</a></li>
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">Blog</a></li>
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">Contact</a></li>
        </ul>
      </div>

      <div>
        <p class="text-ink/30 text-xs uppercase tracking-widest font-medium mb-3">Légal</p>
        <ul class="flex flex-col gap-2">
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">CGU</a></li>
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">Confidentialité</a></li>
          <li><a href="#" class="text-ink/60 text-sm hover:text-action transition-colors">Mentions légales</a></li>
        </ul>
      </div>

    </div>
  </div>
</footer>
</body>
</html>
