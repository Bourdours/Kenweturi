function toggleMessage(btn) {
    const msg = btn.nextElementSibling;
    const icon = btn.querySelector('i');
    const span = btn.querySelector('span');
    msg.classList.toggle('hidden');
    icon.style.transform = msg.classList.contains('hidden') ? '' : 'rotate(180deg)';
    span.textContent = msg.classList.contains('hidden') ? 'Voir le message' : 'Masquer le message';
}