/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.slickNav = {
    attach: function (context, settings) {

      // SlickNav
      $('.menu').slicknav({
        label: '',
        // duration: 1000,
        // easingOpen: "easeOutBounce", //available with jQuery UI
        appendTo:'#brand'
      });
    };
  }
})(jQuery, Drupal);
