// URL Redirect | Start App Script

const urlVar = location.search.replace('?', '').split('=');
$(function() {
  const terminal = $('.window[data-window="cmd"]');
  const CMDurl = './terminal/?'+urlVar[0]+'='+urlVar[1];
  
  switch(urlVar[0]) {
    case 'Page':
      $('.frame').attr('src', CMDurl);
      terminal.attr('style', "width:100%;height:100vh;top:0;left:0;");
      $('.window__title').html(urlVar[1]).attr('style', 'text-transform: capitalize;');
      $('.window__icon i').attr('class', 'uil uil-apps');
      break;
    case '0x': //Error {error_code}
      $('.frame').attr('src', CMDurl);
      $('.window__title').html('Error ' +urlVar[1]);
      $('.window__icon i').attr('class', 'uil uil-exclamation-triangle');
      break;
    default:
      break;
  }
});

window.history.pushState({}, document.title, window.location.pathname); //Clear URL

$(window).load(function() {
  var $container = $('.start-screen');

  $container.masonry({
    itemSelector: '.masonry-item',
    columnWidth: 128
  });

  $('.start-menu').hide().css('opacity', 1);
});


function resizeStart() {
    var startWidth = $('.start-screen').outerWidth();
    var startRound = Math.ceil(startWidth / 128.0) * 128;
  
  console.log('original: ' + startWidth);
  console.log('rounded: ' + startRound);
    
    $('.start-screen').css({
      'width' : startRound
    });
}

//$(window).load(resizeStart);
//$(window).resize(resizeStart);

$(document).ready(function () {
    $("#reload_iframe").click(function () {
      $('iframe').attr('src', $('iframe').attr('src'));
    });
  });


