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
          arrows: true,
          slidesToShow: 2,
          adaptiveHeight: true
      });

    }
  };

})(jQuery, Drupal);
