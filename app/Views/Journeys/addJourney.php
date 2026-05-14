<?= view('partials/head') ?>
<?= view('partials/header') ?>

<main>
    <div>
  <p>
    <i></i>Créer un compte
  </p>
  <h1>
    Rejoignez la communauté Kenweturi
  </h1>

  <?php if (session()->has('errors')): ?>
  <div>
    <i></i>
    <div>
      <p>Veuillez corriger les erreurs suivantes :</p>
      <ul>
        <?php foreach (session('errors') as $error): ?>
        <li><?= esc((string) $error) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  </div>
  <?php endif ?>

  <form action="<?= base_url('/handleRegister') ?>" method="post">
    <?= csrf_field() ?>

    <div>

      <!-- Colonne gauche : Identité + Localisation -->
      <div>

        <div>
          <p>
            <i></i>Identité
          </p>

          <div>
            <div>
              <label for="firstName">
                <i></i>Prénom
              </label>
              <input type="text" name="firstName" id="firstName"
                value="<?= old('firstName') ?>" required placeholder="Jean" />
            </div>
            <div>
              <label for="lastName">
                <i></i>Nom
              </label>
              <input type="text" name="lastName" id="lastName"
                value="<?= old('lastName') ?>" required placeholder="Dupont" />
            </div>
          </div>

          <div>
            <label for="email">
              <i></i>Adresse email
            </label>
            <input type="email" name="email" id="email"
              value="<?= old('email') ?>" required placeholder="votre@email.com" />
          </div>

          <div>
            <div>
              <label for="gender">
                <i></i>Genre
              </label>
              <div>
                <select name="gender" id="gender" required>
                  <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                  <option value="Femme"  <?= old('gender') == 'Femme'  ? 'selected' : '' ?>>Femme</option>
                  <option value="Autre"  <?= old('gender') == 'Autre'  ? 'selected' : '' ?>>Autre</option>
                </select>
                <i></i>
              </div>
            </div>
            <div>
              <label for="birthDate">
                <i></i>Date de naissance
              </label>
              <input type="date" name="birthDate" id="birthDate"
                max="<?= date('Y-m-d') ?>" value="<?= old('birthDate') ?>" />
            </div>
          </div>
        </div>

        <div>
          <p>
            <i></i>Localisation
          </p>
          <div>
            <div>
              <label for="cityName">
                <i></i>Ville
              </label>
              <input type="text" name="cityName" id="cityName"
                value="<?= old('cityName') ?>" required placeholder="Paris" />
            </div>
            <div>
              <label for="postalCode">
                <i></i>Code postal
              </label>
              <input type="text" name="postalCode" id="postalCode"
                value="<?= old('postalCode') ?>" required placeholder="75001" />
            </div>
          </div>
        </div>

      </div>

      <!-- Colonne droite : Sécurité + Conditions -->
      <div>

        <div>
          <p>
            <i></i>Sécurité
          </p>

          <div>
            <label for="password">
              <i></i>Mot de passe
            </label>
            <div>
              <input type="password" name="password" id="password" required
                placeholder="••••••••" />
              <button type="button" onclick="togglePassword('password', this)">
                <i></i>
              </button>
            </div>
          </div>

          <div>
            <label for="passConfirm">
              <i></i>Confirmer le mot de passe
            </label>
            <div>
              <input type="password" name="passConfirm" id="passConfirm" required
                placeholder="••••••••" />
              <button type="button" onclick="togglePassword('passConfirm', this)">
                <i></i>
              </button>
            </div>
          </div>
        </div>

        <div>
          <p>
            <i></i>Conditions
          </p>
          <label>
            <input type="checkbox" required />
            <span>
              J'accepte les <a href="#">conditions d'utilisation</a>
              et la <a href="#">politique de confidentialité</a> de Kenweturi.
            </span>
          </label>
          <button type="submit">
            <i></i>Créer mon compte
          </button>
          <p>
            Déjà un compte ?
</main>

<?= view('partials/footer') ?>