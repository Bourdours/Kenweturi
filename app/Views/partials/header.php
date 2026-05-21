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
    <div class="relative flex items-center justify-between px-6 py-3 md:px-12 md:py-4">

      <!-- Logo -->
      <a href="<?= site_url('/') ?>" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-xl bg-action flex items-center justify-center shrink-0">
          <i class="fa-solid fa-car-side text-ink text-xs"></i>
        </div>
        <!-- <span class="text-2xl font-bold text-ink font-display">Kenweturi</span> -->
        <svg width="240" height="36" viewBox="0 0 240 36" xmlns="http://www.w3.org/2000/svg" class="text-ink">
          <defs>
            <clipPath id="cw">
              <polygon points="0,0 76,0 56,36 0,36" />
            </clipPath>
            <clipPath id="co">
              <polygon points="76,0 240,0 240,36 56,36" />
            </clipPath>
          </defs>
          <text clip-path="url(#cw)"
            x="0" y="28"
            class="font-display text-2xl font-bold"
            fill="currentColor" letter-spacing="3">KENWETURI</text>
          <text clip-path="url(#co)"
            x="0" y="28"
            class="font-display text-2xl font-bold"
            fill="#D9663F" letter-spacing="3">KENWETURI</text>
        </svg>
      </a>

      <!-- Nav desktop -->
      <nav class="hidden lg:flex absolute left-1/2 -translate-x-1/2 items-center gap-8">
        <a href="<?= site_url('journeys') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Chercher un trajet</a>
        <a href="<?= site_url('journeys/new') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Publier un trajet</a>
        <a href="<?= site_url('comment-ca-marche') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Comment ça marche</a>
      </nav>

      <!-- Auth desktop + hamburger mobile -->
      <div class="flex items-center justify-end gap-4">
        <div class="hidden lg:flex items-center gap-4">
          <?php if (session()->get('isLoggedIn')): ?>
            <!-- Menu utilisateur avec dropdown -->
            <div class="relative" id="user-menu-wrapper">
              <button id="user-menu-toggle" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
                <?php $initials = strtoupper(substr((string) session()->get('firstname'), 0, 1) . substr((string) session()->get('lastname'), 0, 1)); ?>
                <?php if (session()->get('avatar')): ?>
                  <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 desktop-avatar-img">
                    <img src="<?= esc(base_url(session()->get('avatar'))) ?>" alt="" class="w-full h-full object-cover"
                      onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
                  </div>
                  <div class="hidden w-9 h-9 rounded-full bg-action text-paper font-bold text-xs shrink-0 items-center justify-center">
                    <?= $initials ?>
                  </div>
                <?php else: ?>
                  <div class="flex w-9 h-9 rounded-full bg-action text-paper font-bold text-xs shrink-0 items-center justify-center">
                    <?= $initials ?>
                  </div>
                <?php endif; ?>
                <span class="text-ink font-medium text-sm"><?= esc((string) session()->get('firstname')) ?></span>
                <i class="fa-solid fa-chevron-down text-ink/40 text-xs"></i>
              </button>

              <!-- Dropdown -->
              <div id="user-dropdown" class="hidden absolute right-0 top-full mt-4 w-56 bg-paper border border-action/10 rounded-xl shadow-lg py-2 z-50">
                <div class="px-4 py-3 border-b border-action/10">
                  <p class="text-ink font-semibold text-sm"><?= esc((string) session()->get('firstname')) ?> <?= esc((string) session()->get('lastname')) ?></p>
                  <p class="text-ink/40 text-xs"><?= esc((string) session()->get('email')) ?></p>
                </div>
                <a href="<?= site_url('profile') ?>" class="flex items-center gap-2 px-4 py-2 text-ink/70 hover:text-action text-sm transition-colors duration-200">
                  <i class="fa-solid fa-user text-xs w-4"></i> Mon profil
                </a>
                <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2 px-4 py-2 text-ink/70 hover:text-action text-sm transition-colors duration-200">
                  <i class="fa-solid fa-gauge text-xs w-4"></i> Tableau de bord
                </a>
                <div class="border-t border-action/10 mt-1 pt-1">
                  <a href="<?= site_url('logout') ?>" class="flex items-center gap-2 px-4 py-2 text-ink/70 hover:text-action text-sm transition-colors duration-200">
                    <i class="fa-solid fa-right-from-bracket text-xs w-4"></i> Déconnexion
                  </a>
                </div>
              </div>
            </div>
          <?php else: ?>
            <a href="<?= site_url('login') ?>" class="<?= $loginClass ?> text-base transition-colors duration-200">Connexion</a>
            <a href="<?= site_url('register') ?>" class="<?= $registerClass ?> text-base transition-colors duration-200">S'inscrire</a>
          <?php endif; ?>
        </div>

        <!-- Bouton hamburger (mobile) -->
        <button id="menu-toggle" class="lg:hidden text-ink/70 hover:text-action focus:outline-none" aria-label="Menu">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Menu mobile déroulant -->
    <div id="mobile-menu" class="hidden flex-col px-6 pb-4 border-t border-action/10 lg:hidden">

      <?php if (session()->get('isLoggedIn')): ?>
        <!-- Bloc infos utilisateur -->
        <div class="flex items-center gap-3 pt-4 pb-4 border-b border-action/10">
          <?php if (session()->get('avatar')): ?>
            <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 mobile-avatar-img">
              <img src="<?= esc(base_url(session()->get('avatar'))) ?>" alt="" class="w-full h-full object-cover"
                onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';">
            </div>
            <div class="hidden w-10 h-10 rounded-full bg-action text-paper font-bold text-xs shrink-0 items-center justify-center">
              <?= $initials ?>
            </div>
          <?php else: ?>
            <div class="flex w-10 h-10 rounded-full bg-action text-paper font-bold text-xs shrink-0 items-center justify-center">
              <?= $initials ?>
            </div>
          <?php endif; ?>
          <div class="flex flex-col">
            <span class="text-ink font-semibold text-sm leading-tight">
              <?= esc((string) session()->get('firstname')) ?> <?= esc((string) session()->get('lastname')) ?>
            </span>
            <span class="text-ink/40 text-xs"><?= esc((string) session()->get('email')) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <nav class="flex flex-col gap-3 pt-4">
        <a href="<?= site_url('journeys') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Chercher un trajet</a>
        <a href="<?= site_url('journeys/new') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Publier un trajet</a>
        <a href="<?= site_url('comment-ca-marche') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Comment ça marche</a>
        <?php if (session()->get('isLoggedIn')): ?>
          <div class="flex flex-col gap-2 pt-4 border-t border-action/10 mt-4">
            <a href="<?= site_url('profile') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Mon profil</a>
            <a href="<?= site_url('dashboard') ?>" class="text-ink/70 hover:text-action font-medium text-base transition-colors duration-200">Tableau de bord</a>
          </div>
      </nav>

      <div class="flex flex-col gap-2 pt-4 border-t border-action/10 mt-4">
        <a href="<?= site_url('logout') ?>" class="text-ink/70 hover:text-action font-medium text-base py-2 transition-colors duration-200">Déconnexion</a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="<?= $loginClassMobile ?> text-base border-t border-action/10 transition-colors duration-200">Connexion</a>
        <a href="<?= site_url('register') ?>" class="<?= $registerClassMobile ?> text-base transition-colors duration-200">S'inscrire</a>
      <?php endif; ?>
      </div>
    </div>

  </header>


  <main class="flex-1 bg-paper">