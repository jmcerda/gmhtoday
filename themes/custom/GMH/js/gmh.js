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
      $('.equalHeight').matchHeight();
    }
  };

})(jQuery, Drupal);

// MatchHeights
// $(function() {
//     $('.equalHeight').matchHeight();
// });

// // SlickNav
// $(function(){
//     $('#menu').slicknav();
// });