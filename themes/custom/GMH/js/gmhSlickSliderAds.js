/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmhAds = {
    attach: function (context, settings) {

      // Ad Slider
      $('.ads').slick({
          autoplay: true,
          dots: true,
          arrows: true,
          // slidesToShow: 1,
          adaptiveHeight: true
      });

    }
  };

})(jQuery, Drupal);
