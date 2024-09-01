$(document).ready(function () {
    var display_width = $(window).width();
    var hamburger = $("#hambuger_menu");
    var menu = $("#menubody");

    $(hamburger).click(function (e) {
      menu.toggleClass("open");
      hamburger.toggleClass("open");
    });

    $(".menu_body__item_wrapper li.has_child").each(function (index) {
      $(this).click(function (event) {
        $('.sub-menu').eq(index).slideToggle();
        event.preventDefault();
        event.stopImmediatePropagation();

      });
      $('.sub-menu').click(function (e){
        e.stopPropagation();
        e.stopImmediatePropagation();
      })
    })


  })