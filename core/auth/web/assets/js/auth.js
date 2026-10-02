// Auth page: show/hide the sign-up password. (The Log In / Sign Up flip is pure CSS on #reg-log.)
// Replaces the original script's inline handlers, its wallet "Sign-In Options" panel and its nickname/email
// availability lookups (those revealed which emails were registered).
(function () {
  'use strict';
  var eye = document.getElementById('eye');
  var field = document.getElementById('regpass');
  if (!eye || !field) return;
  eye.addEventListener('click', function () {
    var show = field.type === 'password';
    field.type = show ? 'text' : 'password';
    eye.classList.toggle('uil-eye', show);
    eye.classList.toggle('uil-eye-slash', !show);
    eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });
})();
