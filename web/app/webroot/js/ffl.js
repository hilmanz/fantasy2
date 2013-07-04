// JAVASCRIPT FFL
// ACIT JAZZ v.1.2
	
jQuery(document).ready(function($) {
	/*------------ADD CLASS DETECT BROWSER------------*/ 
	$("body").addClass(BrowserDetect.browser); 
	$("table tbody tr:nth-child(odd)").addClass("odd");
$	 ("table tbody tr:nth-child(even)").addClass("even");
}); 
// SLIDER
$(window).load(function(){
  $('.bannerslider').flexslider({
	animation: "slide",
	controlNav: true,               
	directionNav: true,           
	prevText: "Previous",          
	nextText: "Next",
	start: function(slider){
	  $('body').removeClass('loading');
	}
  });
  $('#newsticker').flexslider({
	animation: "slide",
	animationSpeed: 1000,
	slideshowSpeed: 3000,
	start: function(slider){
	  $('body').removeClass('loading');
	}
  });
});

  $(function() {
    $( "#clubtabs" ).tabs();
  });