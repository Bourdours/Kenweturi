(function () {
  var html    = document.documentElement;
  var btn     = document.getElementById('rainbow-road-toggle');
  var label   = document.getElementById('rainbow-road-label');
  var sparklesEl = null;

  var SPARKLE_POSITIONS = [
    [75, 5], [92, 14], [82, 26],
    [22, 30], [6, 44], [18, 50],
    [50, 38], [38, 62], [28, 72],
    [4, 78], [14, 87], [-2, 92],
    [58, 18], [44, 67], [68, 80],
    [35, 20], [10, 62], [80, 50]
  ];

  function addSparkles() {
    var svg = document.getElementById('home-svg');
    if (!svg || document.getElementById('rainbow-sparkles')) return;

    var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
    g.id = 'rainbow-sparkles';

    SPARKLE_POSITIONS.forEach(function (pos, i) {
      var c = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      c.setAttribute('cx', pos[0]);
      c.setAttribute('cy', pos[1]);
      c.setAttribute('r', 0.5);
      c.setAttribute('fill', 'hsl(' + ((i * 20) % 360) + ',100%,85%)');
      var dur  = (1.1 + (i % 6) * 0.25).toFixed(2);
      var del  = (-(i * 0.31) % 2.2).toFixed(2);
      c.style.animation = 'sparkle ' + dur + 's ease-in-out ' + del + 's infinite';
      g.appendChild(c);
    });

    svg.appendChild(g);
    sparklesEl = g;
  }

  function removeSparkles() {
    if (sparklesEl) { sparklesEl.remove(); sparklesEl = null; }
  }

  function syncRoadCenter(active) {
    var roadCenter = document.getElementById('road-center');
    if (!roadCenter) return;
    roadCenter.style.animation = active
      ? 'snake 2s linear infinite, rainbowRoadCenter 3s linear infinite'
      : 'snake 2s linear infinite';
    roadCenter.style.strokeOpacity = active ? '0.95' : '';
  }

  function sync() {
    var active = html.classList.contains('rainbow-road');
    if (label) label.textContent = active ? 'Route normale' : 'Rainbow Road';
    syncRoadCenter(active);
    if (active) addSparkles();
    else removeSparkles();
  }

  sync();

  if (btn) btn.addEventListener('click', function () {
    var active = html.classList.contains('rainbow-road');
    html.classList.toggle('rainbow-road', !active);
    localStorage.setItem('rainbowRoad', !active ? 'on' : 'off');
    sync();
  });
})();
