/* ALIEV.IO web terminal (retained part of the _api bundle).
 *
 * Port of the original UI/terminal/script.js. Same look, prompt, history and clock; the changes are:
 *  - the prompt user comes from GET /home/_api/me (the original read it from a PHP session echoed into the page),
 *  - the game launchers, the store/v2/gtin/admin entries and the dead helpers were removed with the
 *    features they opened (see docs/ARCHITECTURE.md §6),
 *  - everything typed or fetched is inserted as text, never as HTML; `cat` only reads same-origin paths,
 *  - ?Page= / ?Tools= redirects accept only the pages that exist.
 */
$(function () {
  var promptUser = 'user';
  var render = function () { $('.prompt').text(promptUser + '@eros:~$'); };
  render();

  if (window.fetch) {
    fetch('/home/_api/me', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (me) {
        if (me && me.authenticated && typeof me.username === 'string') {
          promptUser = me.username.toLowerCase().split(' ')[0];
          render();
        }
      })
      .catch(function () { /* stay anonymous */ });
  }

  var term = new Terminal('#input-line .cmdline', '#container output');
  term.init();

  // Update the clock every second
  setInterval(function () {
    function r(cls, deg) {
      $('.' + cls).attr('transform', 'rotate(' + deg + ' 50 50)');
    }
    var d = new Date();
    r('sec', 6 * d.getSeconds());
    r('min', 6 * d.getMinutes());
    r('hour', 30 * (d.getHours() % 12) + d.getMinutes() / 2);
  }, 1000);
});

var util = util || {};
util.toArray = function (list) {
  return Array.prototype.slice.call(list || [], 0);
};

