<body>
  <header class="bg-primary-dark border-b border-white/10">

    <!-- Barre principale -->
    <div class="flex items-center justify-between px-4 py-3 md:px-8 md:py-4">

      <!-- Logo -->
      <div class="flex items-center gap-2">
        <div class="text-accent text-xl">
          <a href="<?= site_url('/') ?>">
            <i class="fa-solid fa-car-side"></i>
        </div>
        <span class="text-lg font-bold text-white">Kenweturi</span>
        </a>
      </div>

      <!-- Nav desktop -->
      <nav class="hidden md:flex items-center gap-8">
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Trouver un trajet</a>
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Proposer un trajet</a>
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Comment ça marche</a>
      </nav>

      <!-- Auth desktop -->
      <div class="hidden md:flex items-center gap-3">
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Tableau de bord</a>
          <a href="<?= site_url('logout') ?>" class="bg-danger text-white px-5 py-2 rounded-full font-medium hover:bg-danger-dark transition-colors duration-200">Déconnexion</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Connexion</a>
          <a href="<?= site_url('inscription') ?>" class="bg-accent text-white px-5 py-2 rounded-full font-medium hover:bg-accent-dark transition-colors duration-200">Inscription</a>
        <?php endif; ?>
      </div>

      <!-- Bouton hamburger (mobile) -->
      <button id="menu-toggle" class="md:hidden text-white/70 hover:text-white focus:outline-none" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menu mobile déroulant -->
    <div id="mobile-menu" class="hidden flex-col px-4 pb-4 border-t border-white/10 md:hidden">
      <nav class="flex flex-col gap-3 pt-4">
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Trouver un trajet</a>
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Proposer un trajet</a>
        <a href="#" class="text-white/70 hover:text-white font-medium transition-colors duration-200">Comment ça marche</a>
      </nav>
      <div class="flex flex-col gap-2 pt-4 border-t border-white/10 mt-4">
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-center text-white/70 hover:text-white font-medium py-2 transition-colors duration-200">Tableau de bord</a>
          <a href="<?= site_url('logout') ?>" class="text-center bg-danger text-white px-5 py-2 rounded-full font-medium hover:bg-danger-dark transition-colors duration-200">Déconnexion</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="text-center text-white/70 hover:text-white font-medium py-2 transition-colors duration-200">Connexion</a>
          <a href="<?= site_url('inscription') ?>" class="text-center bg-accent text-white px-5 py-2 rounded-full font-medium hover:bg-accent-dark transition-colors duration-200">Inscription</a>
        <?php endif; ?>
      </div>
    </div>

  </header>

  <script src="<?= base_url('js/header.js') ?>" defer></script>