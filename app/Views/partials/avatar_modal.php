<?php

/**
 * @var string $avatarSrc   URL complète de l'avatar (ou chaîne vide)
 * @var string $firstname   Prénom de l'utilisateur
 * @var string $initials    Initiales de repli
 */
?>
<!-- Modal avatar -->
<div id="avatarModal" class="hidden fixed inset-0 bg-black/80 z-[9999] items-center justify-center cursor-zoom-out">
  <?php if (!empty($avatarSrc)): ?>
    <img src="<?= esc($avatarSrc) ?>" alt="Avatar de <?= esc($firstname) ?>" class="max-w-[90vw] max-h-[90vh] rounded-lg object-contain shadow-[0_0_40px_rgba(0,0,0,0.5)]"
      onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
    <div class="hidden w-64 h-64 rounded-full bg-action-dark text-ink font-bold text-[5rem] items-center justify-center shadow-[0_0_40px_rgba(0,0,0,0.5)]">
      <?= esc($initials) ?>
    </div>
  <?php else: ?>
    <div class="flex w-64 h-64 rounded-full bg-action-dark text-ink font-bold text-[5rem] items-center justify-center shadow-[0_0_40px_rgba(0,0,0,0.5)]">
      <?= esc($initials) ?>
    </div>
  <?php endif; ?>
</div>