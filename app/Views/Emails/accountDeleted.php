<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <p>Bonjour <?= esc($firstname) ?> <?= esc($lastname) ?>,</p>
    <p>Nous vous confirmons que votre compte a bien été supprimé le <strong><?= esc($date) ?></strong>.</p>
    <p>Pour toute question : <a href="mailto:<?= esc($support) ?>"><?= esc($support) ?></a></p>
    <p>Cordialement,<br>L'équipe support</p>
</body>
</html>