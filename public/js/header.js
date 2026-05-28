const menuToggle = document.getElementById('menu-toggle');
if (menuToggle) {
  menuToggle.addEventListener('click', function () {
    document.getElementById('mobile-menu').classList.toggle('hidden');
  });
}

const userMenuToggle = document.getElementById('user-menu-toggle');
const userDropdown   = document.getElementById('user-dropdown');

if (userMenuToggle && userDropdown) {
  userMenuToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    userDropdown.classList.toggle('hidden');
  });

  document.addEventListener('click', function () {
    userDropdown.classList.add('hidden');
  });
}

(function () {
  var html  = document.documentElement;
  var icon  = document.getElementById('theme-icon');
  var label = document.getElementById('theme-label');
  var btn   = document.getElementById('theme-toggle');

  function sync() {
    var isDark = html.classList.contains('dark');
    if (icon)  icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    if (label) label.textContent = isDark ? 'Thème clair' : 'Thème sombre';
  }

  sync();

  function toggle() {
    var isDark = html.classList.contains('dark');
    html.classList.toggle('dark', !isDark);
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
    sync();
  }

  if (btn) btn.addEventListener('click', toggle);

  document.addEventListener('keydown', function (e) {
    if (e.altKey && e.key === 't') toggle();
  });
})();

(function () {
  var MODELS = ['default', 'coupe', 'suv'];
  var LABELS = { default: 'Berline', coupe: 'Décapotable', suv: 'SUV' };
  var btn   = document.getElementById('car-model-toggle');
  var label = document.getElementById('car-model-label');

  function sync() {
    var model = localStorage.getItem('carModel') || 'default';
    if (label) label.textContent = LABELS[model];
  }

  sync();

  if (btn) btn.addEventListener('click', function () {
    var current = localStorage.getItem('carModel') || 'default';
    var next = MODELS[(MODELS.indexOf(current) + 1) % MODELS.length];
    localStorage.setItem('carModel', next);
    document.dispatchEvent(new Event('carModelChange'));
    sync();
  });
})();

(function () {
  var html  = document.documentElement;
  var btn   = document.getElementById('car-toggle');
  var label = document.getElementById('car-toggle-label');

  var modelBtn = document.getElementById('car-model-toggle');

  function sync() {
    var off = html.classList.contains('car-off');
    if (label)    label.textContent  = off ? 'Curseur voiture' : 'Curseur normal';
    if (modelBtn) { modelBtn.classList.toggle('hidden', off); modelBtn.classList.toggle('flex', !off); }
  }

  sync();

  if (btn) btn.addEventListener('click', function () {
    var off = html.classList.contains('car-off');
    html.classList.toggle('car-off', !off);
    localStorage.setItem('carCursor', !off ? 'off' : 'on');
    sync();
  });
})();
