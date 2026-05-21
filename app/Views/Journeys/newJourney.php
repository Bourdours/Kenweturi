<?= view('partials/head') ?>
<?= view('partials/header') ?>

  <form id="addJourneyForm" action="/journeys/new" method="post">

      <div class="field" style="color: white;">
        <label for="startAddress">Départ</label>
        <input style="color: black;" id="startAddress" class="address" name="startAddress" type="text">
      </div>

      <div class="field" style="color: white;">
        <label for="endAddress">Arrivée</label>
        <input style="color: black;" id="endAddress" class="address" name="endAddress" type="text">
      </div>

      <div class="field" style="color: white;">
        <label for="stage1">Etape 1</label>
        <input style="color: black;" id="stage1" class="address" name="stagesAddresses[]" type="text">
      </div>

      <div class="field" style="color: white;">
        <label for="stage2">Etape 2</label>
        <input style="color: black;" id="stage1" class="address" name="stagesAddresses[]" type="text">
      </div>

      <div class="field" style="color: white;">
        <label for="startDateTime">Date de départ</label>
        <input style="color: black;" id="startDateTime" name="startDate" type="Date">
      </div>

      <div class="field" style="color: white;">
        <label for="startDateTime">Heure de départ</label>
        <input style="color: black;" id="startDateTime" name="startTime" type="Time">
      </div>

      <div class="field" style="color: white;">
        <label for="seats">Nombre de places</label>
        <input style="color: black;" id="seats" name="seats" type="number">
      </div>

      <div class="field" style="color: white;">
        <label>Fumeur</label>
        <div>
          <input type="radio" id="smoking-yes" name="smoking" value=1>
          <label for="smoking-yes" style="color: white;">Oui</label>

          <input type="radio" id="smoking-no" name="smoking" value=0>
          <label for="smoking-no" style="color: white;">Non</label>
        </div>
      </div>

      <div class="field" style="color: white;">
        <label for="note">Note</label>
        <textarea style="color: black;" id="note" name="note"></textarea>
      </div>

      <button type="submit" style="color: white;">Soumettre</button>

  </form>

<script src="<?= base_url('js/autocompletion.js') ?>" defer></script>

<?= view('partials/footer') ?>