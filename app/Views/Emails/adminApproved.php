<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Compte validé !</title>
</head>
<body style="font-family: sans-serif; color: #1e293b; line-height: 1.5;">

    <h2 style="color: #10b981;">Bonne nouvelle, <?= esc($firstname) ?> !</h2>
    
    <p>Bonjour <?= esc($firstname) ?> <?= esc($lastname) ?>,</p>
    
    <p>Votre inscription sur la plateforme <strong>Kenweturi</strong> vient d'être validée par un administrateur.</p>
    
    <p>Vous pouvez dès à présent vous connecter à votre compte et profiter de tous nos services.</p>
    
    <p style="margin-top: 24px;">
        <a href="<?= base_url('login') ?>" 
           style="background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">
           Me connecter
        </a>
    </p>
    
    <hr style="border: 0; border-top: 1px border #e2e8f0; margin-top: 32px;">
    <p style="color: #64748b; text-align: center; font-size: 12px;">L'équipe Kenweturi</p>

</body>
</html>