document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type="password"]').forEach((input) => {
    // avoid adding duplicate toggles
    if (input.nextElementSibling && input.nextElementSibling.classList && input.nextElementSibling.classList.contains('pwd-toggle')) return;

    // ensure there's a positioned parent (.input-group) so the absolute toggle positions correctly
    if (!input.parentElement || !input.parentElement.classList || !input.parentElement.classList.contains('input-group')) {
      const wrapper = document.createElement('div');
      wrapper.className = 'input-group';
      input.parentElement.insertBefore(wrapper, input);
      wrapper.appendChild(input);
    }

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pwd-toggle';
    btn.setAttribute('aria-pressed', 'false');
    btn.setAttribute('aria-label', 'Show password');
    btn.textContent = 'Show';

    btn.addEventListener('click', () => {
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      btn.textContent = isPwd ? 'Hide' : 'Show';
      btn.setAttribute('aria-pressed', String(isPwd));
      btn.setAttribute('aria-label', isPwd ? 'Hide password' : 'Show password');
    });

    // place the button right after the input
    input.insertAdjacentElement('afterend', btn);
  });
});
