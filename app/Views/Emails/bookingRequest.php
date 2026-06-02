<?php
/** @var string $firstname */
/** @var string $passengerFirstname */
/** @var string $passengerLastname */
/** @var string $cityStart */
/** @var string $cityEnd */
/** @var string $date */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de réservation</title>
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

                            <p style="margin:0 0 24px;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.2px;">
                                Nouvelle demande de réservation
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                                Bonjour <?= esc($firstname) ?>,
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                                <strong style="color:#ffffff;"><?= esc($passengerFirstname) ?> <?= esc($passengerLastname) ?></strong> souhaite rejoindre votre trajet.
                            </p>

                            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#ffffff;">
                                Trajet concerné :
                            </p>
                            <p style="margin:0 0 32px;font-size:15px;color:#93b8d8;line-height:1.6;background-color:#1a3a5c;padding:16px;border-radius:8px;">
                                <?= esc($cityStart) ?> → <?= esc($cityEnd) ?><br>
                                <?= esc($date) ?>
                            </p>

                            <!-- CTA -->
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom:8px;">
                                        <a href="<?= site_url('dashboard/bookings') ?>"
                                            style="display:inline-block;background-color:#D85A30;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.1px;">
                                            Gérer mes réservations
                                        </a>
                                    </td>
                                </tr>
                            </table>

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
