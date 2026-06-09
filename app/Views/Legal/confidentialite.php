<?php /** @var \Config\Site $site */ ?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Légal</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Politique de Confidentialité</h1>
    <p class="text-ink/40 text-sm">Dernière mise à jour : <?= date('d/m/Y') ?></p>
  </div>

  <!-- Introduction -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10">
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi s'engage à protéger vos données personnelles conformément au
      <strong class="text-ink">Règlement Général sur la Protection des Données (RGPD)</strong> et à la loi
      Informatique et Libertés. Cette politique décrit les données que nous collectons, la manière dont nous
      les utilisons et les droits dont vous disposez.
    </p>
  </div>

  <!-- 1. Responsable du traitement -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-building text-action text-sm"></i>1. Responsable du traitement
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Le responsable du traitement de vos données est <strong class="text-ink">Kenweturi</strong>,
      joignable à l'adresse <a href="mailto:<?= esc($site->contactEmail) ?>" class="text-action hover:underline"><?= esc($site->contactEmail) ?></a>.
    </p>
  </div>

  <!-- 2. Données collectées -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-database text-action text-sm"></i>2. Données collectées
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed mb-2">Nous collectons les données suivantes :</p>

    <div class="flex flex-col gap-3">
      <div class="bg-paper rounded-xl p-4 border border-action/10">
        <p class="text-ink text-sm font-semibold mb-1.5">À l'inscription</p>
        <ul class="flex flex-col gap-1.5 text-ink/70 text-sm">
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Nom, prénom</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Adresse e-mail</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Mot de passe (haché, jamais stocké en clair)</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Statut étudiant (optionnel)</li>
        </ul>
      </div>

      <div class="bg-paper rounded-xl p-4 border border-action/10">
        <p class="text-ink text-sm font-semibold mb-1.5">Via l'utilisation du service</p>
        <ul class="flex flex-col gap-1.5 text-ink/70 text-sm">
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Photo de profil (optionnelle)</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Ville de résidence (optionnelle)</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Biographie (optionnelle)</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Trajets publiés (origines, destinations, horaires)</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Historique des trajets effectués ou réservés</li>
        </ul>
      </div>

      <div class="bg-paper rounded-xl p-4 border border-action/10">
        <p class="text-ink text-sm font-semibold mb-1.5">Automatiquement</p>
        <ul class="flex flex-col gap-1.5 text-ink/70 text-sm">
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Adresse IP</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Type de navigateur et système d'exploitation</li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Données de session (cookie de session, préférences de thème)</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- 3. Finalités -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-bullseye text-action text-sm"></i>3. Finalités du traitement
    </h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left border-collapse">
        <thead>
          <tr class="border-b border-action/10">
            <th class="text-ink/50 font-medium text-xs uppercase tracking-wider pb-2 pr-4">Finalité</th>
            <th class="text-ink/50 font-medium text-xs uppercase tracking-wider pb-2">Base légale</th>
          </tr>
        </thead>
        <tbody class="text-ink/70">
          <tr class="border-b border-action/5">
            <td class="py-2.5 pr-4">Gestion des comptes utilisateurs</td>
            <td class="py-2.5">Exécution du contrat</td>
          </tr>
          <tr class="border-b border-action/5">
            <td class="py-2.5 pr-4">Mise en relation conducteurs / passagers</td>
            <td class="py-2.5">Exécution du contrat</td>
          </tr>
          <tr class="border-b border-action/5">
            <td class="py-2.5 pr-4">Envoi d'e-mails transactionnels (réinitialisation MDP…)</td>
            <td class="py-2.5">Exécution du contrat</td>
          </tr>
          <tr class="border-b border-action/5">
            <td class="py-2.5 pr-4">Amélioration du service et statistiques anonymes</td>
            <td class="py-2.5">Intérêt légitime</td>
          </tr>
          <tr>
            <td class="py-2.5 pr-4">Sécurité et prévention des fraudes</td>
            <td class="py-2.5">Obligation légale</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 4. Conservation -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-clock text-action text-sm"></i>4. Durée de conservation
    </h2>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i><span><strong class="text-ink">Données de compte :</strong> conservées le temps de l'activité du compte, puis supprimées sous 30 jours après suppression du compte.</span></li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i><span><strong class="text-ink">Données de trajets :</strong> conservées 2 ans à des fins statistiques anonymisées.</span></li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i><span><strong class="text-ink">Logs de connexion :</strong> conservés 12 mois conformément aux obligations légales.</span></li>
    </ul>
  </div>

  <!-- 5. Partage -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-share-nodes text-action text-sm"></i>5. Partage des données
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Vos données ne sont pas vendues ni cédées à des tiers à des fins commerciales. Elles peuvent être partagées avec :
    </p>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Les autres utilisateurs pour les informations nécessaires à la mise en relation (prénom, ville, profil public).</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Nos prestataires techniques (hébergement, envoi d'e-mails), liés par des contrats de sous-traitance RGPD.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Les autorités compétentes sur réquisition judiciaire.</li>
    </ul>
  </div>

  <!-- 6. Droits -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-user-shield text-action text-sm"></i>6. Vos droits
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">Conformément au RGPD, vous disposez des droits suivants :</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-eye text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Accès</p>
        <p class="text-ink/50 text-xs mt-0.5">Consulter vos données</p>
      </div>
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-pen text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Rectification</p>
        <p class="text-ink/50 text-xs mt-0.5">Corriger vos données</p>
      </div>
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-trash text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Suppression</p>
        <p class="text-ink/50 text-xs mt-0.5">Effacer votre compte</p>
      </div>
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-hand text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Opposition</p>
        <p class="text-ink/50 text-xs mt-0.5">Refuser un traitement</p>
      </div>
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-pause text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Limitation</p>
        <p class="text-ink/50 text-xs mt-0.5">Restreindre un traitement</p>
      </div>
      <div class="bg-paper rounded-xl p-3 border border-action/10 text-center">
        <i class="fa-solid fa-file-export text-action mb-1.5"></i>
        <p class="text-ink text-xs font-semibold">Portabilité</p>
        <p class="text-ink/50 text-xs mt-0.5">Exporter vos données</p>
      </div>
    </div>
    <p class="text-ink/60 text-xs leading-relaxed">
      Pour exercer vos droits, contactez-nous à <a href="mailto:<?= esc($site->contactEmail) ?>" class="text-action hover:underline"><?= esc($site->contactEmail) ?></a>.
      En cas de litige, vous pouvez introduire une réclamation auprès de la
      <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" class="text-action hover:underline">CNIL</a>.
    </p>
  </div>

  <!-- 7. Cookies -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-cookie-bite text-action text-sm"></i>7. Cookies
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed mb-2">Kenweturi utilise uniquement des cookies strictement nécessaires au fonctionnement du service :</p>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i><span><strong class="text-ink">Cookie de session :</strong> maintien de votre connexion pendant la navigation.</span></li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i><span><strong class="text-ink">Préférences locales :</strong> thème (clair/sombre) et options d'affichage, stockés dans <code class="bg-action/10 text-action px-1 rounded text-xs">localStorage</code>.</span></li>
    </ul>
    <p class="text-ink/60 text-xs">Aucun cookie publicitaire ou de tracking tiers n'est utilisé.</p>
  </div>

</div>

<?= view('partials/footer') ?>
