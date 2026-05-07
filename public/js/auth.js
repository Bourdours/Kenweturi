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
