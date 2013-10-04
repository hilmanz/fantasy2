
<div id="content">	
	<div id="slider">
	    <div id="banner">
	      <div class="bannerslider">
	          <ul class="slides">
              <?php 
              foreach($banners as $banner):
              ?>
	            <li class="theSlide">
	                <div class="imgSlide">
	                    <a href="<?=$this->Html->url($banner['Banners']['url'])?>">
                        <img src="<?=$this->Html->url(Configure::read('avatar_web_url').$banner['Banners']['banner_file'])?>" border="0"/>
                      </a>
	                </div>
	            </li>
            <?php endforeach;?>
	          </ul>
	      </div><!-- end .slider -->
	    </div><!-- end #banner -->
	</div><!-- end #slider -->

	<div id="listBox">
        <div class="box tr last">
            <h3>Daftar Sekarang!</h3>
            <div class="entry">
            	<p>
            	<a href="javascript:fb_login();" class="boxButton loginFacebook">&nbsp;</a>
            	<a href="javascript:fb_login();" class="boxButton createAccount last">&nbsp;</a>
                </p>
            </div><!-- end .entry -->
        </div><!-- end .box -->
		<div class="bannerBox">

				<a href="<?=$small_banner[0]['Banners']['url']?>"><img src="<?=$this->Html->url(Configure::read('avatar_web_url').$small_banner[0]['Banners']['banner_file'])?>" /></a>
		</div>
    </div><!-- end #listBox -->
</div><!-- end #content -->

<div id="sidebar" class="latestActivity">
	<div id="videoIntro">
    	<iframe width="100%" height="200" src="//www.youtube.com/embed/ccQjUK2rlRE" frameborder="0" allowfullscreen></iframe>
    </div>
	<div id="caramain" class="tr sidebox">
        <h3>Cara Bermain</h3>
        <div class="entry">
            <p>Selamat datang di SuperSoccer Football Manager, ajang unjuk gigi kemampuan kamu sebagai manager klab liga utama inggris.</p>
            <a class="readmore" href="<?=$this->Html->url('/pages/cara')?>">Selengkapnya </a>
        </div><!-- end .entry -->
    </div>
        <div class="bannerBox">
	                    <a href="<?=$small_banner[1]['Banners']['url']?>"><img src="<?=$this->Html->url(Configure::read('avatar_web_url').$small_banner[0]['Banners']['banner_file'])?>" /></a>
        </div>
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
	 },{scope: 'email,user_location,user_birthday'});
	
}
</script>