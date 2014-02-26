
<div id="content">	
	<div id="slider">
	    <div id="banner">
	      <div class="bannerslider">
	          <ul class="slides">
              	<li>
                    <div id="videoIntro">
                        <iframe width="100%" height="350" src="//www.youtube.com/embed/HxwxlVqW0O0" frameborder="0" allowfullscreen></iframe>
                    </div>		
    			</li>
              <?php 
              foreach($banners as $banner):
              ?>
	            <li class="theSlide">
	                <div class="imgSlide">
	                    <a href="<?=$this->Html->url($banner['Banners']['url'])?>" target="_blank">
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
        <div class="box tr" id="topManager">
            <h3>Top Manager Minggu Ini</h3>
            <div class="topManager">
            	<ul>
                	<li>Nibh Nullam - Liverpool <span class="points">24123 Pts</span></li>
                	<li>Vehicula Vestibulum - Arsenal <span class="points">23123 Pts</span></li>
                	<li>Malesuada Sit - Chelsea <span class="points">20123 Pts</span></li>
                	<li>Duis mollis - Manchester City <span class="points">18123 Pts</span></li>
                	<li>Vehicula Vestibulum - Arsenal <span class="points">23123 Pts</span></li>
                </ul>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="box tr last" id="gameNews">
            <h3>Game News & Update</h3>
            <div class="gameNews">
            	<ul>
                	<li>
                    	<span class="date">15/02/2014</span>
                    	<p>Vehicula Vestibulum Bibendum Consectetur</p>
                    </li>
                	<li>
                    	<span class="date">15/02/2014</span>
                    	<p>Malesuada Commodo Ipsum Tristique Sit</p>
                    </li>
                	<li>
                    	<span class="date">15/02/2014</span>
                    	<p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula</p>
                    </li>
                </ul>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="box tr" id="topPlayerWeek">
            <h3>Pemain Top Minggu Ini</h3>
            <div class="topPlayerWeek">
            	<ul>
                	<li>
                    	<a href="#" class="smallerThumb">
							<?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                            <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
                            <?php else:?>
                            <img width="65" src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
       					 </a>
                         <h3>Tevez</h3>
                    </li>
                	<li>
                    	<a href="#" class="smallerThumb">
							<?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                            <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
                            <?php else:?>
                            <img width="65" src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
       					 </a>
                         <h3>Andrey Shevchenko</h3>
                    </li>
                	<li>
                    	<a href="#" class="smallerThumb">
							<?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                            <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
                            <?php else:?>
                            <img width="65" src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
       					 </a>
                         <h3>Ronaldo</h3>
                    </li>
                	<li>
                    	<a href="#" class="smallerThumb">
							<?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                            <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
                            <?php else:?>
                            <img width="65" src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
       					 </a>
                         <h3>Dudek</h3>
                    </li>
                	<li>
                    	<a href="#" class="smallerThumb">
							<?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                            <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
                            <?php else:?>
                            <img width="65" src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
       					 </a>
                         <h3>Rooney</h3>
                    </li>
                </ul>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="bannerBox last">
	                    <a href="<?=$small_banner_2[0]['Banners']['url']?>" target="_blank"><img src="<?=$this->Html->url(Configure::read('avatar_web_url').$small_banner_2[0]['Banners']['banner_file'])?>" /></a>
        </div>
		<div class="bannerBox last">

				<a href="<?=$small_banner_1[0]['Banners']['url']?>" target="_blank"><img src="<?=$this->Html->url(Configure::read('avatar_web_url').$small_banner_1[0]['Banners']['banner_file'])?>" /></a>
		</div>
    </div><!-- end #listBox -->
</div><!-- end #content -->

<div id="sidebar">
	<div id="loginbox" class="tr">
    	<h3>Login Manager</h3>
		<a href="javascript:fb_login();" class="boxButton loginFacebook">&nbsp;</a>
    </div>
    <div class="banner300x250"></div>
    <div class="banner300x250"></div>
    <div class="banner300x250"></div>
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
	    
	     FB.api('/me', function(response) {
	       console.log('Good to see you, ' + response.name + '.');
           window.location = window.location;
	       document.location = "<?=$FB_AFTER_LOGIN_URL?>";
	     });
	   } else {
	     
	       document.location = "<?=$FB_AFTER_LOGIN_URL?>";
	   }
	 },{scope: 'email,user_location,user_birthday'});
	
}
</script>