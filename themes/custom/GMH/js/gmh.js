/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.equalHeight_behavior = {
    attach: function (context, settings) {
      // MatchHeights
      $('.equalHeight').matchHeight();
      // SlickNav
      $('.menu').slicknav({
		  label: '',
		  duration: 1000,
		  easingOpen: "easeOutBounce", //available with jQuery UI
		  prependTo:'#demo2'
	  });
    }
  };

})(jQuery, Drupal);


// $(function() {
//     $('.equalHeight').matchHeight();
// });

// // SlickNav
// $(function(){
//     $('#menu').slicknav();
// });
