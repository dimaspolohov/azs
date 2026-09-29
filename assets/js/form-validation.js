(function () {
  'use strict';

 var MESSAGES = {
  name: 'Укажите имя',
  phone: 'Укажите номер телефона',
  email: 'Укажите корректный email',
  privacy: 'Необходимо принять условия обработки персональных данных',
  mailing: 'Необходимо дать согласие на получение рекламных и информационных сообщений'
};

  function getKey(field) {
    if (field.getAttribute('data-validate')) return field.getAttribute('data-validate');
    if (field.type === 'checkbox') return 'privacy';
    if (field.type === 'email') return 'email';
    if (field.type === 'tel') return 'phone';
    return 'name';
  }

  function getWrap(field) {
    if (field.type === 'checkbox') {
      return field.closest('.form--policy') || field.closest('.form--input');
    }
    return field.closest('.form--input');
  }

  function getErrorEl(wrap) {
    var el = wrap.querySelector('.form--error');
    if (el) return el;
    el = document.createElement('span');
    el.className = 'form--error';
    el.setAttribute('role', 'alert');
    wrap.appendChild(el);
    return el;
  }

  function showError(field, text) {
    var wrap = getWrap(field);
    if (!wrap) return;
    wrap.classList.add('is-invalid');
    field.setAttribute('aria-invalid', 'true');
    getErrorEl(wrap).textContent = text;
  }

  function clearError(field) {
    var wrap = getWrap(field);
    if (!wrap) return;
    wrap.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    var el = wrap.querySelector('.form--error');
    if (el) el.textContent = '';
  }

  function isEmpty(field) {
    if (field.type === 'checkbox') return !field.checked;
    return !(field.value || '').trim();
  }

  function isPhoneOk(field) {
    if (field.inputmask) return field.inputmask.isComplete();
    var digits = (field.value || '').replace(/\D/g, '');
    return digits.length >= 11;
  }

  function isEmailOk(field) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((field.value || '').trim());
  }

  function isFieldValid(field) {
    if (field.disabled || field.type === 'hidden') return true;
    if (!field.required && isEmpty(field)) return true;

    if (isEmpty(field)) return false;
    if (getKey(field) === 'phone') return isPhoneOk(field);
    if (getKey(field) === 'email') return isEmailOk(field);
    return true;
  }

  function validateForm(form) {
    var fields = form.querySelectorAll('input, textarea, select');
    var firstInvalid = null;

    fields.forEach(function (field) {
      if (field.type === 'submit' || field.type === 'button' || field.type === 'hidden') return;

      if (isFieldValid(field)) {
        clearError(field);
        return;
      }

      showError(field, MESSAGES[getKey(field)] || 'Заполните поле');
      if (!firstInvalid) firstInvalid = field;
    });

    if (firstInvalid) firstInvalid.focus();
    return !firstInvalid;
  }

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form.form');
    if (!form) return;
    if (!validateForm(form)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  }, true);

  document.addEventListener('input', function (e) {
    var field = e.target;
    if (!field.closest || !field.closest('form.form')) return;
    if (isFieldValid(field)) clearError(field);
  });

  document.addEventListener('change', function (e) {
    var field = e.target;
    if (!field.closest || !field.closest('form.form')) return;
    if (field.type === 'checkbox' && isFieldValid(field)) clearError(field);
  });
})();