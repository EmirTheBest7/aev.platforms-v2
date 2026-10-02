//Menu Appear
$('#toggle').click(function() {

    $(this).toggleClass('is-active');
    $('#navbarCollapse').toggleClass('is-active');
  });
  
  
  //Javascript for adding and removing the css classes
  
  $(".header-left svg").on("click", function(e) {
    var pos = $(".main-content").position();
    var position = pos.left;
    if (position >= 240) {
  
      $(".left-side").addClass("hide-menu");
      $(".main-content").addClass("hide-menu");
    } else {
  
      $(".left-side").removeClass("hide-menu");
      $(".main-content").removeClass("hide-menu");
    }
  
    $('.side-menu li').each(function(i) {
      var t = $(this);
      if (position >= 240) {
        setTimeout(function() {
          t.addClass('hide-menu');
        }, (i + 1) * 10);
      } else {
        setTimeout(function() {
          t.removeClass('hide-menu');
        }, (i + 1) * 30);
      }
    });
  
  });

$(function () {

    $('.c-tab--navigation').each(function (element) {

        var slider_width,
            tab_width,
            left_position,
            $active,
            $content,
            $links = $(this).find('a'),
            $currentTab = $(this).find('a.active'),
            $indicator = $(this).find('.c-tab-indicator');

        if ($currentTab.hasClass('active')) {
            slider_width = $('.c-tab--slider').innerWidth();
            tab_width = $currentTab.innerWidth();
            // var $tab_position = $slider_width - tab_width;
            left_position = $currentTab.position().left;
            $indicator.css({ 'width': tab_width + 'px', 'left': left_position + 'px' });
        }

        $active = $($links.filter('[href="' + location.hash + '"]')[0] || $links[0]);
        $active.addClass('active');
        $content = $($active[0].hash);

        $links.not($active).each(function () {
            $(this.hash).hide();
        });

        // Binds the click event handler
        $(this).on('click', 'a', function (e) {
            $active.removeClass('active');
            $content.hide();

            $active = $(this);
            $content = $(this.hash);

            $active.addClass('active');
            $content.show();

            moveSlider($active);
            addRipple(e);
            e.preventDefault();
        });
    });
});


function moveSlider($tab_clicked) {
var $new_tab = $tab_clicked;
var $tab_parent = $new_tab.parent();
var $list_parent = $tab_parent.parent();
var $indicator = $list_parent.find('.c-tab--slider').children();
var new_tab_width = $new_tab.innerWidth();
var left_position = $new_tab.position().left;

$indicator.css({ 'width': new_tab_width + 'px', 'left': left_position + 'px' });
}