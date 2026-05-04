<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Créer un compte</title>
</head>
<body>

    <h2>Inscription</h2>

    <!-- Affichage des erreurs de validation -->
    <?php if (isset($validation)): ?>
        <div style="color: red;">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <form action="#" method="post">
        <?= csrf_field() ?>

        <div>
            <label>Prénom :</label>
            <input type="text" name="firstname" value="<?= old('firstname') ?>" required>
        </div>

        <div>
            <label>Nom :</label>
            <input type="text" name="lastname" value="<?= old('lastname') ?>" required>
        </div>

        <div>
            <label>Email :</label>
            <input type="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div>
            <label>Genre :</label>
            <select name="gender" required>
                <option value="Homme" <?= old('gender') == 'Homme' ? 'selected' : '' ?>>Homme</option>
                <option value="Femme" <?= old('gender') == 'Femme' ? 'selected' : '' ?>>Femme</option>
                <option value="Autre" <?= old('gender') == 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>
        </div>

        <div>
            <label>Date de naissance :</label>
            <input type="date" name="birth_date" value="<?= old('birth_date') ?>">
        </div>

        <div>
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirmer le mot de passe :</label>
            <input type="password" name="pass_confirm" required>
        </div>

        <button type="submit">Créer mon compte</button>
    </form>

</body>
</html>