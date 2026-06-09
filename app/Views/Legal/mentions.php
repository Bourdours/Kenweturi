<?php /** @var \Config\Site $site */ ?>
<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-4 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Légal</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Mentions Légales</h1>
    <p class="text-ink/40 text-sm">Conformément à l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la Confiance dans l'Économie Numérique (LCEN).</p>
  </div>

  <!-- Éditeur -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-building text-action text-sm"></i>Éditeur du site
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Nom</p>
        <p class="text-ink/80">Kenweturi</p>
      </div>
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Forme juridique</p>
        <p class="text-ink/80">Projet étudiant / non commercial</p>
      </div>
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Adresse e-mail</p>
        <p class="text-ink/80"><a href="mailto:<?= esc($site->contactEmail) ?>" class="text-action hover:underline"><?= esc($site->contactEmail) ?></a></p>
      </div>
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Directeur de la publication</p>
        <p class="text-ink/80">L'équipe Kenweturi</p>
      </div>
    </div>
  </div>

  <!-- Hébergement -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-server text-action text-sm"></i>Hébergement
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Hébergeur</p>
        <p class="text-ink/80">À renseigner</p>
      </div>
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Adresse</p>
        <p class="text-ink/80">À renseigner</p>
      </div>
      <div>
        <p class="text-ink/40 text-xs uppercase tracking-wider font-medium mb-1">Site web</p>
        <p class="text-ink/80">À renseigner</p>
      </div>
    </div>
  </div>

  <!-- Propriété intellectuelle -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-copyright text-action text-sm"></i>Propriété intellectuelle
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      L'ensemble des éléments constituant le site Kenweturi (design, structure, textes, logos, icônes, code source)
      sont la propriété exclusive de leurs auteurs et sont protégés par les lois françaises et internationales
      relatives à la propriété intellectuelle.
    </p>
    <p class="text-ink/70 text-sm leading-relaxed">
      Toute reproduction, représentation, modification, publication ou adaptation, totale ou partielle, de ces éléments,
      sans accord préalable et écrit de l'équipe Kenweturi, est strictement interdite.
    </p>
    <div class="flex items-start gap-3 bg-action/5 border border-action/15 rounded-xl px-4 py-3 mt-1">
      <i class="fa-brands fa-font-awesome text-action text-sm mt-0.5 shrink-0"></i>
      <p class="text-ink/60 text-xs leading-relaxed">
        Les icônes utilisées proviennent de <strong class="text-ink/80">Font Awesome</strong> (licence SIL OFL 1.1 pour les polices, CC BY 4.0 pour les icônes).
      </p>
    </div>
  </div>

  <!-- Limitation de responsabilité -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-triangle-exclamation text-action text-sm"></i>Limitation de responsabilité
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site.
      Toutefois, Kenweturi décline toute responsabilité quant aux erreurs ou omissions dans les informations
      diffusées, à l'indisponibilité du service, aux dommages directs ou indirects résultant de l'accès ou
      de l'utilisation du site.
    </p>
    <p class="text-ink/70 text-sm leading-relaxed">
      Des liens hypertextes peuvent pointer vers des sites tiers. Kenweturi n'exerce aucun contrôle sur
      ces sites et décline toute responsabilité quant à leur contenu.
    </p>
  </div>

  <!-- Droit applicable -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-scale-balanced text-action text-sm"></i>Droit applicable
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Le présent site et les présentes mentions légales sont soumis au droit français. En cas de litige,
      et à défaut de résolution amiable, les tribunaux français seront seuls compétents.
    </p>
  </div>

  <!-- Liens vers autres pages légales -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="<?= site_url('cgu') ?>" class="bg-surface border border-action/10 hover:border-action/30 rounded-2xl p-5 flex items-center gap-4 transition-colors group">
      <div class="w-10 h-10 rounded-xl bg-action/10 flex items-center justify-center shrink-0 group-hover:bg-action/20 transition-colors">
        <i class="fa-solid fa-file-contract text-action text-sm"></i>
      </div>
      <div>
        <p class="text-ink text-sm font-semibold">CGU</p>
        <p class="text-ink/40 text-xs">Conditions d'utilisation</p>
      </div>
      <i class="fa-solid fa-chevron-right text-ink/20 text-xs ml-auto"></i>
    </a>
    <a href="<?= site_url('confidentialite') ?>" class="bg-surface border border-action/10 hover:border-action/30 rounded-2xl p-5 flex items-center gap-4 transition-colors group">
      <div class="w-10 h-10 rounded-xl bg-action/10 flex items-center justify-center shrink-0 group-hover:bg-action/20 transition-colors">
        <i class="fa-solid fa-shield-halved text-action text-sm"></i>
      </div>
      <div>
        <p class="text-ink text-sm font-semibold">Confidentialité</p>
        <p class="text-ink/40 text-xs">Politique de protection des données</p>
      </div>
      <i class="fa-solid fa-chevron-right text-ink/20 text-xs ml-auto"></i>
    </a>
  </div>

</div>

<?= view('partials/footer') ?>
