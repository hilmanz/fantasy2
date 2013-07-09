<div id="fillDetailsPage">
	<div id="info-bar" class="tr2">
	    <h4 class="date-now fl">14 june 2013</h4>
	    <div id="newsticker">
	          <ul class="slides">
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Lorem ipsum FC VS Dolor</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">2 Goals Sit amet, consectetuer</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Sdipiscing elit VS Rincidunt Team 3-0,</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Sed diam nonummy nibh euismod tincidunt ut</a></h3>
	            </li><!-- end .newsticker-entry -->
	          </ul><!-- end #newsticker -->
	    </div>
	    <h4 class="fr"><span class="yellow">6</span> DAYS <span class="yellow">0</span> HOUR <span class="yellow">0</span> MINUTE to close</h4>
	</div><!-- end #info-bar -->
    <div id="thecontent">
        <div id="content">
        	<div class="content">
            	<div class="row-2">
                    <h1 class="red">MY PROFILE</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
    			</div><!-- end .row-2 -->
                <form class="theForm" action="<?=$this->Html->url('/profile/update')?>" 
                  enctype="multipart/form-data" method="post">
                    <div class="tr avatarBox">
                        <div class="avatar-big">
                            <img src="http://graph.facebook.com/<?=$USER_DATA['fb_id']?>/picture" />
                        </div>
                        <!--<a href="#" class="button">Change Avatar</a>-->
                    </div>
                    <div class="row">
                        <label>Name</label>
                        <input type="text" name="name" value="<?=h($user['name'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Email</label>
                        <input type="text" name="email" value="<?=h($user['email'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Location</label>
                        <select class="styled" name="city">
                            <option>City</option>
                            <option>Jakarta</option>
                            <option>Bandung</option>
                        </select>
                    </div><!-- end .row -->
                   
                    <div class="row">
                        <input type="hidden" name="save" value="1"/>
                        <input type="submit" value="Save Changes" class="button" />
                    </div><!-- end .row -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">CASH LEFT</h3>
	            <h1>EUR <?=number_format($team_bugdet)?></h1>
	            <h3 class="red">POINTS</h3>
	            <h1>0 pts</h1> 
                <h3 class="red">CURRENT RANK</h3>
                <h1>1</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->