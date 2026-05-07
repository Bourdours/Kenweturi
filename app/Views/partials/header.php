<?php
  $onLoginPage   = uri_string() === 'login';
  $loginClass    = $onLoginPage ? 'bg-accent text-primary-darker px-5 py-2 rounded-full font-bold font-display hover:bg-accent-dark' : 'text-white/90 hover:text-accent font-medium';
  $registerClass = $onLoginPage ? 'text-white/90 hover:text-accent font-medium' : 'bg-accent text-primary-darker px-5 py-2 rounded-full font-bold font-display hover:bg-accent-dark';
  $loginClassMobile    = $onLoginPage ? 'text-center bg-accent text-primary-darker px-5 py-2 rounded-full font-bold font-display hover:bg-accent-dark' : 'text-white/90 hover:text-accent font-medium py-2';
  $registerClassMobile = $onLoginPage ? 'text-white/90 hover:text-accent font-medium py-2' : 'text-center bg-accent text-primary-darker px-5 py-2 rounded-full font-bold font-display hover:bg-accent-dark';
?>
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
        <span class="text-lg font-bold text-white font-display">Kenweturi</span>
        </a>
      </div>

      <!-- Nav desktop -->
      <nav class="hidden md:flex items-center gap-8">
        <a href="<?= site_url('journeys') ?>" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Trouver un trajet</a>
        <a href="<?= site_url('journeys/new') ?>" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Proposer un trajet</a>
        <a href="#" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Comment ça marche</a>
      </nav>

      <!-- Auth desktop -->
      <div class="hidden md:flex items-center gap-3">
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Tableau de bord</a>
          <a href="<?= site_url('logout') ?>" class="text-white/90 px-5 py-2 rounded font-medium hover:bg-accent-dark hover:text-primary-darker transition-colors duration-200">Déconnexion</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="<?= $loginClass ?> transition-colors duration-200">Connexion</a>
          <a href="<?= site_url('register') ?>" class="<?= $registerClass ?> transition-colors duration-200">Inscription</a>
        <?php endif; ?>
      </div>

      <!-- Bouton hamburger (mobile) -->
      <button id="menu-toggle" class="md:hidden text-white/90 hover:text-accent focus:outline-none" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menu mobile déroulant -->
    <div id="mobile-menu" class="hidden flex-col px-4 pb-4 border-t border-white/10 md:hidden">

      <?php if (session()->get('isLoggedIn')): ?>
        <!-- Bloc infos utilisateur -->
        <div class="flex items-center gap-3 pt-4 pb-4 border-b border-white/10">
          <div class="w-10 h-10 rounded-full bg-accent flex items-center justify-center text-white font-bold text-sm shrink-0">
            <?= strtoupper(substr((string) session()->get('firstname'), 0, 1) . substr((string) session()->get('lastname'), 0, 1)) ?>
          </div>
          <div class="flex flex-col">
            <span class="text-white font-semibold text-sm leading-tight">
              <?= esc((string) session()->get('firstname')) ?> <?= esc((string) session()->get('lastname')) ?>
            </span>
            <span class="text-white/50 text-xs"><?= esc((string) session()->get('email')) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <nav class="flex flex-col gap-3 pt-4">
        <a href="#" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Trouver un trajet</a>
        <a href="#" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Proposer un trajet</a>
        <a href="#" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Comment ça marche</a>
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-white/90 hover:text-accent font-medium transition-colors duration-200">Tableau de bord</a>
      </nav>

      <div class="flex flex-col gap-2 pt-4 border-t border-white/10 mt-4">
        <a href="<?= site_url('logout') ?>" class="text-white/90 px-5 py-2 rounded font-medium hover:bg-accent-dark hover:text-primary-darker transition-colors duration-200">Déconnexion</a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="<?= $loginClassMobile ?> border-t border-white/10 transition-colors duration-200">Connexion</a>
        <a href="<?= site_url('register') ?>" class="<?= $registerClassMobile ?> transition-colors duration-200">Inscription</a>
      <?php endif; ?>
      </div>
    </div>

  </header>

  <script src="<?= base_url('js/header.js') ?>" defer></script>