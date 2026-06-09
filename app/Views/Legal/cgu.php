<?php /** @var \Config\Site $site */ ?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Légal</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Conditions Générales d'Utilisation</h1>
    <p class="text-ink/40 text-sm">Dernière mise à jour : <?= date('d/m/Y') ?></p>
  </div>

  <!-- Introduction -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10">
    <p class="text-ink/70 text-sm leading-relaxed">
      Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation de la plateforme
      <strong class="text-ink">Kenweturi</strong>, accessible à l'adresse <strong class="text-ink"><?= esc($site->siteUrl) ?></strong>.
      En créant un compte ou en utilisant le service, vous acceptez sans réserve les présentes conditions.
      Si vous ne les acceptez pas, veuillez ne pas utiliser le service.
    </p>
  </div>

  <!-- 1. Objet -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-circle-info text-action text-sm"></i>1. Objet du service
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi est une plateforme de mise en relation entre conducteurs et passagers pour des trajets de covoiturage,
      principalement à destination des trajets domicile-travail et domicile-formation en région.
      Kenweturi agit en qualité d'intermédiaire technique et n'est pas partie aux accords conclus entre utilisateurs.
    </p>
  </div>

  <!-- 2. Inscription -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-user-plus text-action text-sm"></i>2. Inscription et compte utilisateur
    </h2>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed list-none">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>L'accès au service nécessite la création d'un compte avec une adresse e-mail valide.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Vous devez avoir au moins 18 ans ou être un mineur émancipé pour vous inscrire.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Vous êtes responsable de la confidentialité de vos identifiants et de toute activité effectuée depuis votre compte.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Toute information fournie lors de l'inscription doit être exacte, complète et à jour.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Kenweturi se réserve le droit de suspendre ou supprimer tout compte en cas de violation des présentes CGU.</li>
    </ul>
  </div>

  <!-- 3. Trajets -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-car-side text-action text-sm"></i>3. Publication et réservation de trajets
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed mb-1">
      <strong class="text-ink">Conducteurs :</strong>
    </p>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed mb-3">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Vous devez être titulaire d'un permis de conduire valide et disposer d'une assurance véhicule en cours de validité.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Les informations publiées (date, heure, nombre de places) doivent être exactes.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Le partage de frais ne doit pas constituer une activité lucrative.</li>
    </ul>
    <p class="text-ink/70 text-sm leading-relaxed mb-1">
      <strong class="text-ink">Passagers :</strong>
    </p>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>En réservant un trajet, vous vous engagez à être présent au point de départ à l'heure convenue.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Toute annulation doit être effectuée dans les meilleurs délais pour permettre au conducteur d'en être informé.</li>
    </ul>
  </div>

  <!-- 4. Comportement -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-hand-shake text-action text-sm"></i>4. Comportement des utilisateurs
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">Il est interdit d'utiliser la plateforme pour :</p>
    <ul class="flex flex-col gap-2 text-ink/70 text-sm leading-relaxed">
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Publier des informations fausses, trompeuses ou frauduleuses.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Harceler, menacer ou porter atteinte à un autre utilisateur.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Utiliser le service à des fins commerciales non autorisées.</li>
      <li class="flex items-start gap-2"><i class="fa-solid fa-chevron-right text-action/50 text-xs mt-1 shrink-0"></i>Contourner les mécanismes de sécurité ou tenter d'accéder aux données d'autres utilisateurs.</li>
    </ul>
  </div>

  <!-- 5. Responsabilité -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-shield-halved text-action text-sm"></i>5. Responsabilité
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi met à disposition une plateforme de mise en relation et ne saurait être tenu responsable des dommages
      causés par les utilisateurs entre eux, des accidents de la route, ou du non-respect de leurs engagements mutuels.
      Kenweturi ne garantit pas la disponibilité permanente et ininterrompue du service.
    </p>
  </div>

  <!-- 6. Modification -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-pen-to-square text-action text-sm"></i>6. Modification des CGU
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés
      de toute modification substantielle par e-mail ou par notification sur la plateforme. La poursuite de l'utilisation
      du service après notification vaut acceptation des nouvelles conditions.
    </p>
  </div>

  <!-- Contact -->
  <div class="bg-action/5 border border-action/20 rounded-2xl p-6 flex items-start gap-4">
    <i class="fa-solid fa-envelope text-action mt-0.5"></i>
    <div>
      <p class="text-ink text-sm font-semibold mb-1">Une question sur nos CGU ?</p>
      <p class="text-ink/60 text-sm">Contactez-nous à l'adresse <a href="mailto:<?= esc($site->contactEmail) ?>" class="text-action hover:underline"><?= esc($site->contactEmail) ?></a></p>
    </div>
  </div>

</div>

<?= view('partials/footer') ?>
