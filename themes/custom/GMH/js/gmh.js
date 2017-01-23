/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.gmh = {
    attach: function (context, settings) {
      // MatchHeights
      $('.equalHeight').matchHeight();
      // SlickNav
      $('.menu').slicknav({
        label: '',
        // duration: 1000,
        // easingOpen: "easeOutBounce", //available with jQuery UI
        appendTo:'#brand'
      });
      // Page header moves
      $("#gmh_page_hero").appendTo("#gmh_page_hero_div");
      //Slick Slider
      // Single Item no dots
      $('.single-item').slick({
        dots: false,
        arrows: true,
        slidesToShow: 1,
        // fade: true
      });
      // Single Item w/ dots
      $('.single-item-dots').slick();
    }
  };
  // Drupal.behaviors.slickNav = {
  //   attach: function (context, settings) {
  //       // SlickNav
  //       $('.menu').slicknav({
  //         // label: '',
  //         // duration: 1000,
  //         // easingOpen: "easeOutBounce", //available with jQuery UI
  //         appendTo:'#brand'
  //       });
  //   }
  // };
  // Drupal.behaviors.slickSlider = {
  //   attach: function (context, settings) {
  //     context.once(function() {
  //       // Slick Slider
  //       // Single Item no dots
  //       $('.single-item').slick({
  //         dots: false,
  //         arrows: true,
  //         slidesToShow: 1,
  //         fade: true
  //       });
  //       // Single Item w/ dots
  //       $('.single-item-dots').slick();
  //     }
  //   }
  // };
  // Drupal.behaviors.pageHero = {
  //   attach: function (context, settings) {
  //     context.once(function() {
  //       $("#gmh_page_hero").appendTo("#gmh_page_hero_div");
  //     });
  //   }
  // };

})(jQuery, Drupal);
