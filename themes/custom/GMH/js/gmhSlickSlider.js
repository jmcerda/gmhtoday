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
          // prevArrow: "<button type="button" class="slick-prev">Previous</button>",
          // nextArrow: "<button type="button" class="slick-prev">Next</button>",
          dots: true,
          mobileFirst: true
      });

    }
  };

})(jQuery, Drupal);