$(function() {
  var zIndex = 1;

  var fullHeight = $(window).height(),
      fullWidth  = $(window).width() - 60;
 
  
  $(window).resize(function() {
    fullHeight = $(window).height();
    fullWidth = $(window).width() - 60;
  });
  
  $(function() {
    $('.window:visible').each(function() {
      var appName = $(this).data('window');
      
      $('.taskbar__item[data-window="' + appName + '"]').addClass('taskbar__item--open');
    });
    
    $('.window:hidden').each(function() {
      $(this).addClass('window--opening');
    });
  });
  
  $(function() {
    var initialActive = $('.window:visible').not('.window--minimized').first();
    var appName = $(initialActive).data('window');
    
   $(initialActive).addClass('window--active').css({'z-index' : zIndex++});
   $('.taskbar__item[data-window="' + appName + '"]').addClass('taskbar__item--active');
  });

  
  $('.window').click(function(e) {
    if ( !$(this).is('.window--active')) {
      $('.window').removeClass('window--active');
    }
    
    $(this).addClass('window--active');
    $(this).css({'z-index' : zIndex++});
    
    var appName = $(this).data('window');
    var targetTaskbar = $('.taskbar__item[data-window="' + appName + '"]');
   
    if ( !$('.window__close').is(e.target) && $('.window__close').has(e.target).length === 0 && !$('.window__minimize').is(e.target) && $('.window__minimize').has(e.target).length === 0 ) {
      $('.taskbar__item').removeClass('taskbar__item--active'); $(targetTaskbar).addClass('taskbar__item--active');
      
    }
  });
  
  $('.window').resizable({
    handles: 'n,ne,e,se,s,sw,w,nw',
    stop: function() {
     var initialHeight = $(this).height(),
     initialWidth = $(this).width(),
     initialTop = $(this).position().top,
     initialLeft = $(this).position().left; }
   });

  $('.window').draggable({    
    handle: '.window__titlebar',
    stop: function() {
    var initialHeight = $(this).height(),
        initialWidth = $(this).width(),
        initialTop = $(this).position().top,
        initialLeft = $(this).position().left; 
    },
    start: function(event, ui) {
      var mouseX = event.pageX + 'px';
      console.log(mouseX);
      
      $('.window').removeClass('window--active');
      
      $(this).addClass('window--active');
      $(this).css({'z-index' : zIndex++});

      if ( $(this).hasClass('window--maximized') ) {
       
        //alert(mouseX);
        $(this).removeClass('window--maximized').css({ 'height' : initialHeight,
                      'width'  : initialWidth,
                      'top'    : 0,
                      'left'   : mouseX }); 
      }  
      
      var appName = $(this).data('window');
    var targetTaskbar = $('.taskbar__item[data-window="' + appName + '"]')
    $('.taskbar__item').removeClass('taskbar__item--active'); $(targetTaskbar).addClass('taskbar__item--active');
    }
  });

  function openApp(e) {
    var appName = $(this).data('window');
    var targetWindow = $('.window[data-window="' + appName + '"]');
    var targetTaskbar = $('.taskbar__item[data-window="' + appName + '"]');
    
    e.preventDefault();
    $('.taskbar__item').removeClass('taskbar__item--active');
    
    if ( targetWindow.is(':visible') ) {
      if ( targetWindow.hasClass('window--active') ) {
        $(targetWindow).toggleClass('window--minimized');

        if ( !targetWindow.hasClass('window--minimized') ) {
          var initialHeight = $(targetWindow).height(),
               initialWidth = $(targetWindow).width(),
               initialTop = $(targetWindow).position().top,
               initialLeft = $(targetWindow).position().left; 
          
          $('.window').removeClass('window--active');

         $(targetWindow).addClass('window--active').css({ 
            'z-index' : zIndex++
          });
          
         $(targetTaskbar).addClass('taskbar__item--active');
        }
      } else {
        $('.window').removeClass('window--active');
        $(targetWindow).addClass('window--active').removeClass('window--minimized').css({'z-index' : zIndex++});
        
        $(targetTaskbar).addClass('taskbar__item--active');
      }
    } else {
      $('.window').removeClass('window--active');
      
      $('.window[data-window="' + appName + '"]').css({ 'z-index' : zIndex++ }).addClass('window--active').show();
      
      setTimeout(function() {
        $('.window[data-window="' + appName + '"]').removeClass('window--opening');
      }, 500);
      
      $(targetTaskbar).addClass('taskbar__item--active').addClass('taskbar__item--open');
    } 
  }
  
  $('.taskbar__item').click(openApp);
  $('.start-menu__recent li a').click(openApp);
  $('.start-screen__tile').click(openApp);

  
  
  
  
  
  $('.window__titlebar').each(function() {
    var parentWindow = $(this).closest('.window'); 
    
    $(this).find('a').click(function(e) {
      e.preventDefault();
    });
    
    $(this).find('.window__icon').click(function(e) {
      $(this).siblings('.window__menutoggle').trigger('click');
    });
    
    $(this).find('.window__menutoggle').click(function(e) {
      $(parentWindow).find('.window__menu').fadeToggle(100).toggleClass('window__menu--open');
      $(this).toggleClass('window__menutoggle--open');
    });
    
    $(this).find('.window__close').click(function(e) {
      $(parentWindow).addClass('window--closing');
      
      setTimeout(function() {             $(parentWindow).hide().removeClass('window--closing').addClass('window--opening');
      }, 500);
      
      var appName = $(parentWindow).data('window');
      
      $('.taskbar__item[data-window="' + appName + '"]').removeClass('taskbar__item--open').removeClass('taskbar__item--active');
      
      var closest = $('.window').not('.window--minimized, .window--closing, .window--opening').filter(function() {
        return $(this).css('z-index') < zIndex
      }).first();
      
      $(closest).addClass('window--active');
    });
    
    $(this).find('.window__minimize').click(function(e) {
      $(parentWindow).addClass('window--minimized');
      
     var appName = $(parentWindow).data('window');
    var targetTaskbar = $('.taskbar__item[data-window="' + appName + '"]');
      
     //alert(targetTaskbar.attr('class'));
      $(targetTaskbar).removeClass('taskbar__item--active');
      
    });
    
    $(this).find('.window__maximize').click(function(e) {

      $(parentWindow).toggleClass('window--maximized');

      if ( !$(parentWindow).hasClass('window--maximized') ) {
        $(parentWindow).css({ 'height' : initialHeight,
                              'width'  : initialWidth,
                              'top'    : initialTop,
                              'left'   : initialLeft });
      } else {
        initialHeight = $(parentWindow).height();
        initialWidth = $(parentWindow).width();
        initialTop = $(parentWindow).position().top;
        initialLeft = $(parentWindow).position().left;

        $(parentWindow).css({ 'height' : fullHeight,
                              'width'  : fullWidth,
                              'top'    : 0,
                              'left'   : 0 });
      }
    });
  });

  $('.window__titlebar').mouseup(function(e) {
  var parentWindow = $(this).closest('.window');
  var pos = $(parentWindow).offset().top;
    
    if ( pos < -5 ) {
      //alert('at top')
      $(parentWindow).addClass('window--maximized');
      
      initialHeight = $(parentWindow).height();
        initialWidth = $(parentWindow).width();
        initialTop = $(parentWindow).position().top;
        initialLeft = $(parentWindow).position().left;

        $(parentWindow).css({ 'height' : fullHeight,
                              'width'  : fullWidth,
                              'top'    : 0,
                              'left'   : 0 });
    }
  
});
}); 



