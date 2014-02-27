
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
                <?php for($i=0;$i<sizeof($team);$i++):?>
                	<li><?=h($team[$i]['Manager']['name'])?> 
                      - 
                      <?=h($team[$i]['Team']['team_name'])?> 
                      <span class="points">
                        <?=floatval($team[$i]['Weekly_point']['TotalPoints'])?> Pts</span></li>
                <?php endfor;?>
                </ul>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="box tr last" id="gameNews">
            <h3>Game News & Update</h3>
            <div class="gameNews">
            	<ul>
                  <?php for($i=0;$i<sizeof($tickers);$i++):?>
                	<li>
                    	<span class="date">
                        <?=date("d/m/Y",strtotime($tickers[$i]['Ticker']['post_dt']))?>
                      </span>
                    	<p>
                        <a href="<?=$tickers[$i]['Ticker']['url']?>" target="_blank">
                          <?=h($tickers[$i]['Ticker']['content'])?>
                         </a>
                      </p>
                  </li>
                	<?php endfor;?>
                </ul>
            </div><!-- end .entry -->
        </div><!-- end .box -->
        <div class="box tr" id="topPlayerWeek">
            <h3>Pemain Top Minggu Ini</h3>
            <div class="topPlayerWeek">
             
            	<ul>
                  <?php 
                    for($i=0;$i<sizeof($top_players);$i++):
                  ?>
                	 <li>

                          <a class="thumbPlayersSmall" href="#">
                            <img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description=<?=str_replace('t','',$top_players[$i]['team_id'])?>&dimensions=103x155&id=<?=str_replace('p','',$top_players[$i]['player_id'])?>"/></a>
                         <h3 style="float:left"><?=h($top_players[$i]['name'])?></h3>
                         <span class="points">
                            <?=number_format($top_players[$i]['total'])?>Pts
                          </span>
                    </li>
                	<?php endfor;?>
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
    <?php for($i=0;$i<sizeof($sidebar_banner);$i++):?>
    <div class="banner300x250">
        <a href="<?=$sidebar_banner[$i]['Banners']['url']?>" target="_blank">
          <img src="<?=$this->Html->url(Configure::read('avatar_web_url').$sidebar_banner[0]['Banners']['banner_file'])?>" />
        </a>
    </div>
    <?php endfor;?>
    
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