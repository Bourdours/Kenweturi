<?php

/** @var string $firstname */
/** @var string $lastname */
/** @var string $date */
/** @var string $support */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suppression de votre compte</title>
</head>

<body style="margin:0;padding:0;background-color:#111a26;font-family:'Helvetica Neue',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#111a26;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

          <!-- Header -->
          <tr>
            <td style="background-color:#1a3a5c;border-radius:12px 12px 0 0;padding:28px 40px;text-align:center;">
              <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">Ken</span><span style="font-size:22px;font-weight:700;color:#F2860E;letter-spacing:-0.3px;">weturi</span>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="background-color:#16222e;padding:40px;">
              <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                Bonjour <?= esc($firstname) ?> <?= esc($lastname) ?>,
              </p>
              <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                Nous vous confirmons que votre compte a bien été supprimé le <strong style="color:#ffffff;"><?= esc($date) ?></strong>.
              </p>
              <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                Pour toute question : <a href="mailto:<?= esc($support) ?>" style="color:#378ADD;text-decoration:none;"><?= esc($support) ?></a>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#111a26;border-radius:0 0 12px 12px;padding:24px 40px;border-top:1px solid #1a3a5c;">
              <p style="margin:0;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                Cordialement,<br>
                <span style="color:#93b8d8;">L'équipe Kenweturi</span>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>

</html>