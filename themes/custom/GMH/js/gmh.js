/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.gmhGlobals = {
    attach: function (context, settings) {

      //Slick Slider
      $('.single-item').slick({
          autoplay: true,
          dots: true,
          arrows: true
      });
      $('.ads').slick({
          autoplay: true,
          // autoplaySpeed: 5000,
          dots: true,
          // infinite: true,
          // slidesToShow: 3,
          slidesToScroll: 3,
          variableWidth: true
      });
      $('.fade').slick({
        autoplay: true,
        dots: true,
        infinite: true,
        speed: 500,
        fade: true,
        cssEase: 'linear'
      });

      // MatchHeights
      $('.equalHeight').matchHeight();

      // Page header moves
      $("#gmh_page_hero").appendTo("#gmh_page_hero_div");
      // Title to header
      $(".node--type-issue #block-gmh-page-title").appendTo("#issueTitle");
      // Issue TOC
      $(".node--type-issue #issue-toc").appendTo("#gmh-toc");
      // SlickNav
      // $('.gmh_main_menu').slicknav({
      //   label: '',
      //   // duration: 1000,
      //   // easingOpen: "easeOutBounce", //available with jQuery UI
      //   appendTo:'#brand'
      // });

    }
  };

})(jQuery, Drupal);
