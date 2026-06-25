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
                            <button type="button"
                                class="accept-btn bg-success/10 hover:bg-success text-success hover:text-white font-medium text-xs rounded-lg px-3 py-1.5 transition-colors"
                                data-id="<?= $user['id'] ?>"
                                data-name="<?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>">
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
                    <th class="px-3 py-3 font-display">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-action/10">
                <?php foreach ($pendingUsers as $user): ?>
                    <tr class="hover:bg-paper/30 transition-colors">
                        <!-- Nom complet de l'utilisateur -->
                        <td class="px-4 py-3 font-medium text-ink whitespace-nowrap">
                            <?php $fn = $user['firstname'];
                            $ln = $user['lastname'];
                            $fnTrunc = mb_strlen($fn) > 8;
                            $lnTrunc = mb_strlen($ln) > 10; ?>
                            <span <?= ($fnTrunc || $lnTrunc) ? 'title="' . esc($fn) . ' ' . esc($ln) . '"' : '' ?>><?= $fnTrunc ? esc(mb_strtoupper(mb_substr($fn, 0, 1))) . '.' : esc($fn) ?> <?= $lnTrunc ? esc(mb_substr($ln, 0, 10)) . '…' : esc($ln) ?></span>
                        </td>
                        <!-- Adresse email -->
                        <td class="px-4 py-3 text-ink/70 whitespace-nowrap">
                            <span <?= mb_strlen($user['email']) > 28 ? 'title="' . esc($user['email']) . '"' : '' ?>><?= esc(mb_strlen($user['email']) > 28 ? mb_substr($user['email'], 0, 28) . '…' : $user['email']) ?></span>
                        </td>

                        <td class="px-3 py-3 whitespace-nowrap">
                            <!-- Empêche l'admin de s'auto-valider / s'auto-refuser -->
                            <?php if ((int)$user['id'] !== (int)session()->get('user_id')): ?>

                                <div class="inline-flex items-center gap-2">

                                    <!-- Formulaire de validation de l'inscription -->
                                    <button type="button"
                                        class="accept-btn bg-success/10 hover:bg-success text-success hover:text-white font-medium text-xs rounded-lg px-3 py-1.5 transition-colors"
                                        data-id="<?= $user['id'] ?>"
                                        data-name="<?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>"
                                        data-url="<?= site_url('admin/users/' . $user['id'] . '/validate') ?>">
                                        <i class="fa-solid fa-check mr-1"></i> Valider
                                    </button>


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

<!-- ================== Modal choix du rôle ================== -->
<div id="roleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-surface rounded-2xl border border-action/10 p-6 max-w-sm w-full mx-4 flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-check text-action"></i>
            </div>
            <div>
                <p class="text-ink font-semibold text-sm">Valider le compte</p>
                <p class="text-ink/40 text-xs" id="roleModalName"></p>
            </div>
        </div>
        <p class="text-ink/60 text-sm">Quel est le statut de cet utilisateur ?</p>
        <form id="roleForm" method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="actionAdmin" value="validate">
            <input type="hidden" name="is_student" id="roleValue">
            <div class="flex gap-2 justify-end">
                <button type="button" id="roleCancelBtn"
                    class="text-xs font-semibold px-4 py-2 rounded-lg bg-ink/5 text-ink/60 hover:bg-ink/10 transition-colors">
                    Annuler
                </button>
                <button type="button" data-role="0"
                    class="role-choice-btn text-xs font-semibold px-4 py-2 rounded-lg bg-ink/10 text-ink hover:bg-ink/20 transition-colors">
                    <i class="fa-solid fa-chalkboard-teacher text-xs mr-1"></i>Formateur
                </button>
                <button type="button" data-role="1"
                    class="role-choice-btn text-xs font-semibold px-4 py-2 rounded-lg bg-action/10 text-action hover:bg-action/20 transition-colors">
                    <i class="fa-solid fa-graduation-cap text-xs mr-1"></i>Étudiant
                </button>
            </div>
        </form>
    </div>
</div>
<script src="<?= base_url('js/adminPending.js') ?>"></script>