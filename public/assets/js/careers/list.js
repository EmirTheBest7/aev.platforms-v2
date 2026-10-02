// Careers list: search + company filter. Port of the original page/careers/list/script.js (behaviour unchanged;
// inline handlers replaced by listeners below, search text escaped before it enters a selector).
// CODE FOR Search OR filter

function filterSearch(){
    var search = $(".uk-search-input").eq(0).val().toLowerCase();
    if(!search){
      $(".uk-search-input").eq(0).attr("uk-filter-control", "");
    }else{
      // the text is used inside a CSS attribute selector: escape the characters that could end it
      var safe = search.replace(/[\\'\]]/g, "\\$&");
      $(".uk-search-input").eq(0).attr("uk-filter-control", "filter: [data-name*='" + safe + "']");
    }
    $(".uk-search-input").eq(0).click();
  }
  
  $(".filter-main").on("beforeFilter", function(){
    $(".skills-no-result").removeClass('visible uk-animation-shake');
  });
  
  $(".filter-main").on("afterFilter", function(){
    var isElementVisible = false;
    var i = 0;
  
    while(!isElementVisible && i < $(".skills-el").length)
    {
      if($(".skills-el").eq(i).is(":visible")){
        isElementVisible = true;
      }
  
      i++;
    }
  
    if(isElementVisible === false){
      $(".skills-no-result").addClass('visible uk-animation-shake');
    }
  });
  
  function resetSearchBar(){
    $(".uk-search-input").eq(0).val('');
    $(".uk-search-input").eq(0).val('').attr("uk-filter-control", "");
  }
  

// Event wiring (the original used inline onkeyup/onclick/onsubmit attributes, which the CSP forbids)
$(".uk-search-input").on("keyup", filterSearch);
$("[data-reset-search]").on("click", resetSearchBar);
$("[data-search-form]").on("submit", function (event) { event.preventDefault(); });
