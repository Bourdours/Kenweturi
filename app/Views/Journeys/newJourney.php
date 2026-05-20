<?= view('partials/head', ['extraJs' => [base_url('js/autocompletion.js')]]) ?>
<?= view('partials/header') ?>

<main>

  <form id="addJourneyForm" action="" method="post">
      <div class="field" style="color: white;">
        <label for="startAddress">Départ</label>
        <input style="color: black;" id="startAddress" class="address" name="startAddress" type="text">
        <input style="display: none;" id="startAddressLng" class="lng" name="startLng" type="text">
        <input style="display: none;" id="startAddressLat" class="lat" name="startLat" type="text">
      </div>
      <div class="field" style="color: white;">
        <label for="endAddress">Arrivée</label>
        <input style="color: black;" id="endAddress" class="address" name="endAddress" type="text">
        <input style="display: none;" id="endAddressLng" class="lng" name="endLng" type="text">
        <input style="display: none;" id="endAddressLat" class="lat" name="endLat" type="text">
      </div>
      <button type="submit">Soumettre</button>
  </form>

</main>

<?= view('partials/footer') ?>