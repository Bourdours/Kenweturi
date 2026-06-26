document.querySelectorAll('.use-favorite-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (input) input.value = btn.dataset.address;
    });
});