var Terminal = Terminal || function (cmdLineContainer, outputContainer) {
  var cmdLine_ = document.querySelector(cmdLineContainer);
  var output_ = document.querySelector(outputContainer);

  // Pages that exist; ?Page=<name> and ?Tools=<name> may only point at these.
  var PAGES_ = ['4ukraine', 'donate', 'valentine'];
  var TOOLS_ = ['currency', 'crypto', 'domain', 'editor', 'qr', 'math'];
  var CMDS_ = ['cat', 'clear', 'clock', 'color', 'date', 'docs', 'echo', 'help', 'uname', 'whoami'];

  var urlVar = location.search.replace('?', '').split('=');
  if (urlVar[0] === 'Page' && PAGES_.indexOf(urlVar[1]) !== -1) {
    window.location.href = 'Page/' + urlVar[1] + '/';
  } else if (urlVar[0] === 'Tools' && TOOLS_.indexOf(urlVar[1]) !== -1) {
    window.location.href = 'Tools/' + urlVar[1] + '/';
  }

  var history_ = [];
  var histpos_ = 0;
  var histtemp_ = 0;

  window.addEventListener('click', function () {
    cmdLine_.focus();
  }, false);

  cmdLine_.addEventListener('click', function () { this.value = this.value; }, false);
  cmdLine_.addEventListener('keydown', historyHandler_, false);
  cmdLine_.addEventListener('keydown', processNewCommand_, false);

  function historyHandler_(e) {
    if (history_.length) {
      if (e.keyCode == 38 || e.keyCode == 40) {
        if (history_[histpos_]) {
          history_[histpos_] = this.value;
        } else {
          histtemp_ = this.value;
        }
      }

      if (e.keyCode == 38) { // up
        histpos_--;
        if (histpos_ < 0) {
          histpos_ = 0;
        }
      } else if (e.keyCode == 40) { // down
        histpos_++;
        if (histpos_ > history_.length) {
          histpos_ = history_.length;
        }
      }

      if (e.keyCode == 38 || e.keyCode == 40) {
        this.value = history_[histpos_] ? history_[histpos_] : histtemp_;
        this.value = this.value; // Sets cursor to end of input.
      }
    }
  }

  function processNewCommand_(e) {
    if (e.keyCode == 9) { // tab
      e.preventDefault();
      return;
    }
    if (e.keyCode != 13) { // enter
      return;
    }

    // Save shell history.
    if (this.value) {
      history_[history_.length] = this.value;
      histpos_ = history_.length;
    }

    // Duplicate current input and append to output section.
    var line = this.parentNode.parentNode.cloneNode(true);
    line.removeAttribute('id');
    line.classList.add('line');
    var input = line.querySelector('input.cmdline');
    input.autofocus = false;
    input.readOnly = true;
    output_.appendChild(line);

    var cmd = '';
    var args = [];
    if (this.value && this.value.trim()) {
      args = this.value.split(' ').filter(function (val) {
        return val;
      });
      cmd = args[0].toLowerCase();
      args = args.splice(1); // Remove cmd from arg list.
    }

    switch (cmd) {
      case 'cat':
        var url = args.join(' ');
        if (!url) {
          text('Usage: cat <path>   (same-origin files only)');
          break;
        }
        var target;
        try {
          target = new URL(url, window.location.href);
        } catch (err) {
          text('cat: invalid path');
          break;
        }
        if (target.origin !== window.location.origin) {
          text('cat: only files of this site can be read');
          break;
        }
        $.get(target.pathname, function (data) {
          pre(typeof data === 'string' ? data : JSON.stringify(data, null, 2));
        }).fail(function () {
          text('cat: ' + url + ': cannot read');
        });
        break;
      case 'color':
        $('.prompt').css('color', 'aqua');
        this.value = '';
        return;
      case 'clear':
        output_.innerHTML = '';
        this.value = '';
        return;
      case 'clock':
        var clock = $('.clock-container').first().clone();
        clock.css('display', 'inline-block');
        output_.appendChild(clock[0]);
        break;
      case 'date':
        text(String(new Date()));
        break;
      case 'whoami':
        text($('.prompt').text().split('@')[0]);
        break;
      case 'docs':
        text('[AEV|Docs] is opened in a new window!');
        window.open('../../Docs/', '_blank', 'noopener');
        break;
      case 'echo':
        text(args.join(' '));
        break;
      case 'help':
        list('Commands', CMDS_);
        list('Tools', TOOLS_);
        break;
      case 'uname':
        text(navigator.appVersion);
        break;
      case 'gpu':
        var d = new Date();
        d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000));
        document.cookie = 'gpu_core=true; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
        text('GPU Core status: true');
        break;
      default:
        if (TOOLS_.indexOf(cmd) !== -1) {
          window.location.href = 'Tools/' + cmd + '/';
          break;
        }
        if (cmd === 'donate') {
          window.location.href = 'Page/donate/';
          break;
        }
        if (cmd) {
          text(cmd + ': command not found. Type "help" for all available commands');
        }
    }

    window.scrollTo(0, getDocHeight_());
    this.value = ''; // Clear/setup line for next input.
  }

  // Output helpers: everything is inserted as text.
  function text(value) {
    var p = document.createElement('div');
    p.textContent = value;
    output_.appendChild(p);
  }

  function pre(value) {
    var el = document.createElement('pre');
    el.textContent = value;
    output_.appendChild(el);
  }

  function list(title, items) {
    var box = document.createElement('div');
    box.className = 'ls-files';
    box.appendChild(document.createTextNode(title + ':'));
    items.forEach(function (item) {
      box.appendChild(document.createElement('br'));
      box.appendChild(document.createTextNode(item));
    });
    output_.appendChild(box);
  }

  // Cross-browser impl to get document's height.
  function getDocHeight_() {
    var d = document;
    return Math.max(
      Math.max(d.body.scrollHeight, d.documentElement.scrollHeight),
      Math.max(d.body.offsetHeight, d.documentElement.offsetHeight),
      Math.max(d.body.clientHeight, d.documentElement.clientHeight)
    );
  }

  return {
    init: function () {
      text('');
    },
    output: text
  };
};
