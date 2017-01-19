/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.equalHeight_behavior = {
    attach: function (context, settings) {
      // MatchHeights
      $('.equalHeight').matchHeight();
    }
  };
  Drupal.behaviors.slickNav_behavior = {
    attach: function (context, settings) {
      // SlickNav
      $('.menu').slicknav({
        duration: 500,
        easingOpen: "easeOutBounce"
      });
    }
  };
  Drupal.behaviors.slickSlider_behavior = {
    attach: function (context, settings) {
      // Slick Slider
      $('.single-item').slick();
    }
  };

})(jQuery, Drupal);
