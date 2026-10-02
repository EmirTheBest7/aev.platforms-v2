// Click ripple of the ALIEV button (.ripple-button): sets the click position and plays the "pulse" animation.
// Port of the ripple block of the original _assets/js/core.js, delegated so it works for every button on the page.
(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var button = event.target.closest && event.target.closest('.ripple-button');
    if (!button) return;
    var rect = button.getBoundingClientRect();
    button.style.setProperty('--x', (event.clientX - rect.left) + 'px');
    button.style.setProperty('--y', (event.clientY - rect.top) + 'px');
    button.classList.add('pulse');
    button.addEventListener('animationend', function () { button.classList.remove('pulse'); }, { once: true });
  });
})();
