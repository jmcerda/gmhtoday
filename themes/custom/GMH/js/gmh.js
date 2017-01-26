/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.matchHeight = {
    attach: function (context, settings) {
      $('.equalHeight').matchHeight();
      // Page header moves
      // $("#gmh_page_hero").appendTo("#gmh_page_hero_div");
      //Slick Slider
      // Single Item no dots
      // $('.single-item').slick({
      //   // dots: true,
      //   arrows: true
      //   // slidesToShow: 1,
      //   // fade: true
      // });
      // $('.single-item').slick();
      // SlickNav
      // $('.menu').slicknav();
    }
  };

})(jQuery, Drupal);
