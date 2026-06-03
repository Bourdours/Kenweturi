<?php

/** @var array $car */ ?>
<?= view('partials/head', ['extraJs' => [base_url('js/user.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 flex flex-col gap-6">

  <div class="flex items-center justify-between">
    <h1 class="text-ink text-2xl font-bold font-display">Modifier le véhicule</h1>
    <a href="<?= site_url('profile/edit') ?>" class="text-ink/50 hover:text-action text-sm flex items-center gap-1.5 transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i>Retour
    </a>
  </div>

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

  <form action="<?= site_url('car/' . $car['id'] . '/update') ?>" method="post"
    class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-4">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 my-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="vehicleBrand" class="text-ink/50 text-xs my-1 font-medium mb-1.5 block">Marque</label>
        <div class="relative w-full">
          <input type="text" id="vehicleBrand" name="brand" value="<?= esc($car['brand']) ?>" required
            autocomplete="off"
            class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
          <ul id="brandSuggestions" class="absolute left-0 top-full z-50 w-full bg-paper border border-action/15 rounded-b-lg shadow-lg max-h-48 overflow-y-auto hidden flex flex-col pointer-events-auto"></ul>
        </div>
      </div>
      <div>
        <label for="model" class="text-ink/50 text-xs my-1 font-medium mb-1.5 block">Modèle</label>
        <input type="text" id="model" name="model" value="<?= esc($car['model']) ?>" required
          class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
      </div>
    </div>

    <div class="grid grid-cols-1 my-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="color" class="text-ink/50 my-1 text-xs font-medium mb-1.5 block">Couleur</label>
        <input type="text" id="color" name="color" value="<?= esc($car['color']) ?>" required
          class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
      </div>
      <div>
        <label for="seats" class="text-ink/50 my-1 text-xs font-medium mb-1.5 block">Nombre de places</label>
        <input type="number" id="seats" name="seats" value="<?= esc($car['seats']) ?>" min="1" max="8" required
          class="w-full bg-paper border border-action/15 rounded-lg text-ink text-sm px-3 py-2.5 outline-none focus:border-action/50 transition-colors">
      </div>
    </div>

    <div class="flex items-center justify-end gap-3">
      <a href="<?= site_url('profile/edit') ?>"
        class="border border-action/20 hover:border-action/50 text-ink/60 hover:text-ink font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
        Annuler
      </a>
      <button type="submit"
        class="flex items-center gap-2 bg-action hover:bg-action-dark text-ink font-semibold font-display rounded-lg px-5 py-2.5 text-sm transition-colors cursor-pointer">
        <i class="fa-solid fa-check text-xs"></i>Enregistrer
      </button>
    </div>
  </form>

</div>

<?= view('partials/footer') ?>