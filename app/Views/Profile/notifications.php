<?php

/** @var array $prefs        [slug => bool]  */
/** @var array $prefLabels   [slug => label] */
?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <div class="flex items-center justify-between">
    <h1 class="text-ink text-2xl font-bold font-display">Préférences de notifications</h1>
    <a href="<?= site_url('profile') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i>Retour
    </a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border border-success/20 rounded-xl px-5 py-3 text-success text-sm flex items-center gap-2">
      <i class="fa-solid fa-circle-check shrink-0"></i>
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>

  <p class="text-ink/50 text-sm">
    Choisissez les emails que vous souhaitez recevoir. Les emails de sécurité (mot de passe, connexion, compte) sont toujours envoyés.
  </p>

  <form action="<?= site_url('profile/notifications') ?>" method="post">
    <?= csrf_field() ?>

    <div class="bg-surface rounded-2xl border border-action/10 divide-y divide-action/10">

      <?php foreach ($prefLabels as $slug => $label): ?>
        <label class="flex items-start gap-4 p-5 cursor-pointer hover:bg-action/5 transition-colors">
          <div class="pt-0.5 shrink-0">
            <input
              type="checkbox"
              name="prefs[]"
              value="<?= esc($slug) ?>"
              <?= ($prefs[$slug] ?? true) ? 'checked' : '' ?>
              class="w-4 h-4 rounded accent-action cursor-pointer"
            >
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-ink text-sm font-medium leading-snug"><?= esc($label) ?></p>
          </div>
        </label>
      <?php endforeach; ?>

    </div>

    <div class="flex justify-end gap-3 mt-4">
      <button type="button" id="checkAll" class="text-sm text-ink/50 hover:text-action transition-colors">Tout activer</button>
      <span class="text-ink/20">·</span>
      <button type="button" id="uncheckAll" class="text-sm text-ink/50 hover:text-action transition-colors">Tout désactiver</button>
      <button type="submit"
        class="ml-4 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2 text-sm transition-colors">
        Enregistrer
      </button>
    </div>
  </form>

</div>

<script>
  document.getElementById('checkAll').addEventListener('click', () => {
    document.querySelectorAll('input[name="prefs[]"]').forEach(cb => cb.checked = true);
  });
  document.getElementById('uncheckAll').addEventListener('click', () => {
    document.querySelectorAll('input[name="prefs[]"]').forEach(cb => cb.checked = false);
  });
</script>

<?= view('partials/footer') ?>
