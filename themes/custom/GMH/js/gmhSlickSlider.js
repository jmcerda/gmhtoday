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
          autoplay: true,
          dots: true,
          slidesToShow: 3
      });

    }
  };

})(jQuery, Drupal);
