<body>
<header>

  <div>
    <div>
      <i class="fa-solid fa-car-side"></i>
    </div>
    <span>Kenweturi</span>
  </div>

  <nav>
    <a href="#">Trouver un trajet</a>
    <a href="#">Proposer un trajet</a>
    <a href="#">Comment ça marche</a>
  </nav>

  <div>
    <?php if (session()->get('isLoggedIn')): ?>
      <!-- Affichage si connecté -->
    <a href="<?= site_url('dashboard') ?>">Tableau de bord</a>
    <a href="<?= site_url('logout') ?>" >Déconnexion</a>

    <!-- Affichage si déconnecté -->
    <?php else: ?>
    <a href="<?= site_url('login') ?>">Connexion</a>
    <a href="<?= site_url('inscription') ?>" >Inscription</a>
    <?php endif; ?>
  </div>
</header>