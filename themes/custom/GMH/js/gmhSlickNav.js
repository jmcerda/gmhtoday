/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.slickNav = {
    attach: function (context, settings) {

      // SlickNav
      $('.menu').slicknav();
    };
  }
})(jQuery, Drupal);