// Unfocus windows when desktop is clicked
$('.desktop').click(function(e) {
  if ( $('.desktop').has(e.target).length === 0 ) {
    $('.window').removeClass('window--active');
    $('.taskbar__item').removeClass('taskbar__item--active');
  }
});




// Delete ?
/*
$(function() {
  $('.side__list ul').each(function() {
    if ( $(this).find('ul').is(':visible') ) {
      $(this).children('li').addClass('side__list--open');
    }
  });
});



$(function() {
  $('.side__list li').each(function() {
    if ( $(this).children('ul').length ) {
      //$(this).addClass('list__sublist');
      $(this).children('a').append('<span class="list__toggle"></span>');
      
      
    }
    
    if ( $(this).children('ul').is(':visible') ) {
      $(this).addClass('side__list--open');
    }
  });
});

$(document).on('click', '.list__toggle',  function() {

 $(this).closest('li').children('ul').animate({
  'height' : 'toggle',
  'opacity' : 'toggle'
}, 250);

 $(this).closest('li').toggleClass('side__list--open');


});




function toggleStart(e) {
  $('.start-menu-fade').fadeToggle(500);
  $('.start-menu').fadeToggle(250).toggleClass('start-menu--open');
  $('.taskbar__item--start').toggleClass('start--open');
}
$('.taskbar__item--start').click(toggleStart);
$('.start-menu__recent li a').click(toggleStart);
$('.start-screen__tile').click(toggleStart);

// Prevent "open" class on start
$(function() {
  $('.taskbar__item--start').click(function() {
    $(this).removeClass('taskbar__item--open taskbar__item--active');
  });
});


$(document).mouseup(function(e) {
    var start = $('.start-menu');
    var startToggle = $('.taskbar__item--start');


    if (start.is(':visible') && !startToggle.is(e.target) && startToggle.has(e.target).length === 0 && !start.is(e.target) && start.has(e.target).length === 0 ) {
      toggleStart();
      //alert('clicked outside start');
    }
  
  

});




// Current time 
$(function() {
  var a_p = "";
  var d = new Date();

  var curr_hour = d.getHours();

  if (curr_hour < 12)
     {
     a_p = "AM";
     }
  else
     {
     a_p = "PM";
     }
  if (curr_hour == 0)
     {
     curr_hour = 12;
     }
  if (curr_hour > 12)
     {
     curr_hour = curr_hour - 12;
     }

  var curr_min = d.getMinutes();
  
  if ( curr_min < 10 ) {
    curr_min = '0' + curr_min;
  }

  $('.time').html(curr_hour + ':' + curr_min + ' ' + a_p);
});



$('.menu-toggle').each(function() {
  var menuName = $(this).data('menu');
  var menu = $('.menu[data-menu="' + menuName + '"]');
  var pos = $(this).position();
  var height = $(this).outerHeight();
  
  if ( !$(menu).hasClass('menu--bottom') ) {
    $(menu).position({
      my: 'left top',
      at: 'left bottom',
      of: this,
      collision: 'none'
    });
  } else {
   
  }
  
  $(menu).hide();
  
  $(this).click(function(e) {
    e.preventDefault();
    $('.menu').not(menu).hide();
    $(menu).toggle();
  });
});




$(document).mouseup(function(e) {
  if ( $('.menu').has(e.target).length === 0 && !$('.menu-toggle').is(e.target) && $('.menu-toggle').has(e.target).length === 0 ) {
    $('.menu').hide();
  }
});*/

const STEP_LENGTH = 1;
const CELL_SIZE = 10;
const BORDER_WIDTH = 2;
const MAX_FONT_SIZE = 500;
const MAX_ELECTRONS = 100;
const CELL_DISTANCE = CELL_SIZE + BORDER_WIDTH;


const BG_COLOR = '#1d2227';
const BORDER_COLOR = '#13191f';
const CELL_HIGHLIGHT = '#328bf6';
const ELECTRON_COLOR = '#00b07c';
const FONT_COLOR = '#ff5353';

