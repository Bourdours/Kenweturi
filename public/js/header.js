document.getElementById('menu-toggle').addEventListener('click', function () {
  document.getElementById('mobile-menu').classList.toggle('hidden');
});

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
