<!DOCTYPE html>
<html lang="fr">

<head>
    <script>if(localStorage.getItem('theme')!=='light')document.documentElement.classList.add('dark');if(localStorage.getItem('carCursor')!=='on')document.documentElement.classList.add('car-off');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="<?= base_url('js/app.js') ?>" defer></script>
    <script src="<?= base_url('js/car-cursor.js') ?>" defer></script>
    <title><?= isset($title) ? $title . ', Kenweturi' : 'Kenweturi' ?></title>
    <link rel="stylesheet" href="<?= base_url('css/tailwind.css') ?>">
</head>