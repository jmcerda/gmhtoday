/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmh = {
    attach: function (context, settings) {

      // Slick Slider
      $('.single-item').slick({
          autoplay: true,
          dots: true,
          arrows: true,
          adaptiveHeight: true
      });
      // Ad Slider
      $('.single-item-ads').slick({
          autoplay: true,
          dots: true,
          arrows: true,
          slidesToShow: 3,
          adaptiveHeight: true
      });

    }
  };

})(jQuery, Drupal);
