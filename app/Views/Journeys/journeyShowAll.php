<?= view('partials/head') ?>
<?= view('partials/header') ?>


<main>
    <form id="addJourneyForm" action="/journeys" method="GET">
        <div class="field" >
            <label for="startAddress" style="color: white;">Départ</label>
            <input type="text" id="startAddress" class="address" name="startAddress" value="<?= esc($startAddress ?? '') ?>" placeholder="Ville ou adresse de départ">
            <input type="text" style="display: none;" id="startAddressLng" class="lng" name="startLng" value="<?= esc($lngStart ?? '') ?>">
            <input type="text" style="display: none;" id="startAddressLat" class="lat" name="startLat" value="<?= esc($latStart ?? '') ?>">
        </div>
        <div class="field" >
            <label for="endAddress" style="color: white;">Arrivée</label>
            <input type="text" id="endAddress" class="address" name="endAddress" value="<?= esc($endAddress ?? '') ?>" placeholder="Ville ou adresse d'arrivée">
            <input type="text" style="display: none;" id="endAddressLng" class="lng" name="endLng" value="<?= esc($lngEnd ?? '') ?>">
            <input type="text" style="display: none;" id="endAddressLat" class="lat" name="endLat" value="<?= esc($latEnd ?? '') ?>">
        </div>
        <div>
            <label for="date" style="color: white;">Date</label>
            <input type="date" id="date" name="date" value="<?= esc($filterDate ?? '') ?>">
        </div>
        <div>
            <label for="time" style="color: white;">À partir de</label>
            <input type="time" id="time" name="time" value="<?= esc($filterTime ?? '') ?>">
        </div>
        <div>
            <label for="availableSeats" style="color: white;">Passagers</label>
            <input type="number" id="availableSeats" name="availableSeats" value="<?= esc($availableSeats ?? '') ?>" min="1">
        </div>
        <div>
            <label for="smoking" style="color: white;">Fumeur</label>
            <select id="smoking" name="smoking">
                <option value="">Indifférent</option>
                <option value="0" <?= ($smoking ?? '') === '0' ? 'selected' : '' ?>>Non-fumeur</option>
                <option value="1" <?= ($smoking ?? '') === '1' ? 'selected' : '' ?>>Fumeur</option>
            </select>
        </div>
        <button type="submit" style="color: white;">Rechercher</button>
    </form>

    <?php if (empty($journeys)) : ?>
    <p>Aucun trajet trouvé.</p>
    <?php else : ?>
        <?php foreach ($journeys as $journey) : ?>
            <div style="color: white;">
                <p><?= esc($journey['city_start_name']) ?> -> <?= esc($journey['city_end_name']) ?></p>
                <p><?= esc($journey['start_datetime']) ?></p>
                <p>Conducteur : <?= esc($journey['driver_firstname']) ?> <?= esc($journey['driver_lastname']) ?></p>
                <p>Places restantes : <?= esc($journey['seats']) ?></p>
                <p>Fumeur : <?= esc($journey['smoking']) ? 'Oui' : 'Non' ?></p>
                <a href="/journeys/<?= esc($journey['id']) ?>">Voir le trajet</a>
            </div>
        <?php endforeach ?>

        <?= $pager->makeLinks($page, $perPage, $total) ?>
    <?php endif ?>
</main>
<script src="<?= base_url('js/autocompletion.js') ?>" defer></script>

<?= view('partials/footer') ?>