$(function () {

  // Set the command-line prompt to include the user's IP Address
  //$('.prompt').html('[' + codehelper_ip["IP"] + '@HTML5] # ');
  $('.prompt').html(name);

  // Initialize a new terminal object
  var term = new Terminal('#input-line .cmdline', '#container output');
  term.init();

  // Update the clock every second
  setInterval(function() {
    function r(cls, deg) {
      $('.' + cls).attr('transform', 'rotate('+ deg +' 50 50)')
    }
    var d = new Date()
    r("sec", 6*d.getSeconds())  
    r("min", 6*d.getMinutes())
    r("hour", 30*(d.getHours()%12) + d.getMinutes()/2)
  }, 1000);

});


var util = util || {};
util.toArray = function (list) {
  return Array.prototype.slice.call(list || [], 0);
};

var Terminal = Terminal || function (cmdLineContainer, outputContainer) {
  window.URL = window.URL || window.webkitURL;
  window.requestFileSystem = window.requestFileSystem || window.webkitRequestFileSystem;

  var cmdLine_ = document.querySelector(cmdLineContainer);
  var output_ = document.querySelector(outputContainer);

  // Error handler
  /*
  const error_code = new URLSearchParams(window.location.search).get('0x');
  if (error_code !== null) {
    window.location.href = 'Admin/error/?0x=' +error_code;
    window.history.pushState({}, document.title, window.location.pathname);
  }*/

  // URL Redirects
  const urlVar = location.search.replace('?', '').split('=');
  switch(urlVar[0]) {
    case 'Page':
      window.location.href = urlVar[0]+'/'+urlVar[1];
      break;
    case 'Tools':
      window.location.href = urlVar[0]+'/'+urlVar[1];
      break;
    case '0x': // Error
      window.location.href = 'Admin/error/?'+urlVar[0]+'=' + urlVar[1];
      window.history.pushState({}, document.title, window.location.pathname);
      break;
    default:
      break;
  }


  const CMDS_ = [
    'cat', 'clear', 'clock', 'date', 'echo', 'help', 'uname', 'whoami', 'controls', 'v2', 'store'
  ];

  const GAMES_ = [
    'pacman', 'mario', 'dino', 'dino3d', 'Pong', 'Rubik', 'Blocks', 'crossy', 'sticky', 'rabbit', 'snake'
  ];

  const TOOLS_ = [
    'currency', 'crypto', 'domain', 'editor', 'donate', 'qr', 'math'
  ];

  const ADMIN_ = [
    'resume', 'bday'
  ];

  var fs_ = null;
  var cwd_ = null;
  var history_ = [];
  var histpos_ = 0;
  var histtemp_ = 0;

  window.addEventListener('click', function (e) {
    cmdLine_.focus();
  }, false);

  cmdLine_.addEventListener('click', inputTextClick_, false);
  cmdLine_.addEventListener('keydown', historyHandler_, false);
  cmdLine_.addEventListener('keydown', processNewCommand_, false);

  //
  function inputTextClick_(e) {
    this.value = this.value;
  }

  //
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
      // Implement tab suggest.
    } else if (e.keyCode == 13) { // enter
      // Save shell history.
      if (this.value) {
        history_[history_.length] = this.value;
        histpos_ = history_.length;
      }

      // Duplicate current input and append to output section.
      var line = this.parentNode.parentNode.cloneNode(true);
      line.removeAttribute('id')
      line.classList.add('line');
      var input = line.querySelector('input.cmdline');
      input.autofocus = false;
      input.readOnly = true;
      output_.appendChild(line);

      if (this.value && this.value.trim()) {
        var args = this.value.split(' ').filter(function (val, i) {
          return val;
        });
        var cmd = args[0].toLowerCase();
        args = args.splice(1); // Remove cmd from arg list.
      }


      // CMDs
      switch (cmd) {
        case 'cat':
          var url = args.join(' ');
          if (!url) {
            output('Usage: ' + cmd + 'aliev.io' + '</br>');
            output('Example: ' + cmd + 'aliev.io');
            break;
          }
          $.get(url, function (data) {
            var encodedStr = data.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
              return '&>' + i.charCodeAt(0) + ';';
            });
            output('<pre>' + encodedStr + '</pre>');
          });
          break;
        case 'color':
          $('.prompt').attr('style', 'color: aqua;') // Aqua
          this.value = '';
          return;
        case 'clear':
          output_.innerHTML = '';
          this.value = '';
          return;
        case 'clock':
          var appendDiv = jQuery($('.clock-container')[0].outerHTML);
          appendDiv.attr('style', 'display:inline-block');
          output_.appendChild(appendDiv[0]);
          break;
        case 'date':
          output(new Date());
          break;
        case 'whoami':
          output('user');
          break;
        case 'store':
        output(window.location.href = 'Page/store/');
          break;
        case 'docs':
          output('[AEV|Docs] is openned in new window!');
          output(window.open("../../Docs", "_blank"));
          break;

          // Games
        case 'pacman':
          output(window.location.href = 'Games/PacMan/');
          break;
        case 'doom':
          output(window.location.href = 'Games/doom/');
          break;
        case 'dino':
          output(window.location.href = 'Games/Dino/');
          break;
        case 'dino3d':
          output(window.location.href = 'Games/Dino3D/');
          break;
        case 'pong':
          output(window.location.href = 'Games/Pong/');
          break;
        case 'rubik':
          output(window.location.href = 'Games/Rubik/');
          break;
        case 'blocks':
          output(window.location.href = 'Games/Blocks/');
          break;
        case 'crossy':
          output(window.location.href = 'Games/crossy/');
          break;
        case 'sticky':
          output(window.location.href = 'Games/sticky/');
          break;
        case 'rabbit':
          output(window.location.href = 'Games/rabbit/');
          break;
        case 'snake':
          output(window.location.href = 'Games/snake/');
          break;


          // /Games

        case 'echo':
          output(args.join(' '));
          break;
        case 'help':
          output('<div class="ls-files">Commands:<br>' + CMDS_.join('<br>') + '</div>');
          output('<div class="ls-files">Games:<br>' + GAMES_.join('<br>') + '</div>');
          output('<div class="ls-files">Tools:<br>' + TOOLS_.join('<br>') + '</div>');
          break;

        /* Admin Tools */
        case 'help_emir':
          output('<div class="ls-files">Admin:<br>' + ADMIN_.join('<br>') + '</div>');
          break;
        case 'resume':
          output(window.location.href = 'Admin/resume/');
          break;
        case 'v2':
          output(window.location.href = 'Page/v2/');
          break;
        case 'nofap':
        case 'bday':
          output(window.location.href = 'Admin/bday/');
          break;
        /* Admin Tools */

        /* Tools */
        case 'currency':
          output(window.location.href = 'Tools/currency/');
          break;
        case 'gtin':
          output(window.location.href = 'Page/gtin/');
          break;
        case 'crypto':
          output(window.location.href = 'Tools/crypto/');
          break;
        case 'domain':
          output(window.location.href = 'Tools/domain/');
          break;
        case 'editor':
          output(window.location.href = 'Tools/editor/');
          break;
        case 'donate':
          output(window.location.href = 'Tools/donate/');
          break;
        case 'qr':
          output(window.location.href = 'Tools/qr/');
          break;
        case 'math':
          output(window.location.href = 'Tools/math/');
          break;
        /* Tools */

        


        case 'uname':
          output(navigator.appVersion);
          break;
        case 'aev&nbsp;core-set&nbsp;-gpu&nbsp;-y': case 'gpu': 
          // setCookie
          var gpuStatus = true;
          const d = new Date();
          d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000));
          document.cookie = "gpu_core="+gpuStatus+"; expires="+d.toUTCString() + ";path=/";
          output("GPU Core status: "+gpuStatus);
          break;

        
        case 'whoami':
          var result = "<img src=\"" + codehelper_ip["Flag"] + "\"><br><br>";
          for (var prop in codehelper_ip)
            result += prop + ": " + codehelper_ip[prop] + "<br>";
          output(result);
          break;
        default:
          if (cmd) {
            output(cmd + ': command not found. Type "help" for all available commands');
          }
      };

      window.scrollTo(0, getDocHeight_());
      this.value = ''; // Clear/setup line for next input.
    }
  }

  //
  function formatColumns_(entries) {
    var maxName = entries[0].name;
    util.toArray(entries).forEach(function (entry, i) {
      if (entry.name.length > maxName.length) {
        maxName = entry.name;
      }
    });

    var height = entries.length <= 3 ?
      'height: ' + (entries.length * 15) + 'px;' : '';

    // 12px monospace font yields ~7px screen width.
    var colWidth = maxName.length * 7;

    return ['<div class="ls-files" style="-webkit-column-width:',
      colWidth, 'px;', height, '">'
    ];
  }

  //
  function output(html) {
    //output_.insertAdjacentHTML('beforeEnd', '<p>' + html + '</p>');
    output_.insertAdjacentHTML('beforeEnd', html);
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

  //
  return {
    init: function () {
      output('');
    },
    output: output
  }
};