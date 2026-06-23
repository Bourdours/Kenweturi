<?php
/** @var array|null $favorite Adresse favorite actuelle (location), ou null si non définie */
?>
<div class="p-6 flex flex-col gap-6">

  <div>
    <h2 class="text-ink text-base font-semibold font-display mb-1">Adresse de l'établissement</h2>
    <p class="text-ink/40 text-sm">Cette adresse est utilisée comme repère pour les trajets liés à l'établissement.</p>
  </div>

  <?php if ($favorite): ?>
    <div class="bg-paper rounded-xl border border-action/10 px-4 py-3 flex items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-location-dot text-action text-sm shrink-0"></i>
        <span class="text-ink text-sm"><?= esc($favorite['address']) ?></span>
      </div>
      <form action="<?= site_url('admin/settings/clear') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="text-ink/40 hover:text-action text-xs transition-colors cursor-pointer">
          <i class="fa-solid fa-xmark"></i> Retirer
        </button>
      </form>
    </div>
  <?php else: ?>
    <div class="bg-paper rounded-xl border border-action/10 px-4 py-3 text-ink/40 text-sm">
      Aucune adresse favorite définie pour le moment.
    </div>
  <?php endif; ?>

  <?php if (session()->has('errors')): ?>
    <div class="bg-action/10 border border-action/30 rounded-xl p-4 flex gap-3 items-start">
      <i class="fa-solid fa-triangle-exclamation text-action text-base shrink-0 mt-0.5"></i>
      <div>
        <p class="text-ink text-sm font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
        <ul class="list-disc pl-4 flex flex-col gap-1">
          <?php foreach (session('errors') as $error): ?>
            <li class="text-action text-xs"><?= esc((string) $error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>

  <form action="<?= site_url('admin/settings') ?>" method="post" class="flex flex-col gap-3">
    <?= csrf_field() ?>
    <div class="flex flex-col gap-3">
      <label for="favoriteAddress" class="text-ink/50 text-xs font-medium block">Nouvelle adresse</label>
      <div>
        <input id="favoriteAddress" class="address w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 placeholder:text-ink/30 transition-colors"
          name="favoriteAddress" type="text" placeholder="Adresse de l'établissement..." value="<?= esc(old('favoriteAddress')) ?>">
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">Enregistrer</button>
    </div>
  </form>

</div>