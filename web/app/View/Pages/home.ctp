<div id="content">	
	<div id="slider">
	    <div id="banner">
	      <div class="bannerslider">
	          <ul class="slides">
	            <li class="theSlide">
	                <div class="imgSlide">
	                    <a href="#"><img src="content/slider/1.jpg" /></a>
	                </div>
	            </li>
	            <li class="theSlide">
	                <div class="imgSlide">
	                    <a href="#"><img src="content/slider/2.jpg" /></a>
	                </div>
	            </li>
	            <li class="theSlide">
	                <div class="imgSlide">
	                    <a href="#"><img src="content/slider/3.jpg" /></a>
	                </div>
	            </li>
	          </ul>
	      </div><!-- end .slider -->
	    </div><!-- end #banner -->
	</div><!-- end #slider -->

	<div id="listBox">
        <div class="box tr">
            <h3>How To Play</h3>
            <div class="entry">
                <p>Maecenas sed diam eget risus varius blandit sit amet non magna. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.</p>
                <a class="readmore" href="index.php?menu=faq">See Detail</a>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="box tr last">
            <h3>register now!</h3>
            <div class="entry">
            	<p>
            	<a href="javascript:fb_login();" class="boxButton loginFacebook">&nbsp;</a>
                </p>
            </div><!-- end .entry -->
        </div><!-- end .box -->
    </div><!-- end #listBox -->
</div><!-- end #content -->

<div id="sidebar" class="tr latestActivity">
    <div id="latestActivity">
    	<h1>what's happening</h1>
        <div id="jp-container" class="jp-container">
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_40.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Jason</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_401.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Shevchenko</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_402.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Resty</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_403.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Liverpool Manager</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_404.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Ancelotti</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_405.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Acit Jazz</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_40.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Jason</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_401.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Shevchenko</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_402.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Resty</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_403.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Liverpool Manager</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_404.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Ancelotti</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
            <div class="row">
                <a href="#" class="thumb40 fl"><img src="content/thumb/1_405.jpg" /></a>
                <div class="entry fl">
                    <h3 class="username"><a href="#">Acit Jazz</a></h3>
                    <p>ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div><!-- end .entry -->
            </div><!-- end .row -->
        </div><!-- end #jp-container -->
    </div><!-- end #latestActivity -->
</div><!-- end #sidebar -->


<!-- -->
<script>
  window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
      appId      : '<?=$FB_APP_ID?>',                        // App ID from the app dashboard
      channelUrl : '//<?=$DOMAIN?>/channel.html', // Channel file for x-domain comms
      status     : true,                                 // Check Facebook Login status
      xfbml      : true,                                  // Look for social plugins on the page
      cookie : true
    });

    // Additional initialization code such as adding Event Listeners goes here
    
  };

  // Load the SDK asynchronously
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/all.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>


<script>
function fb_login(){
	FB.login(function(response) {
	   if (response.authResponse) {
	     console.log('Welcome!  Fetching your information.... ');
	     FB.api('/me', function(response) {
	       console.log('Good to see you, ' + response.name + '.');
           window.location = window.location;
	       document.location = "<?=$FB_AFTER_LOGIN_URL?>";
	     });
	   } else {
	     console.log('User cancelled login or did not fully authorize.');
	       document.location = "<?=$FB_AFTER_LOGIN_URL?>";
	   }
	 });
	
}
</script>