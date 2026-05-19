<?= view('partials/head') ?>
<?= view('partials/header') ?>

<div class="max-w-4xl mx-auto py-10 px-6 md:px-8 flex flex-col gap-10">

  <!-- En-tête -->
  <div>
    <p class="text-action text-xs font-semibold uppercase tracking-widest mb-2">Service</p>
    <h1 class="text-ink text-3xl font-bold font-display mb-2">Comment ça marche</h1>
    <p class="text-ink/40 text-sm">En quelques étapes, trouvez ou proposez un covoiturage près de chez vous.</p>
  </div>

  <!-- Parcours conducteur -->
  <div class="flex flex-col gap-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-car-side text-action text-sm"></i>
      </div>
      <h2 class="text-ink text-base font-semibold font-display">Je suis conducteur</h2>
    </div>

    <div class="bg-surface rounded-2xl border border-action/10 divide-y divide-action/10">

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">1</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Créez votre compte</p>
          <p class="text-ink/60 text-xs leading-relaxed">Inscrivez-vous gratuitement et complétez votre profil avec votre véhicule et vos préférences de trajet.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">2</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Publiez votre trajet</p>
          <p class="text-ink/60 text-xs leading-relaxed">Indiquez votre point de départ, votre destination, vos horaires et le nombre de places disponibles.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">3</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Acceptez les demandes</p>
          <p class="text-ink/60 text-xs leading-relaxed">Vous recevez une notification quand un passager réserve une place. Vous gardez le contrôle sur qui monte à bord.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">4</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Partagez la route</p>
          <p class="text-ink/60 text-xs leading-relaxed">Récupérez vos passagers au point de rendez-vous convenu et partagez le trajet. Simple, régulier, économique.</p>
        </div>
      </div>

    </div>

    <a href="<?= site_url('journeys/new') ?>" class="self-start bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-5 py-2.5">
      Publier un trajet
    </a>
  </div>

  <!-- Séparateur -->
  <div class="border-t border-action/10"></div>

  <!-- Parcours passager -->
  <div class="flex flex-col gap-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-person-walking text-action text-sm"></i>
      </div>
      <h2 class="text-ink text-base font-semibold font-display">Je suis passager</h2>
    </div>

    <div class="bg-surface rounded-2xl border border-action/10 divide-y divide-action/10">

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">1</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Recherchez un trajet</p>
          <p class="text-ink/60 text-xs leading-relaxed">Entrez votre départ, votre destination et la date souhaitée pour voir les trajets disponibles autour de vous.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">2</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Choisissez votre conducteur</p>
          <p class="text-ink/60 text-xs leading-relaxed">Consultez les profils, les avis et les horaires. Sélectionnez le trajet qui vous convient le mieux.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">3</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Réservez votre place</p>
          <p class="text-ink/60 text-xs leading-relaxed">Envoyez votre demande de réservation. Vous serez notifié dès que le conducteur l'accepte.</p>
        </div>
      </div>

      <div class="flex items-start gap-5 p-5">
        <div class="w-8 h-8 rounded-full bg-action flex items-center justify-center shrink-0 mt-0.5">
          <span class="text-ink text-xs font-bold">4</span>
        </div>
        <div class="flex flex-col gap-1">
          <p class="text-ink text-sm font-semibold">Voyagez ensemble</p>
          <p class="text-ink/60 text-xs leading-relaxed">Rendez-vous au point de départ convenu et profitez du trajet.</p>
        </div>
      </div>

    </div>

    <a href="<?= site_url('journeys') ?>" class="self-start bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-5 py-2.5">
      Chercher un trajet
    </a>
  </div>

  <!-- Points clés -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
      <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
        <i class="fa-solid fa-leaf text-action text-sm"></i>
      </div>
      <p class="text-ink text-sm font-semibold">Moins de CO₂</p>
      <p class="text-ink/60 text-xs leading-relaxed">Moins de voitures sur les routes pour les mêmes trajets du quotidien.</p>
    </div>

    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
      <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
        <i class="fa-solid fa-handshake text-action text-sm"></i>
      </div>
      <p class="text-ink text-sm font-semibold">Mise en relation simple</p>
      <p class="text-ink/60 text-xs leading-relaxed">Conducteurs et passagers se retrouvent en quelques clics, sans intermédiaire.</p>
    </div>

    <div class="bg-surface rounded-2xl p-5 border border-action/10 flex flex-col gap-2">
      <div class="w-9 h-9 rounded-xl bg-action/10 flex items-center justify-center">
        <i class="fa-solid fa-shield-halved text-action text-sm"></i>
      </div>
      <p class="text-ink text-sm font-semibold">Communauté locale</p>
      <p class="text-ink/60 text-xs leading-relaxed">Conducteurs et passagers d'une même région, collègues ou étudiants.</p>
    </div>

  </div>

  <!-- FAQ -->
  <div class="flex flex-col gap-3">
    <h2 class="text-ink text-base font-semibold font-display">Questions fréquentes</h2>

    <div class="bg-surface rounded-2xl border border-action/10 divide-y divide-action/10">

      <details class="group p-5 cursor-pointer">
        <summary class="flex items-center justify-between gap-4 list-none">
          <p class="text-ink text-sm font-semibold">Est-ce que Kenweturi est gratuit ?</p>
          <i class="fa-solid fa-chevron-down text-ink/30 text-xs transition-transform group-open:rotate-180 shrink-0"></i>
        </summary>
        <p class="text-ink/60 text-xs leading-relaxed mt-3">Oui, l'inscription et l'utilisation de la plateforme sont entièrement gratuites.</p>
      </details>

      <details class="group p-5 cursor-pointer">
        <summary class="flex items-center justify-between gap-4 list-none">
          <p class="text-ink text-sm font-semibold">Ma réservation est-elle confirmée immédiatement ?</p>
          <i class="fa-solid fa-chevron-down text-ink/30 text-xs transition-transform group-open:rotate-180 shrink-0"></i>
        </summary>
        <p class="text-ink/60 text-xs leading-relaxed mt-3">Non, la réservation est confirmée une fois que le conducteur a accepté votre demande. Vous recevez une notification dès sa réponse.</p>
      </details>

      <details class="group p-5 cursor-pointer">
        <summary class="flex items-center justify-between gap-4 list-none">
          <p class="text-ink text-sm font-semibold">Puis-je annuler une réservation ?</p>
          <i class="fa-solid fa-chevron-down text-ink/30 text-xs transition-transform group-open:rotate-180 shrink-0"></i>
        </summary>
        <p class="text-ink/60 text-xs leading-relaxed mt-3">Oui, conducteurs et passagers peuvent annuler depuis leur espace personnel. Pensez à prévenir l'autre partie dès que possible.</p>
      </details>

      <details class="group p-5 cursor-pointer">
        <summary class="flex items-center justify-between gap-4 list-none">
          <p class="text-ink text-sm font-semibold">Qui peut utiliser Kenweturi ?</p>
          <i class="fa-solid fa-chevron-down text-ink/30 text-xs transition-transform group-open:rotate-180 shrink-0"></i>
        </summary>
        <p class="text-ink/60 text-xs leading-relaxed mt-3">Kenweturi est ouvert à tous — salariés, apprentis, étudiants — pour des trajets réguliers domicile-travail ou domicile-formation dans la région.</p>
      </details>

    </div>
  </div>

  <!-- CTA final -->
  <div class="bg-surface rounded-2xl p-6 border border-action/10 flex flex-col sm:flex-row items-center gap-4">
    <div class="flex-1 flex flex-col gap-1">
      <p class="text-ink text-sm font-semibold">Prêt à démarrer ?</p>
      <p class="text-ink/50 text-xs">Rejoignez la communauté Kenweturi et simplifiez vos trajets du quotidien.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 shrink-0">
      <a href="<?= site_url('register') ?>" class="bg-action hover:bg-action/90 transition-colors text-ink font-semibold text-sm rounded-xl px-5 py-2.5 text-center">
        Créer un compte
      </a>
      <a href="<?= site_url('journeys') ?>" class="bg-paper hover:bg-action/10 border border-action/20 transition-colors text-ink/70 font-semibold text-sm rounded-xl px-5 py-2.5 text-center">
        Voir les trajets
      </a>
    </div>
  </div>

</div>

<?= view('partials/footer') ?>