const FONT_FAMILY = 'Helvetica, Arial, "Hiragino Sans GB", "Microsoft YaHei", "WenQuan Yi Micro Hei", sans-serif';

const DPR = window.devicePixelRatio || 1;

const ACTIVE_ELECTRONS = [];
const PINNED_CELLS = [];



class FullscreenCanvas {
  constructor(disableScale = false) {
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    this.canvas = canvas;
    this.context = context;
    this.disableScale = disableScale;

    this.resizeHandlers = [];
    this.handleResize = _.debounce(this.handleResize.bind(this), 100);

    this.adjust();

    window.addEventListener('resize', this.handleResize);
  }

  adjust() {
    const {
      canvas,
      context,
      disableScale } =
    this;

    const {
      innerWidth,
      innerHeight } =
    window;

    this.width = innerWidth;
    this.height = innerHeight;

    const scale = disableScale ? 1 : DPR;

    this.realWidth = canvas.width = innerWidth * scale;
    this.realHeight = canvas.height = innerHeight * scale;
    canvas.style.width = `${innerWidth}px`;
    canvas.style.height = `${innerHeight}px`;

    context.scale(scale, scale);
  }

  clear() {
    const { context } = this;

    context.clearRect(0, 0, this.width, this.height);
  }

  makeCallback(fn) {
    fn(this.context, this);
  }

  blendBackground(background, opacity = 0.05) {
    return this.paint((ctx, { realWidth, realHeight, width, height }) => {
      ctx.globalCompositeOperation = 'source-over';
      ctx.globalAlpha = opacity;

      ctx.drawImage(background, 0, 0, realWidth, realHeight, 0, 0, width, height);
    });
  }

  paint(fn) {
    if (!_.isFunction(fn)) return;

    const { context } = this;

    context.save();

    this.makeCallback(fn);

    context.restore();

    return this;
  }

  repaint(fn) {
    if (!_.isFunction(fn)) return;

    this.clear();

    return this.paint(fn);
  }

  onResize(fn) {
    if (!_.isFunction(fn)) return;

    this.resizeHandlers.push(fn);
  }

  handleResize() {
    const { resizeHandlers } = this;

    if (!resizeHandlers.length) return;

    this.adjust();

    resizeHandlers.forEach(this.makeCallback.bind(this));
  }

  renderIntoView(target = document.getElementById('main')) {
    const { canvas } = this;

    this.container = target;

    canvas.style.position = 'absolute';
    canvas.style.left = '0px';
    canvas.style.top = '0px';
    canvas.style.zIndex = '0';

    target.appendChild(canvas);
  }

  remove() {
    if (!this.container) return;

    try {
      window.removeEventListener('resize', this.handleResize);
      this.container.removeChild(this.canvas);
    } catch (e) {}
  }}

const bgLayer = new FullscreenCanvas();
const mainLayer = new FullscreenCanvas();
const shapeLayer = new FullscreenCanvas(true);


function drawGrid() {
  bgLayer.paint((ctx, { width, height }) => {
    ctx.fillStyle = BG_COLOR;
    ctx.fillRect(0, 0, width, height);

    ctx.fillStyle = BORDER_COLOR;

    // horizontal lines
    for (let h = CELL_SIZE; h < height; h += CELL_DISTANCE) {
      ctx.fillRect(0, h, width, BORDER_WIDTH);
    }

    // vertical lines
    for (let w = CELL_SIZE; w < width; w += CELL_DISTANCE) {
      ctx.fillRect(w, 0, BORDER_WIDTH, height);
    }
  });
}

function prepaint() {
  drawGrid();

  mainLayer.paint((ctx, { width, height }) => {
    // composite with rgba(255,255,255,255) to clear trails
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, width, height);
  });

  mainLayer.blendBackground(bgLayer.canvas, 0.9);
}


const shape = {
  lastText: '',
  lastMatrix: null,
  renderID: undefined,
  isAlive: false,

  init(container = document.getElementById('main')) {
    if (this.isAlive) {
      return;
    }

    bgLayer.onResize(drawGrid);
    mainLayer.onResize(prepaint);

    mainLayer.renderIntoView(container);

    shapeLayer.onResize(() => {
      if (this.lastText) {
        this.print(this.lastText);
      }
    });

    prepaint();
    render();

    this.unbindEvents = handlePointer();
    this.isAlive = true;
  }
};


shape.init();

// prevent zoom
document.addEventListener('touchmove', e => e.preventDefault());