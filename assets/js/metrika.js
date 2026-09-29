(function () {
  'use strict';

  var YM_ID = 26808882;
  var pendingFormType = null;

  function azsReachGoal(target, extra) {
    var payload = extra || {};
    if (typeof ym === 'function') {
      ym(YM_ID, 'reachGoal', target);
      console.log('[YM] reachGoal OK', target, payload);
    } else {
      console.warn('[YM] счётчик не найден, цель не отправлена', target, payload);
    }
  }

  document.addEventListener('click', function (e) {
    var goalEl = e.target.closest('[data-ym-goal]');
    if (goalEl) {
      var goal = goalEl.getAttribute('data-ym-goal');
      var place = goalEl.closest('.header')
        ? 'header'
        : (goalEl.closest('.footer') ? 'footer' : 'news');
      console.log('[YM] клик', goal, place);
      azsReachGoal(goal, { place: place });
      return;
    }

    if (e.target.closest('.azs-buy-now')) {
      console.log('[YM] клик «Купить в один клик»');
      azsReachGoal('kupit-v-odin-klik', { place: 'buy-now' });
      return;
    }

    if (e.target.closest('[data-popup="popup_appended"] a.button-filled')) {
      console.log('[YM] клик «Оформить заказ» в модалке');
      azsReachGoal('klik-oformit-zakaz', { place: 'popup_appended' });
    }
  });

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form') || e.target;

    if (form && form.id === 'azs-order-form') {
      pendingFormType = 'order';
    } else if (form && form.matches && form.matches('.form[data-form-type]')) {
      pendingFormType = form.dataset.formType;
    } else {
      var typed = e.target.closest('.form[data-form-type]');
      pendingFormType = typed ? typed.dataset.formType : null;
    }

    console.log('[YM] submit, formType =', pendingFormType);
  }, true);

  if (typeof PopupManager === 'undefined' || typeof PopupManager.open !== 'function') {
    console.warn('[YM] PopupManager не найден');
    return;
  }

  var origOpen = PopupManager.open;
  PopupManager.open = function (name) {
    console.log('[YM] popup open', name, 'pending =', pendingFormType);

    if (name === 'popup_success' && pendingFormType === 'callback') {
      console.log('[YM] форма callback отправлена успешно');
      azsReachGoal('otpravka-formy-v-hedere', { formType: pendingFormType });
    }

    if (name === 'popup_success' && pendingFormType === 'consultation') {
      console.log('[YM] сквозная форма на главной отправлена успешно');
      azsReachGoal('otpravka-skvoznoy-formy', { formType: pendingFormType });
    }

    if (name === 'popup_success' && pendingFormType === 'news') {
      console.log('[YM] форма подписки отправлена успешно');
      azsReachGoal('otpravka-formy-o-podpiske', { formType: pendingFormType });
    }

    if (name === 'popup_success' && pendingFormType === 'order') {
      console.log('[YM] заказ из корзины отправлен успешно');
      azsReachGoal('oformit-zakaz-v-korzine', { formType: pendingFormType });
    }

    if (name === 'popup_success' || name === 'popup_error') {
      pendingFormType = null;
    }

    return origOpen.apply(this, arguments);
  };
})();