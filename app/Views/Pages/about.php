<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-6">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Entreprise</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">À propos de Kenweturi</h1>
    <p class="text-ink/40 text-sm">Le covoiturage régional, simple et solidaire.</p>
  </div>

  <!-- Mission -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-bullseye text-action text-sm"></i>Notre mission
    </h2>
    <p class="text-ink/70 text-sm leading-relaxed">
      Kenweturi est une plateforme de covoiturage pensée pour les trajets du quotidien — domicile-travail et domicile-formation.
      Notre objectif : rendre les déplacements régionaux plus économiques, plus écologiques et plus conviviaux, en connectant
      conducteurs et passagers qui partagent les mêmes routes.
    </p>
  </div>

  <!-- Valeurs -->
  <div class="flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display">Nos valeurs</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-leaf text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Écologie</p>
        <p class="text-ink/60 text-xs leading-relaxed">Moins de voitures sur les routes, moins d'émissions. Chaque trajet partagé compte.</p>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-handshake text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Solidarité</p>
        <p class="text-ink/60 text-xs leading-relaxed">Créer du lien entre collègues et étudiants d'une même région, trajet après trajet.</p>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
        <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
          <i class="fa-solid fa-piggy-bank text-action text-sm"></i>
        </div>
        <p class="text-ink text-sm font-semibold">Économies</p>
        <p class="text-ink/60 text-xs leading-relaxed">Partager les frais de carburant pour alléger le budget de chacun.</p>
      </div>

    </div>
  </div>

  <!-- Comment ça marche -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col gap-4">
    <h2 class="text-ink text-base font-semibold font-display flex items-center gap-2">
      <i class="fa-solid fa-route text-action text-sm"></i>Comment ça marche
    </h2>

    <div class="flex flex-col gap-4">
      <div class="flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">1</span>
        </div>
        <div>
          <p class="text-ink text-sm font-semibold">Créez votre compte</p>
          <p class="text-ink/60 text-xs leading-relaxed mt-0.5">Inscrivez-vous en quelques secondes et complétez votre profil.</p>
        </div>
      </div>

      <div class="flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">2</span>
        </div>
        <div>
          <p class="text-ink text-sm font-semibold">Publiez ou recherchez un trajet</p>
          <p class="text-ink/60 text-xs leading-relaxed mt-0.5">Conducteur, proposez vos trajets réguliers. Passager, trouvez un covoiturage qui correspond à votre itinéraire.</p>
        </div>
      </div>

      <div class="flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">3</span>
        </div>
        <div>
          <p class="text-ink text-sm font-semibold">Voyagez ensemble</p>
          <p class="text-ink/60 text-xs leading-relaxed mt-0.5">Partagez le trajet, partagez les frais. Simple, économique, convivial.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="flex flex-col sm:flex-row gap-3">
    <a href="<?= site_url('journeys') ?>" class="flex-1 bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-5 py-3 text-center">
      Chercher un trajet
    </a>
    <a href="<?= site_url('contact') ?>" class="flex-1 bg-surface hover:bg-action/10 border border-action/20 transition-colors text-ink/70 font-semibold text-sm rounded-xl px-5 py-3 text-center">
      Nous contacter
    </a>
  </div>

</div>

<?= view('partials/footer') ?>
