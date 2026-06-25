<?php

/** @var array       $waypoints [['lat' => float, 'lng' => float, 'label' => string], ...] */
/** @var string|null $geojson   GeoJSON brut du tracé (optionnel) */
?>
<article class="bg-surface-card rounded-2xl overflow-hidden border border-action/10">
    <div id="journeyMap"
        class="h-64 w-full"
        data-waypoints="<?= esc(json_encode($waypoints), 'attr') ?>"
        <?php if (!empty($geojson)): ?>data-geojson="<?= esc($geojson, 'attr') ?>" <?php endif ?>>
    </div>
</article>