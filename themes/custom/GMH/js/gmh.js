/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmhGlobals = {
    attach: function (context, settings) {

      //Slick Slider
      $('.single-item').slick({
          // autoplay: true,
          dots: true,
          arrows: true,
      });

      // MatchHeights
      $('.equalHeight').matchHeight();

      // Page header moves
      $("#gmh_page_hero").appendTo("#gmh_page_hero_div");
      // Title to header
      $("h1").appendTo("#gmh_page_hero_div");

      // SlickNav
      // $('.gmh_main_menu').slicknav({
      //   label: '',
      //   // duration: 1000,
      //   // easingOpen: "easeOutBounce", //available with jQuery UI
      //   appendTo:'#brand'
      // });

    }
  };

})(jQuery, Drupal);
