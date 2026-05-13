document.getElementById('menu-toggle').addEventListener('click', function () {
  document.getElementById('mobile-menu').classList.toggle('hidden');
});

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

  if (btn) btn.addEventListener('click', function () {
    var isDark = html.classList.contains('dark');
    html.classList.toggle('dark', !isDark);
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
    sync();
  });
})();
