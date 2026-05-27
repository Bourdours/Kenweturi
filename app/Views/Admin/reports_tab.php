<?php

/** @var array $pendingUsers Liste des utilisateurs en attente de validation */
?>

<!-- Aucune inscription en attente -->
<?php if (empty($pendingUsers)): ?>

  <div class="flex flex-col items-center justify-center py-16 gap-3 text-center">
    <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center">
      <i class="fa-solid fa-user-check text-success text-xl"></i>
    </div>
    <p class="text-ink font-semibold font-display">Aucune inscription en attente</p>
    <p class="text-ink/40 text-sm">Tous les comptes utilisateurs sont à jour.</p>
  </div>

<?php else: ?>

  <!-- Tableau des inscriptions en attente -->
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm text-ink">
      <thead>
        <tr class="bg-paper border-b border-action/10 text-ink/50 font-display font-semibold text-xs uppercase tracking-wider">
          <th class="px-6 py-4">Utilisateur</th>
          <th class="px-6 py-4">Email</th>
          <th class="px-6 py-4">Ville (ID)</th>
          <th class="px-6 py-4">Date d'inscription</th>
          <th class="px-6 py-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-action/5">
        <?php foreach ($pendingUsers as $user): ?>
          <tr class="hover:bg-paper/40 transition-colors">
            <!-- Nom complet de l'utilisateur -->
            <td class="px-6 py-4 font-medium whitespace-nowrap">
              <?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>
            </td>

            <!-- Adresse email -->
            <td class="px-6 py-4 text-ink/70 whitespace-nowrap">
              <?= esc($user['email']) ?>
            </td>

            <!-- Identifiant de la ville -->
            <td class="px-6 py-4 text-ink/70 whitespace-nowrap">
              <span class="inline-flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-[10px] text-ink/30"></i>
                ID Ville : <?= esc($user['city_id']) ?>
              </span>
            </td>

            <!-- Date et heure d'inscription -->
            <td class="px-6 py-4 text-ink/40 whitespace-nowrap">
              <?php
              $date = new DateTime($user['registered_at'], new DateTimeZone('UTC'));
              $date->setTimezone(new DateTimeZone('Europe/Paris'));
              echo $date->format('d/m/Y à H:i');
              ?>
            </td>
            
            <!-- Boutons d'action : valider ou refuser l'inscription -->
            <td class="px-6 py-4 text-right whitespace-nowrap">
              <div class="inline-flex gap-2">

                <!-- Formulaire de validation -->
                <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST">
                  <?= csrf_field() ?>
                  <input type="hidden" name="actionAdmin" value="validate">
                  <button type="submit"
                    class="bg-success/10 hover:bg-success text-success hover:text-white border border-success/20 font-semibold text-xs rounded-lg px-3 py-1.5 transition-colors duration-150">
                    <i class="fa-solid fa-check mr-1"></i>Valider
                  </button>
                </form>

                <!-- Formulaire de rejet -->
                <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST">
                  <?= csrf_field() ?>
                  <input type="hidden" name="actionAdmin" value="reject">
                  <button type="submit"
                    class="bg-danger/10 hover:bg-danger text-danger hover:text-white border border-danger/20 font-semibold text-xs rounded-lg px-3 py-1.5 transition-colors duration-150">
                    <i class="fa-solid fa-xmark mr-1"></i>Refuser
                  </button>
                </form>

              </div>
            </td>

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>