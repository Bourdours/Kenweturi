<?php

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */
$pager->setSurroundCount(2);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>" class="flex justify-center mt-8">
    <ul class="flex items-center gap-1">

        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>"
                   aria-label="<?= lang('Pager.first') ?>"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-ink/50 hover:bg-action/10 hover:text-action transition-colors text-sm">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPrevious() ?>"
                   aria-label="<?= lang('Pager.previous') ?>"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-ink/50 hover:bg-action/10 hover:text-action transition-colors text-sm">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li>
                <a href="<?= $link['uri'] ?>"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-sm font-medium transition-colors
                          <?= $link['active']
                              ? 'bg-action text-ink font-semibold shadow-sm'
                              : 'text-ink/60 hover:bg-action/10 hover:text-action' ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>"
                   aria-label="<?= lang('Pager.next') ?>"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-ink/50 hover:bg-action/10 hover:text-action transition-colors text-sm">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>"
                   aria-label="<?= lang('Pager.last') ?>"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-ink/50 hover:bg-action/10 hover:text-action transition-colors text-sm">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            </li>
        <?php endif ?>

    </ul>
</nav>
