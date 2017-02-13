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
        mobileFirst: true,
        infinite: false,
        adaptiveHeight: true
        // onAfterChange: function(slide, index){
        //   if(index == 1){
        //     $('.single-item').slickPause();
        //   }
        // }
      });
      $('.ads').slick({
          // autoplay: true,
          dots: true,
          infinite: true,
          slidesToShow: 3,
          slidesToScroll: 3
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
      $('#superfish-main').slicknav({
        label: '',
        duration: 500,
        // easingOpen: "easeOutBounce", //available with jQuery UI
        appendTo:'#brand'
      });
      //Scroll to top

      //Check to see if the window is top if not then display button
      $(window).scroll(function(){
        if ($(this).scrollTop() > 100) {
          $('.scrollToTop').fadeIn();
        } else {
          $('.scrollToTop').fadeOut();
        }
      });

      //Click event to scroll to top
      $('.scrollToTop').click(function(){
        $('html, body').animate({scrollTop : 0},800);
        return false;
      });

    }
  };

})(jQuery, Drupal);
