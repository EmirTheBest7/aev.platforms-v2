/* ALIEV.IO main page — behaviour layer.
 *
 * Port of the inline <script> blocks of the legacy page/main/index.php and the page parts of
 * _assets/js/core.js (preloader, title swap, ripple, menu toggle). Same behaviour, same jQuery
 * effects (fade/slide/toggle timings); the differences are:
 *   - no inline handlers: everything is wired by delegation on data-* hooks (CSP forbids inline JS),
 *   - every feature is initialised through safe(), so one failing/optional feature can never stop
 *     the others, and the preloader is released by a fail-safe independent of every other module,
 *   - the ticker reads /api/prices (same origin) instead of a third-party API.
 * Loaded after jQuery, three.js and sections.js (Hammer + the section scroller).
 */
(function () {
  'use strict';

  var $ = window.jQuery;
  var warn = function (name, err) { if (window.console) console.warn('[home] ' + name + ' disabled:', err && err.message ? err.message : err); };
  var safe = function (name, fn) { try { fn(); } catch (err) { warn(name, err); } };
  var notify = function (title, text, seconds) { if (window.notis) window.notis.create(title, text, seconds || 4); };

  /* ---------------------------------------------------------------- preloader */
  // Legacy: shown for 2 s after DOM ready, then faded out. Here it is also released by the load
  // event and, independently, by a hard timeout, so no optional resource can keep it up.
  safe('preloader', function () {
    var screen = document.querySelector('.loading-screen');
    if (!screen) return;
    var released = false;
    var release = function () {
      if (released) return;
      released = true;
      screen.style.zIndex = '15';
      $(screen).fadeOut(400, function () { screen.setAttribute('hidden', ''); screen.style.display = 'none'; });
    };
    var shownSince = Date.now();
    var minimum = 1200; // let the logo animation be seen
    var whenReady = function () { setTimeout(release, Math.max(0, minimum - (Date.now() - shownSince))); };
    if (document.readyState === 'complete') whenReady();
    else window.addEventListener('load', whenReady, { once: true });
    setTimeout(release, 5000); // fail-safe: never wait longer than this
  });

  /* ------------------------------------------------------------ title swap */
  safe('title', function () {
    var base = document.title;
    var alternatives = ['ΛΞV | Coding is art.', 'ΛΞV | Digital Studio.'];
    window.addEventListener('focus', function () { document.title = base; });
    window.addEventListener('blur', function () { document.title = alternatives[Math.floor(Math.random() * alternatives.length)]; });
  });

  /* ----------------------------------------------------------- ripple button */
  safe('ripple', function () {
    document.addEventListener('click', function (e) {
      var button = e.target.closest && e.target.closest('.ripple-button');
      if (!button) return;
      var rect = button.getBoundingClientRect();
      button.style.setProperty('--x', (e.clientX - rect.left) + 'px');
      button.style.setProperty('--y', (e.clientY - rect.top) + 'px');
      button.classList.add('pulse');
      button.addEventListener('animationend', function () { button.classList.remove('pulse'); }, { once: true });
    });
  });

  /* ------------------------------------------------------- navbar + panels */
  var navbar = document.getElementById('navbarCollapse');
  var toggle = document.getElementById('toggle');
  var panels = {
    menu: { x: '0px', y: '450px', z: '-400px' },
    widgets: { x: '-400px', y: '0px', z: '-400px' },
    settings: { x: '-400px', y: '-450px', z: '0px' }
  };

  function setMenu(open) {
    if (!toggle || !navbar) return;
    toggle.classList.toggle('is-active', open);
    navbar.classList.toggle('is-active', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function showPanel(name) {
    var p = panels[name];
    if (!p) return;
    var x = document.getElementById('login1');
    var y = document.getElementById('register1');
    var z = document.getElementById('settings1');
    if (!x || !y || !z) return;
    x.style.left = p.x; y.style.left = p.y; z.style.left = p.z;
    if (name === 'widgets') {
      $('#ext-sign-in-content').fadeIn(1500);
      $('#ext-settings-content').fadeOut();
      document.querySelectorAll('iframe[data-src]').forEach(function (f) { f.src = f.getAttribute('data-src'); f.removeAttribute('data-src'); });
    } else if (name === 'settings') {
      $('#ext-settings-content').fadeIn(1500);
      $('#ext-sign-in-content').fadeOut();
    } else {
      $('#ext-sign-in-content').fadeOut();
      $('#ext-settings-content').fadeOut();
    }
  }

  safe('navbar', function () {
    $('#ext-sign-in-content').hide();
    $('#ext-settings-content').hide();

    if (toggle) toggle.addEventListener('click', function () { setMenu(!navbar.classList.contains('is-active')); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navbar && navbar.classList.contains('is-active')) { setMenu(false); showPanel('menu'); if (toggle) toggle.focus(); }
    });
    document.addEventListener('click', function (e) {
      var btn = e.target.closest && e.target.closest('[data-panel]');
      if (btn) { e.preventDefault(); showPanel(btn.getAttribute('data-panel')); }
    });

    // Cards in the navbar that jump to a page section (Hire / Works) behave like the side navigation.
    document.addEventListener('click', function (e) {
      var link = e.target.closest && e.target.closest('a[data-section]');
      if (!link) return;
      var item = document.querySelector('.side-nav li[data-section="' + link.getAttribute('data-section') + '"]');
      if (!item) return;
      e.preventDefault();
      setMenu(false);
      item.click();
    });
    // The menu's Re:Search field only opens the Spotlight (it is not typed into): Enter/focus-by-keyboard opens it too.
    var menuSearch = document.querySelector('.m-spotlight-search');
    if (menuSearch) menuSearch.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key.length === 1) { e.preventDefault(); menuSearch.click(); }
    });
    // Keyboard access for the side navigation (<li role="button">).
    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Enter' || e.key === ' ') && e.target.matches && e.target.matches('.side-nav li[role="button"], .setting[role="button"]')) {
        e.preventDefault();
        e.target.click();
      }
    });

    // Accordion (legacy Accordion plugin, single-open).
    $('#aDHieSVT .aDHieSVT-link').on('click', function () {
      var $next = $(this).next();
      $next.slideToggle();
      $(this).parent().toggleClass('aDHieSVT-open');
      $('#aDHieSVT .aDHieSVT-submenu').not($next).slideUp().parent().removeClass('aDHieSVT-open');
    });
  });

  /* ---------------------------------------------- date widget (client clock) */
  safe('date', function () {
    var now = new Date();
    var weekday = document.getElementById('widget_weekday');
    var daymonth = document.getElementById('widget_daymonth');
    if (weekday) weekday.textContent = now.toLocaleDateString('en-us', { weekday: 'long' });
    if (daymonth) daymonth.textContent = now.toLocaleDateString('en-us', { day: 'numeric' }) + '. ' + now.toLocaleDateString('en-us', { month: 'long' });
  });

  /* ------------------------------------------- app launcher + profile panel */
  safe('launcher', function () {
    var launcher = $('.aev-app-launcher');
    var profile = $('.aev-profile-options');
    var appsBtn = $('.aev-apps-menu');
    var badgeBtn = $('.aev-user-badge');
    var sync = function () {
      appsBtn.attr('aria-expanded', launcher.is(':visible') ? 'true' : 'false');
      badgeBtn.attr('aria-expanded', profile.is(':visible') ? 'true' : 'false');
    };

    appsBtn.on('click', function (e) { e.stopPropagation(); profile.hide(); launcher.toggle(); sync(); });
    badgeBtn.on('click', function (e) { e.stopPropagation(); launcher.hide(); profile.toggle(); sync(); });
    $(document).on('click', function () { launcher.hide(); profile.hide(); sync(); });
    launcher.on('click', function (e) { e.stopPropagation(); });
    profile.on('click', function (e) { e.stopPropagation(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') { launcher.hide(); profile.hide(); sync(); } });

    // Notifications fade away while a panel is open (legacy behaviour).
    $('.aev-apps-menu, .aev-user-badge').on('click', function () { $('.aev-notifications').fadeOut(1000); });
    $(document).on('click', function (e) {
      if (!$(e.target).closest('.aev-apps-menu, .aev-user-badge').length) $('.aev-notifications').fadeIn(1000);
    });
  });

  /* --------------------------------------------------- language dropdown */
  safe('dropdown', function () {
    $('select').each(function (i, select) {
      if ($(this).next().hasClass('dropdown-select')) return;
      $(this).after('<div class="dropdown-select wide ' + ($(this).attr('class') || '') + '" tabindex="0" role="listbox"><span class="current"></span><div class="list"><ul></ul></div></div>');
      var dropdown = $(this).next();
      var selected = $(this).find('option:selected');
      dropdown.find('.current').text(selected.data('display-text') || selected.text());
      $(select).find('option').each(function (j, o) {
        var li = $('<li class="option" role="option"></li>').text($(o).text())
          .attr('data-value', $(o).val()).attr('data-display-text', $(o).data('display-text') || '');
        if ($(o).is(':selected')) li.addClass('selected');
        dropdown.find('ul').append(li);
      });
    });
    var search = $('<div class="dd-search"><input autocomplete="off" class="dd-searchbox" type="text" aria-label="Filter languages"></div>');
    $('.dropdown-select ul').before(search);
    $(document).on('input', '.dd-searchbox', function () {
      var v = $(this).val().toLowerCase();
      $(this).closest('.dropdown-select').find('li').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(v) > -1); });
    });

    $(document).on('click', '.dropdown-select', function (event) {
      if ($(event.target).hasClass('dd-searchbox')) return;
      $('.dropdown-select').not($(this)).removeClass('open');
      $(this).toggleClass('open');
      if ($(this).hasClass('open')) { $(this).find('.option').attr('tabindex', 0); $(this).find('.selected').focus(); }
      else { $(this).find('.option').removeAttr('tabindex'); $(this).focus(); }
    });
    $(document).on('click', function (event) {
      if ($(event.target).closest('.dropdown-select').length === 0) {
        $('.dropdown-select').removeClass('open');
        $('.dropdown-select .option').removeAttr('tabindex');
      }
    });
    $(document).on('click', '.dropdown-select .option', function () {
      $(this).closest('.list').find('.selected').removeClass('selected');
      $(this).addClass('selected');
      var text = $(this).data('display-text') || $(this).text();
      $(this).closest('.dropdown-select').find('.current').text(text);
      $(this).closest('.dropdown-select').prev('select').val($(this).data('value')).trigger('change');
    });
    $(document).on('keydown', '.dropdown-select', function (event) {
      if ($(event.target).hasClass('dd-searchbox')) return;
      var focused = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
      if (event.keyCode === 13) {
        if ($(this).hasClass('open')) focused.trigger('click'); else $(this).trigger('click');
        return false;
      } else if (event.keyCode === 40) {
        if (!$(this).hasClass('open')) $(this).trigger('click'); else focused.next().focus();
        return false;
      } else if (event.keyCode === 38) {
        if (!$(this).hasClass('open')) $(this).trigger('click'); else focused.prev().focus();
        return false;
      } else if (event.keyCode === 27) {
        if ($(this).hasClass('open')) $(this).trigger('click');
        return false;
      }
    });
    // The language list is shown as designed; translations are not implemented yet (honest feedback).
    $('#language').on('change', function () {
      if (this.value !== 'en') notify('Language', 'Translations are not available yet — the site stays in English.', 4);
    });
  });

  /* ------------------------------------------------------- data-action hooks */
  var deferredInstall = null;
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredInstall = e;
    var btn = document.querySelector('[data-action="pwa-install"]');
    if (btn) btn.hidden = false;
  });

  var actions = {
    'open-launcher': function (e) { e.preventDefault(); setMenu(false); $('.aev-app-launcher').show(); $('.aev-profile-options').hide(); },
    'open-spotlight': function (e) {
      e.preventDefault();
      var pop = document.getElementById('spotlight');
      var opts = document.getElementById('spotlight-options');
      if (pop && pop.showPopover) { pop.showPopover(); if (opts && opts.showPopover) opts.showPopover(); }
      $('.aev-app-launcher').hide();
    },
    fullscreen: function () {
      if (!document.fullscreenEnabled) { notify('Fullscreen', 'Your browser does not allow fullscreen here.', 3); return; }
      if (!document.fullscreenElement) document.documentElement.requestFullscreen(); else document.exitFullscreen();
    },
    pwa: function () { setMenu(false); window.location.hash = '#RccSCT7-open'; },
    'close-pwa': function () { window.location.hash = ''; },
    'pwa-install': function () {
      if (!deferredInstall) return;
      deferredInstall.prompt();
      deferredInstall.userChoice.then(function () { deferredInstall = null; });
    },
    shortcuts: function () { notify('Shortcuts', 'Ctrl/⌘ + J — open Spotlight search · ↑ ↓ — move through the sections · Esc — close menus', 8); },
    'functional-key': function () { notify('Functional key', 'Key verification is not available yet.', 4); }
  };

  safe('actions', function () {
    document.addEventListener('click', function (e) {
      var node = e.target.closest && e.target.closest('[data-action]');
      if (!node) return;
      var fn = actions[node.getAttribute('data-action')];
      if (fn) fn(e, node);
    });
    // Destinations that are not rebuilt yet keep their control but answer honestly.
    document.addEventListener('click', function (e) {
      var node = e.target.closest && e.target.closest('[data-soon]');
      if (!node) return;
      e.preventDefault();
      notify(node.getAttribute('data-soon'), 'This section is not available yet.', 4);
    });
  });

  /* -------------------------------------------------------- crypto ticker */
  safe('ticker', function () {
    var cells = document.querySelectorAll('[data-price]');
    if (!cells.length || !window.fetch) return;
    var fmt = function (n) { return n >= 100 ? Math.round(n).toString() : n >= 1 ? n.toFixed(2) : n.toFixed(4); };
    var refresh = function () {
      fetch('/api/prices', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function (data) {
          cells.forEach(function (cell) {
            var p = data.prices && data.prices[cell.getAttribute('data-price')];
            cell.textContent = p && typeof p.usd === 'number' ? fmt(p.usd) : '—';
            if (p) cell.setAttribute('data-state', p.state);
          });
        })
        .catch(function (err) { warn('ticker refresh', err); }); // keeps the last shown values
    };
    refresh();
    setInterval(refresh, 60000);
  });

  /* ----------------------------------------------------------- hire form */
  safe('hire', function () {
    var form = document.querySelector('[data-hire-form]');
    if (!form || !window.fetch || !window.FormData) return;
    var message = form.querySelector('[data-hire-message]');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var submit = form.querySelector('input[type="submit"]');
      if (submit) submit.disabled = true;
      if (message) message.textContent = 'Sending…';
      fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return { ok: false, errors: { form: 'Unexpected response.' } }; }); })
        .then(function (data) {
          if (data.ok) {
            form.reset();
            $(form).find('input').removeClass('has-value');
            if (message) message.textContent = 'Request sent. Reference ' + data.reference + '. We will get back to you.';
          } else if (message) {
            message.textContent = Object.keys(data.errors || {}).map(function (k) { return data.errors[k]; }).join(' ') || 'Could not send the request.';
          }
        })
        .catch(function () { if (message) message.textContent = 'Network error — please try again.'; })
        .then(function () { if (submit) submit.disabled = false; });
    });
  });

  /* ------------------------------------------------------------- intergram */
  safe('intergram', function () {
    var node = document.getElementById('aev-intergram');
    if (!node) return; // not configured: the chat stays out (no placeholder id)
    var cfg = JSON.parse(node.textContent);
    window.intergramId = cfg.chatId;
    window.intergramServer = cfg.server;
    window.intergramCustomizations = {
      titleOpen: cfg.titleOpen, introMessage: cfg.intro, mainColor: cfg.mainColor,
      alwaysUseFloatingButton: true, closedStyle: 'button', disableLoadmill: true
    };
    var s = document.createElement('script');
    s.src = '/build/vendor/intergram/widget.js';
    s.async = true;
    s.onerror = function () { warn('intergram', 'widget script unavailable'); };
    document.body.appendChild(s);
  });
})();
