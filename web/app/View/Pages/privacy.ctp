<style>
	#faqPage p{
	margin:0 0 20px 0;
  }
  	#faqPage h1{ font-size:30px;}
	#faqPage ul{list-style:circle; padding:5px 5px 5px 20px; margin:0 0 0 20px;}
</style>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<?php echo $this->element('meta'); ?>
	
</head>
<body>
	
	<div id="fb-root"></div>
	<div id="effect"></div>
   	<div id="flag"></div>
 	<div id="body">
        <div id="universal">

                <?php if($USER_IS_LOGIN):?>

          			 <div id="header">
             		  	<a id="logo" href="<?=$this->Html->url('/manage/team')?>" title="SUPER SOCCER - FANTASY FOOTBALL LEAGUE">&nbsp;</a>
                        <div id="user-info">
                           
                            <a href="<?=$this->Html->url('/profile')?>" class="thumb40 fl">
                            <?php if(strlen(@$user['avatar_img'])==0 || @$user['avatar_img']=='0'):?>
                                <img src="http://graph.facebook.com/<?=$USER_DATA['fb_id']?>/picture" />
                            <?php else:?>
                                <img src="<?=$this->Html->url('/files/120x120_'.@$user['avatar_img'])?>" />
                            <?php endif;?>
                            </a>
                            <div class="entry fl">
                                <h3 class="username"><a href="<?=$this->Html->url('/profile')?>">
                                    <?php if(isset($user)):?>
                                    <?=h($user['name'])?>
                                    <?php else:?>
                                    <?=h($USER_DATA['name'])?>
                                    <?php endif;?>
                                </a> |
                                 <a class="logout" href="<?=$this->Html->url('/profile/logout')?>">Keluar</a></h3>
                                <span class="points red"><?=number_format($USER_POINTS)?> Pts</span>
                            </div><!-- end .entry -->
                        </div>
           			 </div><!-- end #header -->
                <?php else:?>
          			 <div id="header">
            		 <a id="logo" href="<?=$this->Html->url('/')?>" title="SUPER SOCCER - FANTASY FOOTBALL LEAGUE">&nbsp;</a>
         		     </div><!-- end #header -->
          		<?php endif;?>
            <?php
                if($USER_IS_LOGIN):
            ?>
                <div id="navigation">
                	<ul>
                    	<li>
                        	<a href="<?=$this->Html->url('/manage/team')?>">Mengelola Tim</a>
                        </li>
                    	<li>
                        	<a href="<?=$this->Html->url('/manage/club')?>">Klab Saya</a>
                        </li>
                    	<li>
                        	<a href="<?=$this->Html->url('/profile')?>">Profil Saya</a>
                        </li>
                    	<li>
                        	<a href="<?=$this->Html->url('/leaderboard/overall')?>">Papan Peringkat</a>
                        </li>
                    	<li>
                        	<a href="<?=$this->Html->url('/market')?>">Bursa Transfer</a>
                        </li>
                    	<li><a href="<?=$this->Html->url('/pages/faq')?>">Bantuan & FAQ</a></li>
                    </ul>
                </div>
			<?php else:?>
                <div id="topnavs" class="topnav">
                    <ul id="topnav">
                        <li><a href="http://www.supersoccer.co.id/" target="_blank" class="nav1">Home</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/supersoccer-tv/" class="nav2">Supersoccer TV</a>
                        <ul>
                               <li><a href="#">Live Streaming Service</a></li>
                                <li><a href="#">Video Collections</a></li>
                        </ul>
                        </li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/" class="nav3">Liga Inggris</a>
                          <ul>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/berita-liga-inggris/">Berita</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/ulasan-liga-inggris/">Ulasan</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/special-features-liga-inggris/">Special Features</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/preview-pertandingan-liga-inggris/">Preview Pertandingan</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/laporan-pertandingan-liga-inggris/">Laporan Pertandingan</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/liga-inggris/photo-liga-inggris/">Photo</a></li>
                            <li><a href="http://www.supersoccer.co.id/info-statistik-liga-inggris/">Info Statistik</a></li>
                            <li class="menu2"><a href="http://www.supersoccer.co.id/category/klub-liga-inggris/">Klub Liga Inggris</a>
                                <ul>
                                    <li style="height:12px"><div class="navTop"></div></li>
                                    <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-united/">Manchester United</a></li>
                                    <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-city/">Manchester City</a></li>
                                    <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/arsenalfc/">Arsenal</a></li>
                                    <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/chelsea/">Chelsea</a></li>
                                    <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-united/">Liverpool</a></li>
                                </ul>
                            </li>
                             </ul>
                        </li>
                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/" class="nav4">Sepakbola Internasional</a>
                             <ul>
                                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/berita-liga-internasional/">Berita</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/ulasan-liga-internasional/">Ulasan</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/special-features-liga-internasional/">Special Features</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/preview-pertandingan-liga-internasional/">Preview Pertandingan</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/laporan-pertandingan-liga-internasional/">Laporan Pertandingan</a></li>
                                <li><a href="http://www.supersoccer.co.id/photo-sepakbola-internasional/">Photo</a></li>
                                <li><a href="http://www.supersoccer.co.id/info-statistik-sepakbola-internasional/">Info Statistik</a></li>
                                <li class="menu2"><a href="#">Klub</a>
                                    <ul>
                                        <li style="height:12px"><div class="navTop"></div></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/">Klub Serie A</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/internazionale-milano">Internazionale Milano</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/juventus">Juventus</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/ac-milan">AC Milan</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/">Klub La Liga</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/real-madrid">Real Madrid</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/barcelona">Barcelona</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/">Klub Bundesliga</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/bayern-munich">Bayern Munich</a></li>
                                        <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/borussia-dortmund">Borussia Dortmund</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
               <!--         <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/" class="nav5">Sepakbola Indonesia</a>
                            <ul>
                            <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/berita-sepakbola-indonesia/">Berita</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/ulasan-sepakbola-indonesia/">Ulasan</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/special-features-sepakbola-indonesia/">Special Features</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/preview-pertandingan-sepakbola-indonesia/">Preview Pertandingan</a></li>
                            <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/laporan-pertandingan-sepakbola-indonesia/">Laporan Pertandingan</a></li>
                            </ul>
                        </li>-->
                        <li><a href="#" class="nav7">Supersoccer Collections</a>
                            <ul>
                            <li><a href="#">Supersoccer Collection</a></li>
                             </ul>
                        </li>
                    </ul>
                </div>
            <?php endif;?>
            <div id="container">
				
