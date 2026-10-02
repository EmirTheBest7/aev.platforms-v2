// Domain check: asks GET /home/_api/tools/domain?name=… and shows the answer as text.
(function () {
  'use strict';
  var form = document.getElementById('domain-form');
  var input = document.getElementById('domain-name');
  var result = document.getElementById('domain-result');

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    result.textContent = 'Checking…';
    fetch('/home/_api/tools/domain?name=' + encodeURIComponent(input.value), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (!data.ok) { result.textContent = data.message || 'Could not check this name.'; return; }
        result.textContent = data.domain + (data.hasRecords ? ': has DNS records (already in use).' : ': no DNS records found — it may be free to register.');
      })
      .catch(function () { result.textContent = 'The check could not be completed. Try again later.'; });
  });
})();
