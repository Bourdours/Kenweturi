<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Entreprise</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Blog</h1>
    <p class="text-ink/40 text-sm">Conseils, actualités et bonnes pratiques autour du covoiturage.</p>
  </div>

  <!-- Placeholder -->
  <div class="bg-surface rounded-2xl p-12 border border-action/10 flex flex-col items-center gap-4 text-center">
    <div class="w-14 h-14 rounded-2xl bg-action/10 flex items-center justify-center">
      <i class="fa-solid fa-pen-nib text-action text-xl"></i>
    </div>
    <div>
      <p class="text-ink font-semibold text-base">Bientôt disponible</p>
      <p class="text-ink/50 text-sm leading-relaxed mt-1 max-w-xs">
        Nous préparons des articles sur le covoiturage, l'écologie et les bons plans pour vos trajets du quotidien.
      </p>
    </div>
    <a href="<?= site_url('contact') ?>" class="mt-2 text-action text-sm font-semibold hover:underline">
      Être notifié au lancement →
    </a>
  </div>

</div>

<?= view('partials/footer') ?>
