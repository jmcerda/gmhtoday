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
      $('.ads').slick({
          autoplay: true,
          dots: true,
          arrows: true,
          // slidesToShow: 1,
          adaptiveHeight: true
      });
      // Fade Slider
      $('.fade').slick({
        dots: true,
        infinite: true,
        speed: 500,
        fade: true,
        cssEase: 'linear'
      });

    }
  };

})(jQuery, Drupal);
