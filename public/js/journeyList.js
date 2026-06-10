(function () {
    const buttons = document.querySelectorAll('.jsMapToggle');
    const tooltip = document.getElementById('mapTooltip');

    if (!buttons.length || !tooltip) return;

    const style      = getComputedStyle(document.documentElement);
    const toRgb      = (v) => `rgb(${style.getPropertyValue(v).trim().replace(/\s+/g, ',')})`;
    const brandColor = toRgb('--color-brand');
    const isDark     = document.documentElement.classList.contains('dark');
    const tileUrl    = isDark
        ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
        : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';

    const isTouch = window.matchMedia('(hover: none)').matches;

    const map = L.map(tooltip, {
        zoomControl:        false,
        attributionControl: false,
        dragging:           false,
        scrollWheelZoom:    false,
        doubleClickZoom:    false,
        touchZoom:          false,
        keyboard:           false,
    });

    L.tileLayer(tileUrl).addTo(map);

    let currentLayer = null;
    let activeBtn    = null;
    let timer;

    function positionTooltip(card, cursorX, cursorY) {
        const tipW = 280, tipH = 180;

        if (isTouch) {
            const rect  = card.getBoundingClientRect();
            const left  = Math.max(8, Math.min(rect.left, window.innerWidth - tipW - 8));
            const below = window.innerHeight - rect.bottom > tipH + 8;
            tooltip.style.left = `${left}px`;
            tooltip.style.top  = `${below ? rect.bottom + 8 : rect.top - tipH - 8}px`;
        } else {
            const offset = 16;
            const left   = cursorX + offset + tipW < window.innerWidth
                ? cursorX + offset
                : cursorX - tipW - offset;
            tooltip.style.left = `${left}px`;
            tooltip.style.top  = `${Math.max(8, Math.min(cursorY - tipH / 2, window.innerHeight - tipH - 8))}px`;
        }
    }

    function showMap(btn, card, cursorX, cursorY) {
        if (currentLayer) { map.removeLayer(currentLayer); currentLayer = null; }

        currentLayer = L.geoJSON(JSON.parse(card.dataset.geojson), {
            style: { color: brandColor, weight: 3, opacity: 0.9 },
        }).addTo(map);

        positionTooltip(card, cursorX, cursorY);
        tooltip.classList.remove('opacity-0');
        if (isTouch) tooltip.classList.remove('pointer-events-none');

        map.invalidateSize();
        map.fitBounds(currentLayer.getBounds(), { padding: [16, 16] });
        activeBtn = btn;
    }

    function hideMap() {
        tooltip.classList.add('opacity-0');
        if (isTouch) tooltip.classList.add('pointer-events-none');
        activeBtn = null;
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (!isTouch) return;

            const card = btn.closest('[data-geojson]');
            if (activeBtn === btn) {
                hideMap();
            } else {
                if (activeBtn) hideMap();
                showMap(btn, card, 0, 0);
            }
        });

        if (!isTouch) {
            btn.addEventListener('mouseenter', (e) => {
                timer = setTimeout(() => {
                    showMap(btn, btn.closest('[data-geojson]'), e.clientX, e.clientY);
                }, 120);
            });

            btn.addEventListener('mouseleave', () => {
                clearTimeout(timer);
                hideMap();
            });
        }
    });

    if (isTouch) {
        document.addEventListener('click', (e) => {
            if (!tooltip.contains(e.target)) hideMap();
        });
        window.addEventListener('scroll', hideMap, { passive: true });
    }
})();
