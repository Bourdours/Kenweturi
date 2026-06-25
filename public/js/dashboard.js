function setDashboardRole(role) {
    localStorage.setItem('dashboardRole', role);
    applyDashboardRole(role);
}

function applyDashboardRole(role) {
    document.querySelectorAll('[data-role]').forEach(function (el) {
        el.style.display = el.dataset.role === role ? '' : 'none';
    });

    var activeClasses = ['bg-action', 'text-ink'];
    var inactiveClasses = ['text-muted'];

    var btnPassenger = document.getElementById('toggle-passenger');
    var btnDriver = document.getElementById('toggle-driver');

    if (role === 'passenger') {
        activeClasses.forEach(function (c) { btnPassenger.classList.add(c); });
        inactiveClasses.forEach(function (c) { btnPassenger.classList.remove(c); });
        activeClasses.forEach(function (c) { btnDriver.classList.remove(c); });
        inactiveClasses.forEach(function (c) { btnDriver.classList.add(c); });
    } else {
        activeClasses.forEach(function (c) { btnDriver.classList.add(c); });
        inactiveClasses.forEach(function (c) { btnDriver.classList.remove(c); });
        activeClasses.forEach(function (c) { btnPassenger.classList.remove(c); });
        inactiveClasses.forEach(function (c) { btnPassenger.classList.add(c); });
    }
}

applyDashboardRole(localStorage.getItem('dashboardRole') || 'driver');
