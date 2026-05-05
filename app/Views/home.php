<?= view('partials/head') ?>
<?= view('partials/header') ?>

    <!-- Message de succès après inscription (test) -->
    <?php if (session()->getFlashdata('success')): ?>
        <div>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

<main>
    <h1>Accueil</h1>
</main>

<?= view('partials/footer') ?>