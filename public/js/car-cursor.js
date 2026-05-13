(function () {
  const W = 72, H = 40;
  const FRONT_X = 68, CENTER_Y = 22; // nez = point curseur

  // Thème sombre : caisse navy, bande orange
  const SVG_DARK = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="9" ry="4" fill="#0A1525"/>
    <ellipse cx="56" cy="35" rx="9" ry="4" fill="#0A1525"/>
    <path d="M2 28 L2 17 Q2 14 6 14 L67 14 Q70 14 70 17 L70 28 Q70 32 67 32 L5 32 Q2 32 2 28Z" fill="#22467A"/>
    <path d="M10 14 C12 5 18 3 24 3 L44 3 C50 3 54 6 56 14Z" fill="#22467A"/>
    <path d="M13 13 C14 7 18 4 23 4 L22 13Z" fill="rgba(180,215,255,0.75)"/>
    <rect x="23" y="4" width="18" height="9" fill="rgba(180,215,255,0.75)"/>
    <path d="M43 4 C48 4 52 7 54 13 L43 13Z" fill="rgba(180,215,255,0.75)"/>
    <rect x="4" y="22" width="64" height="3" rx="1.5" fill="#D9663F"/>
    <rect x="67" y="17" width="4" height="8" rx="2" fill="#FFF0A0"/>
    <rect x="1" y="17" width="4" height="8" rx="2" fill="#D9663F"/>
    <circle cx="14" cy="34" r="7" fill="#0A1525"/>
    <circle cx="14" cy="34" r="3" fill="#8899BB"/>
    <circle cx="56" cy="34" r="7" fill="#0A1525"/>
    <circle cx="56" cy="34" r="3" fill="#8899BB"/>
  </svg>`;

  // Thème clair : caisse orange, bande navy — lisible sur fond crème
  const SVG_LIGHT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="9" ry="4" fill="#1B3862"/>
    <ellipse cx="56" cy="35" rx="9" ry="4" fill="#1B3862"/>
    <path d="M2 28 L2 17 Q2 14 6 14 L67 14 Q70 14 70 17 L70 28 Q70 32 67 32 L5 32 Q2 32 2 28Z" fill="#D9663F"/>
    <path d="M10 14 C12 5 18 3 24 3 L44 3 C50 3 54 6 56 14Z" fill="#C25530"/>
    <path d="M13 13 C14 7 18 4 23 4 L22 13Z" fill="rgba(14,26,46,0.4)"/>
    <rect x="23" y="4" width="18" height="9" fill="rgba(14,26,46,0.4)"/>
    <path d="M43 4 C48 4 52 7 54 13 L43 13Z" fill="rgba(14,26,46,0.4)"/>
    <rect x="4" y="22" width="64" height="3" rx="1.5" fill="#22467A"/>
    <rect x="67" y="17" width="4" height="8" rx="2" fill="#FFE566"/>
    <rect x="1" y="17" width="4" height="8" rx="2" fill="#22467A"/>
    <circle cx="14" cy="34" r="7" fill="#1B3862"/>
    <circle cx="14" cy="34" r="3" fill="#F4F2EC"/>
    <circle cx="56" cy="34" r="7" fill="#1B3862"/>
    <circle cx="56" cy="34" r="3" fill="#F4F2EC"/>
  </svg>`;

  // Décapotable : toit ouvert, cockpit visible, appuie-têtes, pare-brise seul
  const SVG_COUPE_DARK = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="9" ry="4" fill="#0A1525"/>
    <ellipse cx="56" cy="35" rx="9" ry="4" fill="#0A1525"/>
    <path d="M2 29 L2 19 Q2 16 6 16 L67 16 Q70 16 70 19 L70 29 Q70 32 67 32 L5 32 Q2 32 2 29Z" fill="#22467A"/>
    <rect x="11" y="12" width="36" height="4" rx="1.5" fill="#0A1525"/>
    <path d="M49 16 L51 10 L63 10 L63 16Z" fill="rgba(180,215,255,0.75)"/>
    <ellipse cx="23" cy="13" rx="3" ry="2.5" fill="#1B3862"/>
    <ellipse cx="34" cy="13" rx="3" ry="2.5" fill="#1B3862"/>
    <rect x="1" y="14" width="10" height="2" rx="1" fill="#D9663F"/>
    <rect x="4" y="24" width="64" height="2" rx="1" fill="#D9663F"/>
    <rect x="67" y="19" width="4" height="5" rx="1.5" fill="#FFF0A0"/>
    <rect x="1" y="19" width="4" height="5" rx="1.5" fill="#D9663F"/>
    <circle cx="14" cy="34" r="7" fill="#0A1525"/>
    <circle cx="14" cy="34" r="3.5" fill="#8899BB"/>
    <circle cx="56" cy="34" r="7" fill="#0A1525"/>
    <circle cx="56" cy="34" r="3.5" fill="#8899BB"/>
  </svg>`;

  const SVG_COUPE_LIGHT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="9" ry="4" fill="#1B3862"/>
    <ellipse cx="56" cy="35" rx="9" ry="4" fill="#1B3862"/>
    <path d="M2 29 L2 19 Q2 16 6 16 L67 16 Q70 16 70 19 L70 29 Q70 32 67 32 L5 32 Q2 32 2 29Z" fill="#D9663F"/>
    <rect x="11" y="12" width="36" height="4" rx="1.5" fill="#1B3862"/>
    <path d="M49 16 L51 10 L63 10 L63 16Z" fill="rgba(14,26,46,0.4)"/>
    <ellipse cx="23" cy="13" rx="3" ry="2.5" fill="#22467A"/>
    <ellipse cx="34" cy="13" rx="3" ry="2.5" fill="#22467A"/>
    <rect x="1" y="14" width="10" height="2" rx="1" fill="#22467A"/>
    <rect x="4" y="24" width="64" height="2" rx="1" fill="#22467A"/>
    <rect x="67" y="19" width="4" height="5" rx="1.5" fill="#FFE566"/>
    <rect x="1" y="19" width="4" height="5" rx="1.5" fill="#22467A"/>
    <circle cx="14" cy="34" r="7" fill="#1B3862"/>
    <circle cx="14" cy="34" r="3.5" fill="#F4F2EC"/>
    <circle cx="56" cy="34" r="7" fill="#1B3862"/>
    <circle cx="56" cy="34" r="3.5" fill="#F4F2EC"/>
  </svg>`;

  // SUV / 4x4 : caisse haute et carrée, galerie de toit, pare-chocs épais
  const SVG_SUV_DARK = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="10" ry="4" fill="#0A1525"/>
    <ellipse cx="56" cy="35" rx="10" ry="4" fill="#0A1525"/>
    <path d="M2 30 L2 13 Q2 10 6 10 L67 10 Q70 10 70 13 L70 30 Q70 33 67 33 L5 33 Q2 33 2 30Z" fill="#22467A"/>
    <path d="M6 10 C6 4 10 2 16 2 L54 2 C60 2 62 4 62 10Z" fill="#22467A"/>
    <rect x="20" y="2" width="26" height="1.5" rx="0.75" fill="#D9663F" opacity="0.8"/>
    <path d="M9 9 C9 4 13 3 17 3 L16 9Z" fill="rgba(180,215,255,0.75)"/>
    <rect x="17" y="3" width="22" height="6" fill="rgba(180,215,255,0.75)"/>
    <path d="M41 3 C47 3 56 6 59 9 L41 9Z" fill="rgba(180,215,255,0.75)"/>
    <rect x="4" y="22" width="64" height="3" rx="1.5" fill="#D9663F"/>
    <rect x="67" y="12" width="4" height="8" rx="1.5" fill="#FFF0A0"/>
    <rect x="1" y="12" width="4" height="8" rx="1.5" fill="#D9663F"/>
    <rect x="63" y="27" width="8" height="4" rx="1.5" fill="#1B3862"/>
    <rect x="1" y="27" width="8" height="4" rx="1.5" fill="#1B3862"/>
    <circle cx="14" cy="34" r="7" fill="#0A1525"/>
    <circle cx="14" cy="34" r="2.5" fill="#8899BB"/>
    <circle cx="56" cy="34" r="7" fill="#0A1525"/>
    <circle cx="56" cy="34" r="2.5" fill="#8899BB"/>
  </svg>`;

  const SVG_SUV_LIGHT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
    <ellipse cx="14" cy="35" rx="10" ry="4" fill="#1B3862"/>
    <ellipse cx="56" cy="35" rx="10" ry="4" fill="#1B3862"/>
    <path d="M2 30 L2 13 Q2 10 6 10 L67 10 Q70 10 70 13 L70 30 Q70 33 67 33 L5 33 Q2 33 2 30Z" fill="#D9663F"/>
    <path d="M6 10 C6 4 10 2 16 2 L54 2 C60 2 62 4 62 10Z" fill="#C25530"/>
    <rect x="20" y="2" width="26" height="1.5" rx="0.75" fill="#22467A" opacity="0.8"/>
    <path d="M9 9 C9 4 13 3 17 3 L16 9Z" fill="rgba(14,26,46,0.4)"/>
    <rect x="17" y="3" width="22" height="6" fill="rgba(14,26,46,0.4)"/>
    <path d="M41 3 C47 3 56 6 59 9 L41 9Z" fill="rgba(14,26,46,0.4)"/>
    <rect x="4" y="22" width="64" height="3" rx="1.5" fill="#22467A"/>
    <rect x="67" y="12" width="4" height="8" rx="1.5" fill="#FFE566"/>
    <rect x="1" y="12" width="4" height="8" rx="1.5" fill="#22467A"/>
    <rect x="63" y="27" width="8" height="4" rx="1.5" fill="#1B3862"/>
    <rect x="1" y="27" width="8" height="4" rx="1.5" fill="#1B3862"/>
    <circle cx="14" cy="34" r="7" fill="#1B3862"/>
    <circle cx="14" cy="34" r="2.5" fill="#F4F2EC"/>
    <circle cx="56" cy="34" r="7" fill="#1B3862"/>
    <circle cx="56" cy="34" r="2.5" fill="#F4F2EC"/>
  </svg>`;

  const cursorStyle = document.createElement('style');
  cursorStyle.textContent = '*, *::before, *::after { cursor: none !important; }';
  document.head.appendChild(cursorStyle);

  const car = document.createElement('div');
  car.setAttribute('aria-hidden', 'true');
  Object.assign(car.style, {
    position: 'fixed',
    top: '0',
    left: '0',
    pointerEvents: 'none',
    zIndex: '2147483647',
    willChange: 'transform',
    transform: 'translate(-200px, -200px)',
  });
  document.body.appendChild(car);

  // Point lumineux positionné exactement sur le curseur (sans délai)
  const dot = document.createElement('div');
  dot.setAttribute('aria-hidden', 'true');
  Object.assign(dot.style, {
    position: 'fixed',
    top: '0',
    left: '0',
    width: '7px',
    height: '7px',
    borderRadius: '50%',
    background: '#FFF0A0',
    boxShadow: '0 0 6px 3px rgba(255,240,160,0.7)',
    pointerEvents: 'none',
    zIndex: '2147483646',
    willChange: 'transform, opacity',
    transform: 'translate(-200px, -200px)',
    opacity: '0',
  });
  document.body.appendChild(dot);

  let carOff = false;

  function getCarSVG(dark) {
    const model = localStorage.getItem('carModel') || 'default';
    if (model === 'coupe') return dark ? SVG_COUPE_DARK : SVG_COUPE_LIGHT;
    if (model === 'suv')   return dark ? SVG_SUV_DARK   : SVG_SUV_LIGHT;
    return dark ? SVG_DARK : SVG_LIGHT;
  }

  function applyConfig() {
    const dark = document.documentElement.classList.contains('dark');
    carOff = document.documentElement.classList.contains('car-off');
    car.style.visibility  = carOff ? 'hidden' : 'visible';
    car.innerHTML         = getCarSVG(dark);
    dot.style.background  = dark ? '#FFF0A0' : '#D9663F';
    dot.style.boxShadow   = dark
      ? '0 0 6px 3px rgba(255,240,160,0.7)'
      : '0 0 6px 3px rgba(217,102,63,0.7)';
  }
  applyConfig();
  new MutationObserver(applyConfig).observe(
    document.documentElement, { attributeFilter: ['class'] }
  );
  document.addEventListener('carModelChange', applyConfig);

  // Nez à x=68 dans le SVG ; transform-origin par défaut = 50% = x=36
  // Après scaleX(s) : x_nez_local = 36 + 32*s
  // Pour que le nez soit au curseur : elem_left = cx - 36 - 32*s
  const OX       = W / 2;          // 36
  const NOSE_OFF = FRONT_X - OX;   // 32

  let tx = -200, ty = -200;
  let cx = -200, cy = -200;
  let targetScaleX = 1, currentScaleX = 1;
  let targetTilt = 0, currentTilt = 0;
  let initialized = false;

  document.addEventListener('mousemove', (e) => {
    if (!initialized) {
      cx = e.clientX;
      cy = e.clientY;
      initialized = true;
    }
    const rawDx = e.clientX - tx;
    const rawDy = e.clientY - ty;
    const speed = Math.hypot(rawDx, rawDy);

    if (rawDx > 3) targetScaleX = 1;
    else if (rawDx < -3) targetScaleX = -1;

    if (speed > 1) {
      const angle = Math.atan2(rawDy, Math.abs(rawDx)) * (180 / Math.PI);
      targetTilt = Math.max(-20, Math.min(20, angle));
    }

    tx = e.clientX;
    ty = e.clientY;
  });

  document.addEventListener('mouseleave', () => {
    tx = -200;
    ty = -200;
    targetTilt = 0;
    initialized = false;
  });

  (function animate() {
    cx += (tx - cx) * 0.12;
    cy += (ty - cy) * 0.12;
    currentScaleX += (targetScaleX - currentScaleX) * 0.12;
    currentTilt   += (targetTilt   - currentTilt)   * 0.08;

    // scaleX(-1) inverse le sens visuel de rotate → on multiplie par currentScaleX
    // pour que l'inclinaison reste cohérente dans les deux sens
    const effectiveTilt = currentTilt * currentScaleX;
    const elemX = cx - OX - NOSE_OFF * currentScaleX;

    car.style.transform =
      `translate(${elemX}px, ${cy - CENTER_Y}px) scaleX(${currentScaleX}) rotate(${effectiveTilt}deg)`;

    // Le point suit le vrai curseur sans délai ; s'efface quand la voiture l'a rattrapé
    const dist = Math.hypot(tx - cx, ty - cy);
    dot.style.transform = `translate(${tx - 3.5}px, ${ty - 3.5}px)`;
    dot.style.opacity   = tx > -100 ? (carOff ? 1 : Math.min(1, dist / 40)) : 0;

    requestAnimationFrame(animate);
  })();
})();
