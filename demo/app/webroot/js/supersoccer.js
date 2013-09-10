// JAVASCRIPT FFL
// ACIT JAZZ v.1.2
$(document).ready(function() {
	$("body").addClass(BrowserDetect.browser); 
	$("table tbody tr:nth-child(odd)").addClass("odd");
	$("table tbody tr:nth-child(even)").addClass("even");
	// Popup
	
	$('.showPopup').fancybox();
	
	// Live Channel
	jQuery('#livechannels').jcarousel({
		scroll: 5,
	});
	
	// Drop Down Menu
	$('ul#topnav').superfish({ 
        delay:       600,
        animation:   {opacity:'show',height:'show'},
        speed:       'fast',
        autoArrows:  true,
        dropShadows: false
    });

	
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