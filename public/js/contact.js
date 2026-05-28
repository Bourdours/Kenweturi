const trigger = document.getElementById('subject-trigger');
const list    = document.getElementById('subject-list');
const input   = document.getElementById('subject-input');
const label   = document.getElementById('subject-label');
const chevron = document.getElementById('subject-chevron');

if (!trigger || !list || !input || !label || !chevron) return;

trigger.addEventListener('click', () => {
  const isOpen = list.style.display === 'block';
  list.style.display = isOpen ? 'none' : 'block';
  chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
});

list.querySelectorAll('.autocomplete-item').forEach(item => {
  item.addEventListener('click', () => {
    input.value = item.dataset.value;
    label.textContent = item.dataset.value;
    label.classList.remove('text-ink/30');
    label.classList.add('text-ink');
    list.style.display = 'none';
    chevron.style.transform = '';
  });
});

document.addEventListener('click', (e) => {
  if (!trigger.contains(e.target) && !list.contains(e.target)) {
    list.style.display = 'none';
    chevron.style.transform = '';
  }
});
