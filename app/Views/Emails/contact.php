<?php
/** @var string $name */
/** @var string $email */
/** @var string $subject */
/** @var string $message */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau message de contact</title>
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

              <p style="margin:0 0 8px;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.2px;">
                Nouveau message de contact
              </p>
              <p style="margin:0 0 28px;font-size:14px;color:#93b8d8;line-height:1.6;">
                Sujet : <strong style="color:#ffffff;"><?= esc($subject) ?></strong>
              </p>

              <!-- Infos expéditeur -->
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;background-color:#1a2d40;border-radius:8px;padding:16px;">
                <tr>
                  <td style="padding:4px 0;">
                    <p style="margin:0;font-size:13px;color:#93b8d8;">Nom</p>
                    <p style="margin:2px 0 0;font-size:14px;color:#ffffff;font-weight:600;"><?= esc($name) ?></p>
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 0 4px;">
                    <p style="margin:0;font-size:13px;color:#93b8d8;">Email</p>
                    <a href="mailto:<?= esc($email) ?>" style="margin:2px 0 0;font-size:14px;color:#F2860E;font-weight:600;text-decoration:none;display:block;"><?= esc($email) ?></a>
                  </td>
                </tr>
              </table>

              <!-- Message -->
              <p style="margin:0 0 8px;font-size:13px;color:#93b8d8;text-transform:uppercase;letter-spacing:0.05em;">Message</p>
              <div style="background-color:#1a2d40;border-radius:8px;padding:16px;margin-bottom:28px;">
                <p style="margin:0;font-size:14px;color:#d0e4f5;line-height:1.7;white-space:pre-wrap;"><?= esc($message) ?></p>
              </div>

              <p style="margin:0;font-size:13px;color:#555;">
                Répondez directement à cet email pour contacter l'expéditeur.
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#111a26;border-radius:0 0 12px 12px;padding:24px 40px;border-top:1px solid #1a3a5c;">
              <p style="margin:0;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                Message reçu via le formulaire de contact de kenweturi.fr
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
