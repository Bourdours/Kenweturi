document.querySelectorAll('input, select').forEach(function (el) {
  el.addEventListener('invalid', function () {
    const v = el.validity;
    if (v.valueMissing)       el.setCustomValidity('Ce champ est obligatoire.');
    else if (v.typeMismatch && el.type === 'email') el.setCustomValidity('Veuillez saisir une adresse email valide.');
    else if (v.tooShort)      el.setCustomValidity('Minimum ' + el.minLength + ' caractères requis.');
    else if (v.patternMismatch) el.setCustomValidity('Format invalide.');
    else                      el.setCustomValidity('');
  });
  el.addEventListener('input', function () {
    el.setCustomValidity('');
  });
});

const pwInput = document.getElementById('password');

if (pwInput) {
  const bars = ['sb1', 'sb2', 'sb3', 'sb4'].map(id => document.getElementById(id));
  const crits = [
    { id: 'crit-length',  test: v => v.length >= 8 },
    { id: 'crit-upper',   test: v => /[A-Z]/.test(v) },
    { id: 'crit-number',  test: v => /[0-9]/.test(v) },
    { id: 'crit-special', test: v => /[^a-zA-Z0-9]/.test(v) },
  ].map(c => ({ ...c, el: document.getElementById(c.id) }));

  const barColors  = ['#D80B1C', '#C85028', '#E5C988', '#9DB387'];
  const emptyColor = 'rgba(239,234,224,0.08)';
  const metColor   = '#9DB387';

  pwInput.addEventListener('input', function () {
    const val = this.value;
    let score = 0;

    crits.forEach(({ el, test }) => {
      const met = val.length > 0 && test(val);
      if (met) score++;
      const icon = el.querySelector('i');
      if (met) {
        el.style.opacity = '1';
        el.style.color   = '#EFEAE0';
        icon.className   = 'fa-solid fa-circle-check w-3 text-center';
        icon.style.color = metColor;
      } else {
        el.style.opacity = '';
        el.style.color   = '';
        icon.className   = 'fa-regular fa-circle w-3 text-center';
        icon.style.color = '';
      }
    });

    const fillColor = score > 0 ? barColors[score - 1] : emptyColor;
    bars.forEach((bar, i) => {
      bar.style.background = i < score ? fillColor : emptyColor;
    });
  });
}

function togglePassword(id, btn) {
  const input = document.getElementById(id);
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
