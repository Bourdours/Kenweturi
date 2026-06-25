<?php

/** @var \Config\Site $site */ ?>
<?= view('partials/head', ['extraJs' => [base_url('js/contact.js')]]) ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Entreprise</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Nous contacter</h1>
    <p class="text-ink/40 text-sm">Une question, un problème ou une suggestion ? Écrivez-nous.</p>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-success/10 border border-success/20 rounded-2xl px-6 py-4 text-success text-sm">
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>

  <?php $errors = session()->getFlashdata('errors') ?? []; ?>

  <!-- Formulaire -->
  <form action="<?= site_url('contact') ?>" method="POST" class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-5">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

      <!-- Nom -->
      <div class="flex flex-col gap-1.5">
        <label for="name" class="text-ink/60 text-xs font-medium uppercase tracking-widest">Nom</label>
        <input
          type="text"
          id="name"
          name="name"
          value="<?= esc(old('name')) ?>"
          placeholder="Jean Dupont"
          class="bg-paper border <?= isset($errors['name']) ? 'border-red-400' : 'border-action/20' ?> rounded-xl px-4 py-2.5 text-ink text-sm placeholder:text-ink/30 focus:outline-none focus:border-action transition-colors">
        <?php if (isset($errors['name'])): ?>
          <p class="text-red-400 text-xs"><?= esc($errors['name']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div class="flex flex-col gap-1.5">
        <label for="email" class="text-ink/60 text-xs font-medium uppercase tracking-widest">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?= esc(old('email')) ?>"
          placeholder="jean@exemple.fr"
          class="bg-paper border <?= isset($errors['email']) ? 'border-red-400' : 'border-action/20' ?> rounded-xl px-4 py-2.5 text-ink text-sm placeholder:text-ink/30 focus:outline-none focus:border-action transition-colors">
        <?php if (isset($errors['email'])): ?>
          <p class="text-red-400 text-xs"><?= esc($errors['email']) ?></p>
        <?php endif; ?>
      </div>

    </div>

    <!-- Sujet -->
    <?php $selectedSubject = old('subject') ?: ''; ?>
    <div class="flex flex-col gap-1.5">
      <label class="text-ink/60 text-xs font-medium uppercase tracking-widest">Sujet</label>
      <div class="relative" id="subject-dropdown-wrapper">
        <input type="hidden" name="subject" id="subject-input" value="<?= esc($selectedSubject) ?>">
        <button type="button" id="subject-trigger"
          class="w-full text-left bg-paper border <?= isset($errors['subject']) ? 'border-red-400' : 'border-action/20' ?> rounded-xl px-4 py-2.5 text-sm flex items-center justify-between gap-2 focus:outline-none focus:border-action transition-colors">
          <span id="subject-label" class="<?= $selectedSubject ? 'text-ink' : 'text-ink/30' ?>">
            <?= $selectedSubject ? esc($selectedSubject) : 'Choisissez un sujet' ?>
          </span>
          <i class="fa-solid fa-chevron-down text-ink/30 text-xs transition-transform" id="subject-chevron"></i>
        </button>
        <ul class="autocomplete-dropdown" id="subject-list">
          <?php foreach (['Question générale', 'Problème avec un trajet', 'Signaler un utilisateur', "Suggestion d'amélioration", 'Autre'] as $option): ?>
            <li class="autocomplete-item <?= $selectedSubject === $option ? 'text-action font-semibold' : '' ?>" data-value="<?= esc($option) ?>">
              <?= esc($option) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php if (isset($errors['subject'])): ?>
        <p class="text-red-400 text-xs"><?= esc($errors['subject']) ?></p>
      <?php endif; ?>
    </div>

    <!-- Message -->
    <div class="flex flex-col gap-1.5">
      <label for="message" class="text-ink/60 text-xs font-medium uppercase tracking-widest">Message</label>
      <textarea
        id="message"
        name="message"
        rows="5"
        placeholder="Décrivez votre demande..."
        class="bg-paper border <?= isset($errors['message']) ? 'border-red-400' : 'border-action/20' ?> rounded-xl px-4 py-2.5 text-ink text-sm placeholder:text-ink/30 focus:outline-none focus:border-action transition-colors resize-none"><?= esc(old('message')) ?></textarea>
      <?php if (isset($errors['message'])): ?>
        <p class="text-red-400 text-xs"><?= esc($errors['message']) ?></p>
      <?php endif; ?>
    </div>

    <button type="submit" class="self-start bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-6 py-2.5">
      Envoyer le message
    </button>

  </form>

  <!-- Contact direct -->
  <div class="bg-surface rounded-2xl p-5 border border-action/10 flex items-center gap-4">
    <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
      <i class="fa-solid fa-envelope text-action text-sm"></i>
    </div>
    <div>
      <p class="text-ink text-sm font-semibold">Contact direct</p>
      <a href="mailto:<?= esc($site->contactEmail) ?>" class="text-action text-sm hover:underline"><?= esc($site->contactEmail) ?></a>
    </div>
  </div>

</div>


<?= view('partials/footer') ?>