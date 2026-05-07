<?php
  $onLoginPage        = uri_string() === 'login';
  $loginClass         = $onLoginPage
    ? 'bg-action text-ink px-5 py-2 rounded-full font-semibold font-display hover:bg-action-dark'
    : 'text-ink/70 hover:text-action font-medium';
  $registerClass      = $onLoginPage
    ? 'text-ink/70 hover:text-action font-medium'
    : 'bg-action text-ink px-5 py-2 rounded-full font-semibold font-display hover:bg-action-dark';
  $loginClassMobile   = $onLoginPage
    ? 'text-center bg-action text-ink px-5 py-2 rounded-full font-semibold font-display hover:bg-action-dark'
    : 'text-ink/70 hover:text-action font-medium py-2';
  $registerClassMobile = $onLoginPage
    ? 'text-ink/70 hover:text-action font-medium py-2'
    : 'text-center bg-action text-ink px-5 py-2 rounded-full font-semibold font-display hover:bg-action-dark';
?>
<body class="flex flex-col min-h-screen">
  <header class="bg-paper border-b border-action/10">

    <!-- Barre principale -->
    <div class="flex items-center justify-between px-6 py-3 md:px-12 md:py-4">

      <!-- Logo -->
      <a href="<?= site_url('/') ?>" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-full bg-action flex items-center justify-center shrink-0">
          <i class="fa-solid fa-car-side text-ink text-xs"></i>
        </div>
        <span class="text-base font-bold text-ink font-display">Kenweturi</span>
      </a>

      <!-- Nav desktop -->
      <nav class="hidden md:flex items-center gap-8">
        <a href="<?= site_url('journeys') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Chercher un trajet</a>
        <a href="<?= site_url('journeys/new') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Publier un trajet</a>
        <a href="#" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Comment ça marche</a>
      </nav>

      <!-- Auth desktop -->
      <div class="hidden md:flex items-center gap-4">
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Tableau de bord</a>
          <a href="<?= site_url('logout') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Déconnexion</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="<?= $loginClass ?> text-sm transition-colors duration-200">Connexion</a>
          <a href="<?= site_url('register') ?>" class="<?= $registerClass ?> text-sm transition-colors duration-200">S'inscrire</a>
        <?php endif; ?>
      </div>

      <!-- Bouton hamburger (mobile) -->
      <button id="menu-toggle" class="md:hidden text-ink/70 hover:text-action focus:outline-none" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menu mobile déroulant -->
    <div id="mobile-menu" class="hidden flex-col px-6 pb-4 border-t border-action/10 md:hidden">

      <?php if (session()->get('isLoggedIn')): ?>
        <!-- Bloc infos utilisateur -->
        <div class="flex items-center gap-3 pt-4 pb-4 border-b border-action/10">
          <div class="w-10 h-10 rounded-full bg-action/20 flex items-center justify-center text-action font-bold text-sm shrink-0">
            <?= strtoupper(substr((string) session()->get('firstname'), 0, 1) . substr((string) session()->get('lastname'), 0, 1)) ?>
          </div>
          <div class="flex flex-col">
            <span class="text-ink font-semibold text-sm leading-tight">
              <?= esc((string) session()->get('firstname')) ?> <?= esc((string) session()->get('lastname')) ?>
            </span>
            <span class="text-ink/40 text-xs"><?= esc((string) session()->get('email')) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <nav class="flex flex-col gap-3 pt-4">
        <a href="<?= site_url('journeys') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Chercher un trajet</a>
        <a href="<?= site_url('journeys/new') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Publier un trajet</a>
        <a href="#" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Comment ça marche</a>
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('dashboard') ?>" class="text-ink/70 hover:text-action font-medium text-sm transition-colors duration-200">Tableau de bord</a>
      </nav>

      <div class="flex flex-col gap-2 pt-4 border-t border-action/10 mt-4">
        <a href="<?= site_url('logout') ?>" class="text-ink/70 hover:text-action font-medium text-sm py-2 transition-colors duration-200">Déconnexion</a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="<?= $loginClassMobile ?> text-sm border-t border-action/10 transition-colors duration-200">Connexion</a>
        <a href="<?= site_url('register') ?>" class="<?= $registerClassMobile ?> text-sm transition-colors duration-200">S'inscrire</a>
      <?php endif; ?>
      </div>
    </div>

  </header>

  <script src="<?= base_url('js/header.js') ?>" defer></script>

<main class="flex-1 bg-paper">
