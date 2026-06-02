<?php
/** @var int    $reporterId */
/** @var int    $journeyId */
/** @var string $description */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau signalement - Kenweturi</title>
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
                                Nouveau signalement à traiter
                            </p>

                            <p style="margin:0 0 28px;font-size:15px;color:#93b8d8;line-height:1.6;">
                                Un nouveau signalement vient d'être soumis sur la plateforme <strong style="color:#ffffff;">Kenweturi</strong>. Merci de le traiter sous 48h depuis l'interface d'administration.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
                                <tr>
                                    <td style="padding:10px 16px;background-color:#1a3a5c;border-radius:8px 8px 0 0;border-bottom:1px solid #16222e;">
                                        <span style="font-size:13px;font-weight:700;color:#ffffff;">Signalé par (user_id)</span><br>
                                        <span style="font-size:15px;color:#93b8d8;"><?= esc($reporterId) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 16px;background-color:#1a3a5c;border-radius:0 0 8px 8px;">
                                        <span style="font-size:13px;font-weight:700;color:#ffffff;">Trajet signalé (journey_id)</span><br>
                                        <span style="font-size:15px;color:#93b8d8;"><?= esc($journeyId) ?></span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#ffffff;">
                                Description du signalement :
                            </p>
                            <p style="margin:0;font-size:15px;color:#93b8d8;line-height:1.6;background-color:#1a3a5c;padding:16px;border-radius:8px;">
                                <?= nl2br(esc($description)) ?>
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
