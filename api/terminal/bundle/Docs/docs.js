// Docs viewer: loads the HTML fragments of ./mds into the content pane.
// Port of the inline loadExternalHTML() of the original page. Fragments are the bundle's own static files and are
// inserted as markup only; the original also executed any <script> inside them (there are none) — not carried over.
(function () {
  'use strict';
  var pane = document.getElementById('content');
  if (!pane || !window.fetch) return;

  function load(url) {
    fetch(url, { credentials: 'same-origin' })
      .then(function (response) { return response.ok ? response.text() : Promise.reject(new Error('HTTP ' + response.status)); })
      .then(function (html) { pane.innerHTML = html; })
      .catch(function () { pane.textContent = 'This page could not be loaded.'; });
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest && event.target.closest('[data-doc]');
    if (link) load(link.getAttribute('data-doc'));
  });
  document.addEventListener('keydown', function (event) {
    if ((event.key === 'Enter' || event.key === ' ') && event.target.matches && event.target.matches('[data-doc]')) {
      event.preventDefault();
      load(event.target.getAttribute('data-doc'));
    }
  });
  load('./mds/original.html');
})();
