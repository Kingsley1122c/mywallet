document.addEventListener('DOMContentLoaded', () => {
  const toggleLabels = {
    en: { show: 'Show', hide: 'Hide', showPassword: 'Show password', hidePassword: 'Hide password' },
    es: { show: 'Mostrar', hide: 'Ocultar', showPassword: 'Mostrar contrasena', hidePassword: 'Ocultar contrasena' },
    fr: { show: 'Afficher', hide: 'Masquer', showPassword: 'Afficher le mot de passe', hidePassword: 'Masquer le mot de passe' },
    de: { show: 'Anzeigen', hide: 'Ausblenden', showPassword: 'Passwort anzeigen', hidePassword: 'Passwort ausblenden' },
    it: { show: 'Mostra', hide: 'Nascondi', showPassword: 'Mostra password', hidePassword: 'Nascondi password' },
    pt: { show: 'Mostrar', hide: 'Ocultar', showPassword: 'Mostrar senha', hidePassword: 'Ocultar senha' },
    ko: { show: '표시', hide: '숨기기', showPassword: '비밀번호 표시', hidePassword: '비밀번호 숨기기' },
    ja: { show: '表示', hide: '非表示', showPassword: 'パスワードを表示', hidePassword: 'パスワードを非表示' },
    'zh-tw': { show: '顯示', hide: '隱藏', showPassword: '顯示密碼', hidePassword: '隱藏密碼' },
    ar: { show: 'إظهار', hide: 'إخفاء', showPassword: 'إظهار كلمة المرور', hidePassword: 'إخفاء كلمة المرور' },
    hi: { show: 'दिखाएं', hide: 'छुपाएं', showPassword: 'पासवर्ड दिखाएं', hidePassword: 'पासवर्ड छुपाएं' },
    ru: { show: 'Показать', hide: 'Скрыть', showPassword: 'Показать пароль', hidePassword: 'Скрыть пароль' },
    nl: { show: 'Tonen', hide: 'Verbergen', showPassword: 'Wachtwoord tonen', hidePassword: 'Wachtwoord verbergen' }
  };

  const getToggleLanguage = () => {
    const current = (window.i18n && window.i18n.currentLang) || localStorage.getItem('mw_lang') || 'en';
    return String(current).toLowerCase();
  };

  const translateToggle = (key, fallback) => {
    const lang = getToggleLanguage();
    const normalizedKey = key === 'show-label' ? 'show' : key === 'hide-label' ? 'hide' : key === 'show-password-label' ? 'showPassword' : 'hidePassword';
    const mapped = (toggleLabels[lang] && toggleLabels[lang][normalizedKey]) || (toggleLabels[lang.split('-')[0]] && toggleLabels[lang.split('-')[0]][normalizedKey]);
    if (mapped) {
      return mapped;
    }
    if (window.i18n && typeof window.i18n.t === 'function') {
      const translated = window.i18n.t(key);
      if (translated && translated !== key) {
        return translated;
      }
    }
    return fallback;
  };

  const syncToggleButton = (btn, input) => {
    const isVisible = input.type === 'text';
    btn.textContent = isVisible ? translateToggle('hide-label', 'Hide') : translateToggle('show-label', 'Show');
    btn.setAttribute('aria-label', isVisible ? translateToggle('hide-password-label', 'Hide password') : translateToggle('show-password-label', 'Show password'));
  };

  const refreshPasswordToggles = () => {
    document.querySelectorAll('.pwd-toggle').forEach((btn) => {
      const input = btn.previousElementSibling;
      if (input && (input.type === 'password' || input.type === 'text')) {
        syncToggleButton(btn, input);
      }
    });
  };

  if (typeof window !== 'undefined') {
    window.refreshPasswordToggles = refreshPasswordToggles;
  }

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
    btn.setAttribute('aria-label', translateToggle('show-password-label', 'Show password'));
    btn.textContent = translateToggle('show-label', 'Show');

    btn.addEventListener('click', () => {
      const isPwd = input.type === 'password';
      input.type = isPwd ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(isPwd));
      syncToggleButton(btn, input);
    });

    // place the button right after the input
    input.insertAdjacentElement('afterend', btn);
  });

  const langSelect = document.getElementById('lang-select') || document.querySelector('select[name="language"]');
  if (langSelect) {
    langSelect.addEventListener('change', () => {
      window.setTimeout(refreshPasswordToggles, 0);
    });
  }

  window.addEventListener('storage', (e) => {
    if (e.key === 'mw_lang') {
      refreshPasswordToggles();
    }
  });
});
