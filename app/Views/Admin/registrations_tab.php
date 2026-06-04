<?php
/** @var array $pendingUsers Liste des utilisateurs en attente de validation */
?>

<!-- Aucune inscription en attente -->
<?php if (empty($pendingUsers)): ?>

    <div class="flex flex-col items-center justify-center py-16 gap-3 text-center">
        <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center">
            <i class="fa-solid fa-circle-check text-success text-xl"></i>
        </div>
        <p class="text-ink font-semibold font-display">Aucune inscription en attente</p>
        <p class="text-ink/40 text-sm">Tous les nouveaux utilisateurs ont été vérifiés.</p>
    </div>

<?php else: ?>

    <!-- Vue mobile : cartes empilées -->
    <div class="flex flex-col divide-y divide-action/10 tab:hidden">
        <?php foreach ($pendingUsers as $user): ?>
            <div class="px-5 py-4 flex flex-col gap-3">

                <!-- Nom complet de l'utilisateur -->
                <div class="flex flex-col gap-0.5">
                    <span class="font-semibold text-ink text-sm font-display">
                        <?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>
                    </span>
                    <!-- Adresse email -->
                    <span class="text-ink/60 text-xs"><?= esc($user['email']) ?></span>
                </div>

                <!-- Ville ou code postal + Date et heure d'inscription -->
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink/50">
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-ink/30"></i>
                        <?= esc($user['city_name'] ?? 'Non renseignée') ?>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-clock text-ink/30"></i>
                        <?php
                        $date = new DateTime($user['registered_at'], new DateTimeZone('UTC'));
                        $date->setTimezone(new DateTimeZone('Europe/Paris'));
                        echo $date->format('d/m/Y à H:i');
                        ?>
                    </span>
                </div>

                <!-- Empêche l'admin de s'auto-valider / s'auto-refuser -->
                <?php if ((int)$user['id'] !== (int)session()->get('user_id')): ?>
                    <div class="flex gap-2">

                        <!-- Formulaire de validation de l'inscription -->
                        <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST" class="flex-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="actionAdmin" value="validate">
                            <button type="submit"
                                class="w-full bg-success/10 hover:bg-success text-success hover:text-white font-medium text-xs rounded-lg px-3 py-2 transition-colors">
                                <i class="fa-solid fa-check mr-1"></i> Valider
                            </button>
                        </form>

                        <!-- Formulaire de rejet de l'inscription -->
                        <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST" class="flex-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="actionAdmin" value="reject">
                            <button type="submit"
                                class="w-full bg-danger/10 hover:bg-danger text-danger hover:text-white font-medium text-xs rounded-lg px-3 py-2 transition-colors">
                                <i class="fa-solid fa-xmark mr-1"></i> Refuser
                            </button>
                        </form>

                    </div>
                <?php else: ?>
                    <!-- L'admin connecté ne peut pas agir sur son propre compte -->
                    <span class="text-ink/30 text-xs italic">Vous</span>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tableau des inscriptions en attente -->
    <div class="hidden tab:block overflow-x-auto scrollbar-hover">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-action/10 text-ink/50 font-semibold">
                    <th class="px-4 py-3 font-display">Utilisateur</th>
                    <th class="px-4 py-3 font-display">Email</th>
                    <th class="px-4 py-3 font-display">Ville</th>
                    <th class="px-4 py-3 font-display">Date</th>
                    <th class="px-3 py-3 font-display">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-action/10">
                <?php foreach ($pendingUsers as $user): ?>
                    <tr class="hover:bg-paper/30 transition-colors">
                        <!-- Nom complet de l'utilisateur -->
                        <td class="px-4 py-3 font-medium text-ink whitespace-nowrap">
                            <?php $fn = $user['firstname']; $ln = $user['lastname']; $fnTrunc = mb_strlen($fn) > 8; $lnTrunc = mb_strlen($ln) > 10; ?>
                            <span <?= ($fnTrunc || $lnTrunc) ? 'title="' . esc($fn) . ' ' . esc($ln) . '"' : '' ?>><?= $fnTrunc ? esc(mb_strtoupper(mb_substr($fn, 0, 1))) . '.' : esc($fn) ?> <?= $lnTrunc ? esc(mb_substr($ln, 0, 10)) . '…' : esc($ln) ?></span>
                        </td>
                        <!-- Adresse email -->
                        <td class="px-4 py-3 text-ink/70 whitespace-nowrap">
                            <span <?= mb_strlen($user['email']) > 28 ? 'title="' . esc($user['email']) . '"' : '' ?>><?= esc(mb_strlen($user['email']) > 28 ? mb_substr($user['email'], 0, 28) . '…' : $user['email']) ?></span>
                        </td>
                        <!-- Ville ou code postal -->
                        <td class="px-4 py-3 text-ink/70 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-ink/30 text-xs"></i>
                                <?php $city = $user['city_name'] ?? 'Non renseignée'; ?>
                        <span <?= mb_strlen($city) > 9 ? 'title="' . esc($city) . '"' : '' ?>><?= esc(mb_strlen($city) > 9 ? mb_substr($city, 0, 9) . '…' : $city) ?></span>
                            </span>
                        </td>

                        <!-- Date et heure d'inscription -->
                        <td class="px-4 py-3 text-ink/40 whitespace-nowrap">
                            <?php
                            $date = new DateTime($user['registered_at'], new DateTimeZone('UTC'));
                            $date->setTimezone(new DateTimeZone('Europe/Paris'));
                            echo $date->format('d/m/y H:i');
                            ?>
                        </td>

                        <td class="px-3 py-3 whitespace-nowrap">
                            <!-- Empêche l'admin de s'auto-valider / s'auto-refuser -->
                            <?php if ((int)$user['id'] !== (int)session()->get('user_id')): ?>

                                <div class="inline-flex items-center gap-2">

                                    <!-- Formulaire de validation de l'inscription -->
                                    <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="actionAdmin" value="validate">
                                        <button type="submit"
                                            class="bg-success/10 hover:bg-success text-success hover:text-white font-medium text-xs rounded-lg px-3 py-1.5 transition-colors">
                                            <i class="fa-solid fa-check mr-1"></i> Valider
                                        </button>
                                    </form>

                                    <!-- Formulaire de rejet de l'inscription -->
                                    <form action="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="actionAdmin" value="reject">
                                        <button type="submit"
                                            class="bg-danger/10 hover:bg-danger text-danger hover:text-white font-medium text-xs rounded-lg px-3 py-1.5 transition-colors">
                                            <i class="fa-solid fa-xmark mr-1"></i> Refuser
                                        </button>
                                    </form>

                                </div>

                            <?php else: ?>
                                <!-- L'admin connecté ne peut pas agir sur son propre compte -->
                                <span class="text-ink/30 text-xs italic">Vous</span>
                            <?php endif; ?>

                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>