<div id="faqPage">
    <div id="thecontent">
        <div class="content">
            <div class="row-3">

<h1>PRIVACY POLICY</h1>

<h2>Privacy Policy For SuperSoccer Football Manager</h2>
<p>This Privacy Policy governs the manner in which SuperSoccer Football Manager collects, uses, maintains and discloses information collected from users (each, a "User") of the <a class="yellow" href="http://fm.supersoccer.co.id">http://fm.supersoccer.co.id</a> website ("Site"). This privacy policy applies to the Site and all products and services offered by SuperSoccer Football Manager.</p>

<h4>Personal identification information</h4>
<p>We may collect personal identification information from Users in a variety of ways, including, but not limited to, when Users visit our site, register on the site, subscribe to the newsletter, fill out a form, and in connection with other activities, services, features or resources we make available on our Site. Users may be asked for, as appropriate, name, email address, phone number. We will collect personal identification information from Users only if they voluntarily submit such information to us. Users can always refuse to supply personally identification information, except that it may prevent them from engaging in certain Site related activities.</p>
<h4>Non-personal identification information</h4>
<p>We may collect non-personal identification information about Users whenever they interact with our Site. Non-personal identification information may include the browser name, the type of computer and technical information about Users means of connection to our Site, such as the operating system and the Internet service providers utilized and other similar information..</p>
<h4>Web browser cookies</h4>
<p>Our Site may use "cookies" to enhance User experience. User's web browser places cookies on their hard drive for record-keeping purposes and sometimes to track information about them. User may choose to set their web browser to refuse cookies, or to alert you when cookies are being sent. If they do so, note that some parts of the Site may not function properly..</p>
<h4>How we use collected information</h4>
<p>SuperSoccer Football Manager may collect and use Users personal information for the following purposes:</p>
<ul>
	<li>To personalize user experience<br/>
	We may use information in the aggregate to understand how our Users as a group use the services and resources provided on our Site.</li>
	<li>To send periodic emails<br/>
If User decides to opt-in to our mailing list, they will receive emails that may include company news, updates, related product or service information, etc. If at any time the User would like to unsubscribe from receiving future emails, we include detailed unsubscribe instructions at the bottom of each email or User may contact us via our Site.</li>
</ul>
<h4>How we protect your information</h4>
<p>We adopt appropriate data collection, storage and processing practices and security measures to protect against unauthorized access, alteration, disclosure or destruction of your personal information, username, password, transaction information and data stored on our Site.</p>
<h4>Sharing your personal information</h4>
<p>We do not sell, trade, or rent Users personal identification information to others. We may share generic aggregated demographic information not linked to any personal identification information regarding visitors and users with our business partners, trusted affiliates and advertisers for the purposes outlined above.</p>
<h4>Changes to this privacy policy</h4>
<p>SuperSoccer Football Manager has the discretion to update this privacy policy at any time. When we do, we will post a notification on the main page of our Site, revise the updated date at the bottom of this page and send you an email. We encourage Users to frequently check this page for any changes to stay informed about how we are helping to protect the personal information we collect. You acknowledge and agree that it is your responsibility to review this privacy policy periodically and become aware of modifications.</p>
<h4>Your acceptance of these terms</h4>
<p>By using this Site, you signify your acceptance of this policy. If you do not agree to this policy, please do not use our Site. Your continued use of the Site following the posting of changes to this policy will be deemed your acceptance of those changes.</p>
<h4>Contacting us</h4>
<p>If you have any questions about this Privacy Policy, the practices of this site, or your dealings with this site, please contact us at:<br/>
SuperSoccer Football Manager<br/>
<a class="yellow" href="http://fm.supersoccer.co.id">http://fm.supersoccer.co.id</a><br/>
Jakarta, Indonesia<br/>
footballmanager@supersoccer.co.id<br/>
<br/>
This document was last updated on October 22, 2013</p>

            </div><!-- end .row-3 -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #faqPage -->

            </div><!-- end #container -->
            <div id="footer">
                <div id="footNav">
                  	<p class="fl"><a class="yellow" href="<?=$this->Html->url('/pages/privacy')?>" target="_blank">Privacy Policy</a> | <a  class="yellow" href="http://www.supersoccer.co.id/terms-and-conditions/" target="_blank">Terms And Conditions</a></p>
                  	<p class="fr">Copyright &copy; Supersoccer.co.id 2013</p>
                </div>
                
            </div>
        </div><!-- end #universal -->

    </div><!-- end #body -->
	<?php echo $this->element('js'); ?>
    
	<?php echo $this->element('sql_dump'); ?>
 
<script type="text/javascript">
        //<![CDATA[
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount','UA-4622806-45']);
        _gaq.push(['_trackPageview']);
        (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
        //]]>
    </script>
</body>
</html>

