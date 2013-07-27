<div id="fillDetailsPage">
	<?php echo $this->element('infobar'); ?>
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
	            <h1><?=number_format($USER_POINTS)?> pts</h1> 
                <h3 class="red">CURRENT RANK</h3>
                <h1><?=number_format($USER_RANK)?></h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->