<?php

/** @var array $allUsers Liste de tous les utilisateurs */
?>
<div class="flex flex-col gap-0">

    <!-- Barre de recherche + compteur -->
    <div class="px-6 py-4 border-b border-action/10 flex items-center gap-4">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-ink/30 text-sm"></i>
            <input
                type="text"
                id="searchUser"
                placeholder="Rechercher un utilisateur..."
                class="w-full pl-10 pr-4 py-2 text-sm bg-ink/5 border border-action/10 rounded-lg text-ink placeholder:text-ink/30 focus:outline-none focus:border-action/40" />
        </div>
        <span class="text-xs text-ink/40 shrink-0">
            <span id="userCount"><?= count($allUsers) - count(array_filter($allUsers, fn($u) => $u['role'] === 'superadmin')) ?></span> membre(s)
        </span>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="divide-y divide-action/10 overflow-y-auto max-h-[520px]" id="userList">
        <?php $i = 1;
        foreach ($allUsers as $user): ?>
            <?php if ($user['role'] === 'superadmin') continue; ?>
            <div class="user-row flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4"
                data-name="<?= strtolower(esc($user['firstname']) . ' ' . esc($user['lastname'])) ?>"
                data-email="<?= strtolower(esc($user['email'])) ?>">
                <div class="flex items-center gap-3">
                    <!-- Numéro -->
                    <span class="text-xs text-ink/30 font-mono w-5 shrink-0"><?= $i++ ?></span>
                    <div class="w-9 h-9 rounded-full bg-action/10 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user text-action text-sm"></i>
                    </div>
                    <div>
                        <p class="text-ink text-sm font-semibold">
                            <?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>
                        </p>
                        <p class="text-ink/40 text-xs"><?= esc($user['email']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 min-w-[220px] justify-end">
                    <form action="<?= site_url('admin/users/' . $user['id'] . '/role') ?>" method="post" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <?php $role = $user['role'] ?? 'user'; ?>
                        <?php if ($role === 'admin'): ?>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-action/10 text-action">
                                <i class="fa-solid fa-shield text-xs mr-1"></i>Admin
                            </span>
                            <button type="submit" name="role" value="user"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-danger/10 text-danger hover:bg-danger/20 transition-colors">
                                <i class="fa-solid fa-arrow-down text-xs mr-1"></i>Rétrograder
                            </button>
                        <?php else: ?>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-ink/5 text-ink/40">
                                <i class="fa-solid fa-user text-xs mr-1"></i>Utilisateur
                            </span>
                            <button type="submit" name="role" value="admin"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-action/10 text-action hover:bg-action/20 transition-colors">
                                <i class="fa-solid fa-arrow-up text-xs mr-1"></i>Promouvoir
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Bouton suppression -->
                    <form action="<?= site_url('admin/users/' . $user['id'] . '/delete') ?>" class="delete-form" method="post">
                        <?= csrf_field() ?>
                        <button type="submit"
                            data-id="<?= $user['id'] ?>"
                            data-name="<?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>"
                            class="delete-btn text-xs font-semibold px-3 py-1.5 rounded-lg bg-danger/10 text-danger hover:bg-danger/20 transition-colors">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Message si aucun résultat -->
        <div id="noResults" class="hidden px-6 py-8 text-center text-ink/30 text-sm">
            <i class="fa-solid fa-user-slash mb-2 text-xl block"></i>
            Aucun utilisateur trouvé.
        </div>
    </div>
</div>

<!-- Modal confirmation suppression -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50">
    <div class="bg-surface rounded-2xl border border-action/10 p-6 max-w-sm w-full mx-4 flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-danger/10 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-trash text-danger"></i>
            </div>
            <div>
                <p class="text-ink font-semibold text-sm">Supprimer le compte</p>
                <p class="text-ink/40 text-xs" id="deleteModalName"></p>
            </div>
        </div>
        <p class="text-ink/60 text-sm">Cette action est irréversible. Le compte sera désactivé et tous ses trajets annulés.</p>
        <div class="flex gap-2 justify-end">
            <button id="deleteCancelBtn"
                class="text-xs font-semibold px-4 py-2 rounded-lg bg-ink/5 text-ink/60 hover:bg-ink/10 transition-colors">
                Annuler
            </button>
            <button id="deleteConfirmBtn"
                class="text-xs font-semibold px-4 py-2 rounded-lg bg-danger/10 text-danger hover:bg-danger/20 transition-colors">
                <i class="fa-solid fa-trash text-xs mr-1"></i>Supprimer
            </button>
        </div>
    </div>
</div>
<script src="<?= base_url('js/admin.js') ?>"></script>