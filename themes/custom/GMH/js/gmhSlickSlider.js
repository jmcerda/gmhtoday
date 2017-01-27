/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmh = {
    attach: function (context, settings) {

      //Slick Slider
      $('.single-item').slick({
          arrows: true,
          autoplay: true,
          dots: true
      });

    }
  };

})(jQuery, Drupal);
