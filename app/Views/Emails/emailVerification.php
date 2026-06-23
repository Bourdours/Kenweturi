<?php
/** @var string $firstname */
/** @var string $verifyLink */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmez votre adresse email</title>
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
                                Bienvenue, <?= esc($firstname) ?> !
                            </p>

                            <p style="margin:0 0 20px;font-size:15px;color:#93b8d8;line-height:1.6;">
                                Merci pour votre inscription sur <strong style="color:#ffffff;">Kenweturi</strong>. Pour finaliser votre demande, veuillez d'abord confirmer votre adresse email en cliquant sur le bouton ci-dessous.
                            </p>

                            <p style="margin:0 0 32px;font-size:15px;color:#93b8d8;line-height:1.6;">
                                Ce lien est valable <strong style="color:#ffffff;">24 heures</strong>. Passé ce délai, vous devrez vous réinscrire.
                            </p>

                            <!-- CTA -->
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom:24px;">
                                        <a href="<?= $verifyLink ?>"
                                            style="display:inline-block;background-color:#D85A30;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.1px;">
                                            Confirmer mon adresse email
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#556678;line-height:1.6;">
                                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                                <span style="color:#93b8d8;word-break:break-all;"><?= $verifyLink ?></span>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#111a26;border-radius:0 0 12px 12px;padding:24px 40px;border-top:1px solid #1a3a5c;">
                            <p style="margin:0;font-size:12px;color:#555;line-height:1.6;text-align:center;">
                                Si vous n'êtes pas à l'origine de cette inscription, ignorez cet email.<br>
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
