(function () {
    const el = document.getElementById('journeyMap');
    if (!el) return;

    const waypoints = JSON.parse(el.dataset.waypoints);
    if (!waypoints.length) return;

    const style = getComputedStyle(document.documentElement);
    const toRgb = (v) => `rgb(${style.getPropertyValue(v).trim().replace(/\s+/g, ',')})`;
    const brandColor = toRgb('--color-brand');
    const actionColor = toRgb('--color-action');

    const map = L.map(el, { scrollWheelZoom: false });

    el.addEventListener('click', () => map.scrollWheelZoom.enable());
    el.addEventListener('mouseleave', () => map.scrollWheelZoom.disable());

    const isDark = document.documentElement.classList.contains('dark');
    L.tileLayer('https://data.geopf.fr/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2&STYLE=normal&FORMAT=image%2Fpng&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {
        attribution: '© <a href="https://www.ign.fr/">IGN</a>',
        minZoom: 2,
        maxZoom: 18,
    }).addTo(map);

    const latlngs = waypoints.map((wp) => [wp.lat, wp.lng]);

    map.fitBounds(L.latLngBounds(latlngs), { padding: [24, 24] });

    if (el.dataset.geojson) {
        L.geoJSON(JSON.parse(el.dataset.geojson), {
            style: { color: brandColor, weight: 4, opacity: 0.8 },
        }).addTo(map);
    } else {
        L.polyline(latlngs, { color: brandColor, weight: 4, opacity: 0.8 }).addTo(map);
    }

    waypoints.forEach((wp, i) => {
        const isFirst = i === 0;
        const isLast = i === waypoints.length - 1;
        const color = isFirst ? brandColor : isLast ? actionColor : '#6b7280';

        const icon = L.divIcon({
            className: '',
            html: `<div style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.35)"></div>`,
            iconSize: [12, 12],
            iconAnchor: [6, 6],
        });

        L.marker([wp.lat, wp.lng], { icon })
            .bindPopup(`<strong>${wp.label}</strong>`)
            .addTo(map);
    });

})();
