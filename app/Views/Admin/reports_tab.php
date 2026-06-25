<?php

/** @var array $reports       Liste des signalements ouverts */
/** @var array $closedReports Liste des signalements clôturés */
?>

<!-- Aucun signalement en attente -->
<?php if (empty($reports)): ?>

  <div class="flex flex-col items-center justify-center py-16 gap-3 text-center">
    <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center">
      <i class="fa-solid fa-circle-check text-success text-xl"></i>
    </div>
    <p class="text-ink font-semibold font-display">Aucun signalement en attente</p>
    <p class="text-ink/40 text-sm">Tous les signalements ont été traités.</p>
  </div>

<?php else: ?>

  <!-- Vue mobile : cartes empilées -->
  <div class="flex flex-col divide-y divide-action/10 md:hidden">
    <?php foreach ($reports as $report): ?>
      <div class="px-5 py-4 flex flex-col gap-3 cursor-pointer"
        onclick="window.location='<?= site_url('admin/reports/' . $report['id']) ?>'">

        <!-- Titre et date du signalement -->
        <div class="flex flex-col gap-0.5">
          <span class="font-semibold text-ink text-sm font-display"><?= esc($report['title']) ?></span>
          <span class="text-ink/40 text-xs">
            <?php
            $date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Europe/Paris'));
            echo $date->format('d/m/Y à H:i');
            ?>
          </span>
        </div>

        <!-- Parties concernées -->
        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink/50">
          <span class="inline-flex items-center gap-1">
            <i class="fa-solid fa-user text-ink/30"></i>
            Signalé par : <?= esc($report['reporter_firstname']) ?> <?= esc($report['reporter_lastname']) ?>
          </span>
          <span class="inline-flex items-center gap-1">
            <i class="fa-solid fa-user-xmark text-ink/30"></i>
            Signalé : <?= esc($report['reported_firstname']) ?> <?= esc($report['reported_lastname']) ?>
          </span>
          <?php if (!empty($report['journey_date'])): ?>
            <span class="inline-flex items-center gap-1">
              <i class="fa-solid fa-calendar text-ink/30"></i>
              Trajet : <?= (new DateTime($report['journey_date']))->format('d/m/Y') ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Formulaire d'action -->
        <form action="<?= site_url('admin/reports/' . $report['id'] . '/resolve') ?>" method="POST"
          class="flex flex-col gap-2" onclick="event.stopPropagation()">
          <?= csrf_field() ?>
          <textarea name="commentAdmin" rows="2" placeholder="Commentaire obligatoire…"
            class="w-full rounded-lg border border-action/20 bg-paper px-3 py-2 text-xs text-ink placeholder:text-ink/30 focus:outline-none focus:border-action/50 resize-none"></textarea>
          <div class="flex gap-2">
            <button type="submit" name="actionAdmin" value="warn"
              class="flex-1 bg-action/10 hover:bg-action text-action hover:text-ink font-medium text-xs rounded-lg px-3 py-2 transition-colors">
              <i class="fa-solid fa-triangle-exclamation mr-1"></i> Avertir
            </button>
            <button type="submit" name="actionAdmin" value="ban"
              class="flex-1 bg-danger/10 hover:bg-danger text-danger hover:text-ink font-medium text-xs rounded-lg px-3 py-2 transition-colors">
              <i class="fa-solid fa-ban mr-1"></i> Bannir
            </button>
            <button type="submit" name="actionAdmin" value="close"
              class="flex-1 bg-ink/5 hover:bg-ink/10 text-ink/50 hover:text-ink font-medium text-xs rounded-lg px-3 py-2 transition-colors">
              <i class="fa-solid fa-xmark mr-1"></i> Clôturer
            </button>
          </div>
        </form>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- Vue desktop : tableau -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm">
      <thead>
        <tr class="border-b border-action/10 text-ink/50 font-semibold">
          <th class="px-6 py-4 font-display">Signalement</th>
          <th class="px-6 py-4 font-display">Signalé par</th>
          <th class="px-6 py-4 font-display">Utilisateur signalé</th>
          <th class="px-6 py-4 font-display">Date trajet</th>
          <th class="px-6 py-4 font-display">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-action/10">
        <?php foreach ($reports as $report): ?>
          <tr class="hover:bg-paper/30 transition-colors cursor-pointer"
            onclick="window.location='<?= site_url('admin/reports/' . $report['id']) ?>'">

            <!-- Titre et date du signalement -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="font-medium text-ink"><?= esc($report['title']) ?></span>
              <span class="block text-ink/40 text-xs">
                <?php
                $date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
                $date->setTimezone(new DateTimeZone('Europe/Paris'));
                echo $date->format('d/m/Y à H:i');
                ?>
              </span>
            </td>

            <!-- Auteur du signalement -->
            <td class="px-6 py-4 text-ink/70 whitespace-nowrap">
              <?= esc($report['reporter_firstname']) ?> <?= esc($report['reporter_lastname']) ?>
            </td>

            <!-- Utilisateur signalé -->
            <td class="px-6 py-4 text-ink/70 whitespace-nowrap">
              <?= esc($report['reported_firstname']) ?> <?= esc($report['reported_lastname']) ?>
            </td>

            <!-- Date du trajet concerné -->
            <td class="px-6 py-4 text-ink/40 whitespace-nowrap">
              <?= !empty($report['journey_date']) ? (new DateTime($report['journey_date']))->format('d/m/Y') : '—' ?>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4" onclick="event.stopPropagation()">
              <form action="<?= site_url('admin/reports/' . $report['id'] . '/resolve') ?>" method="POST" class="flex flex-col gap-2">
                <?= csrf_field() ?>
                <textarea name="commentAdmin" rows="1" placeholder="Commentaire…"
                  class="w-full rounded-lg border border-action/20 bg-paper px-3 py-1.5 text-xs text-ink placeholder:text-ink/30 focus:outline-none focus:border-action/50 resize-none"></textarea>
                <div class="flex gap-1.5">
                  <button type="submit" name="actionAdmin" value="warn"
                    class="bg-action/10 hover:bg-action text-action hover:text-ink font-medium text-xs rounded-lg px-2.5 py-1.5 transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Avertir
                  </button>
                  <button type="submit" name="actionAdmin" value="ban"
                    class="bg-danger/10 hover:bg-danger text-danger hover:text-ink font-medium text-xs rounded-lg px-2.5 py-1.5 transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-ban mr-1"></i> Bannir
                  </button>
                  <button type="submit" name="actionAdmin" value="close"
                    class="bg-ink/5 hover:bg-ink/10 text-ink/50 hover:text-ink font-medium text-xs rounded-lg px-2.5 py-1.5 transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-xmark mr-1"></i> Clôturer
                  </button>
                </div>
              </form>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- Section signalements clôturés -->
