const swapBtn = document.getElementById('swapAddresses');
if (!swapBtn) return;
swapBtn.addEventListener('click', function () {
    const pairs = [
        ['startAddress', 'endAddress'],
        ['startAddressLng', 'endAddressLng'],
        ['startAddressLat', 'endAddressLat'],
    ];
    pairs.forEach(([a, b]) => {
        const elA = document.getElementById(a);
        const elB = document.getElementById(b);
        [elA.value, elB.value] = [elB.value, elA.value];
    });
});
