/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmh = {
    attach: function (context, settings) {

      // MatchHeights
      $('.equalHeight').matchHeight();,

      // Page header moves
      $("#gmh_page_hero").appendTo("#gmh_page_hero_div");

    }
  };

})(jQuery, Drupal);