<?php if (!empty($closedReports)): ?>
  <details class="group mt-4 border-t border-action/10">
    <summary class="flex items-center justify-between px-5 md:px-6 py-4 cursor-pointer list-none select-none text-sm text-ink/50 hover:text-ink transition-colors">
      <span class="font-semibold font-display flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-success"></i>
        Signalements clôturés
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-ink/10 text-ink/60 text-xs font-bold"><?= count($closedReports) ?></span>
      </span>
      <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 group-open:rotate-180"></i>
    </summary>

    <!-- Vue mobile : cartes empilées -->
    <div class="flex flex-col divide-y divide-action/10 md:hidden">
      <?php foreach ($closedReports as $report): ?>
        <div class="px-5 py-4 flex flex-col gap-2 cursor-pointer opacity-70 hover:opacity-100 transition-opacity"
          onclick="window.location='<?= site_url('admin/reports/' . $report['id']) ?>'">

          <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-ink text-sm font-display"><?= esc($report['title']) ?></span>
            <span class="text-ink/40 text-xs">
              <?php
              $date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
              $date->setTimezone(new DateTimeZone('Europe/Paris'));
              echo $date->format('d/m/Y à H:i');
              ?>
            </span>
          </div>

          <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink/50">
            <span class="inline-flex items-center gap-1">
              <i class="fa-solid fa-user text-ink/30"></i>
              Signalé par : <?= esc($report['reporter_firstname']) ?> <?= esc($report['reporter_lastname']) ?>
            </span>
            <span class="inline-flex items-center gap-1">
              <i class="fa-solid fa-user-xmark text-ink/30"></i>
              Signalé : <?= esc($report['reported_firstname']) ?> <?= esc($report['reported_lastname']) ?>
            </span>
          </div>

          <?php if (!empty($report['admin_comment'])): ?>
            <p class="text-xs text-ink/40 italic truncate">
              <i class="fa-solid fa-comment-dots mr-1"></i><?= esc($report['admin_comment']) ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($report['resolved_at'])): ?>
            <span class="text-xs text-ink/30">
              Clôturé le <?= (new DateTime($report['resolved_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i') ?>
            </span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Vue desktop : tableau -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-action/10 text-ink/40 font-semibold">
            <th class="px-6 py-3 font-display">Signalement</th>
            <th class="px-6 py-3 font-display">Signalé par</th>
            <th class="px-6 py-3 font-display">Utilisateur signalé</th>
            <th class="px-6 py-3 font-display">Clôturé le</th>
            <th class="px-6 py-3 font-display">Commentaire</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-action/10">
          <?php foreach ($closedReports as $report): ?>
            <tr class="hover:bg-paper/30 transition-colors cursor-pointer opacity-60 hover:opacity-100"
              onclick="window.location='<?= site_url('admin/reports/' . $report['id']) ?>'">

              <td class="px-6 py-3 whitespace-nowrap">
                <span class="font-medium text-ink"><?= esc($report['title']) ?></span>
                <span class="block text-ink/40 text-xs">
                  <?php
                  $date = new DateTime($report['created_at'], new DateTimeZone('UTC'));
                  $date->setTimezone(new DateTimeZone('Europe/Paris'));
                  echo $date->format('d/m/Y à H:i');
                  ?>
                </span>
              </td>

              <td class="px-6 py-3 text-ink/70 whitespace-nowrap">
                <?= esc($report['reporter_firstname']) ?> <?= esc($report['reporter_lastname']) ?>
              </td>

              <td class="px-6 py-3 text-ink/70 whitespace-nowrap">
                <?= esc($report['reported_firstname']) ?> <?= esc($report['reported_lastname']) ?>
              </td>

              <td class="px-6 py-3 text-ink/40 whitespace-nowrap text-xs">
                <?php if (!empty($report['resolved_at'])): ?>
                  <?= (new DateTime($report['resolved_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i') ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>

              <td class="px-6 py-3 text-ink/40 text-xs max-w-xs truncate">
                <?= !empty($report['admin_comment']) ? esc($report['admin_comment']) : '—' ?>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </details>
<?php endif; ?>