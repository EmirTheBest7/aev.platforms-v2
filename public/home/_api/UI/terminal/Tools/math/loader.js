// Loads a calculator fragment (./functions/*.html) into #content and runs the fragment's own inline script —
// the same behaviour as the original loadExternalHTML(). Fragments are this bundle's static files only.
(function () {
  'use strict';
  function load(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4 || xhr.status !== 200) return;
      var content = document.getElementById('content');
      content.innerHTML = xhr.responseText;
      Array.prototype.forEach.call(content.getElementsByTagName('script'), function (old) {
        var script = document.createElement('script');
        script.text = old.innerHTML;
        document.head.appendChild(script).parentNode.removeChild(script);
      });
    };
    xhr.send();
  }
  document.addEventListener('click', function (event) {
    var trigger = event.target.closest && event.target.closest('[data-load]');
    if (trigger && /^\.\/functions\/[a-z-]+\.html$/.test(trigger.getAttribute('data-load'))) load(trigger.getAttribute('data-load'));
  });
})();
