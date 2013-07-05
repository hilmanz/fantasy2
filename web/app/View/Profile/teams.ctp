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
                    <h1 class="red">Fill in Your Teams</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <div class="select-team">
                    <form class="theForm" action="teams" method="post">
                        <h3>Hey, we've noticed you like these teams:</h3>
                        <div class="row">
                            <a class="teamBox" href="#">
                                <img src="images/team/logo1.png" />
                            </a><!-- end .teamBox -->
                            <a class="teamBox" href="#">
                                <img src="images/team/logo2.png" />
                            </a><!-- end .teamBox -->
                        </div><!-- end .row -->
                        <h3>Our you can select others:</h3>
                        <div class="row">
                            <?php 
                                $teamList = json_decode($team_list,true);
                                for($i=0;$i < sizeof($teamList);$i++):
                            ?>
                            <a class="teamBox" href="#selectTeam/<?=$o[$i]['uid']?>" title="<?=$teamList[$i]['name']?>">
                                <img src="images/team/logo1.png" />
                            </a><!-- end .teamBox -->
                            <?php endfor;?>
                        </div><!-- end .row -->
                        <div class="row">
                            <label>Personalize Your Team Name</label>
                            <input type="text" />
                        </div><!-- end .row -->
                        <div class="row">
                            <input type="hidden" name="team_id" value="<?=$team['uid']?>"/>
                            <input type="hidden" name="fb_id" value="<?=$fb_id?>"/>
                            <input type="hidden" name="create_team" value="1"/>
                            <input type="submit" value="Save &amp; Continue" class="button" />
                        </div><!-- end .row -->
                    </form>
                </div><!-- end .select-team -->
            </div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li><a href="details">Fill in Your Detail</a></li>
	               <li class="current"><a href="teams">Fill in Your Team</a></li>
	               <li><a href="players">Fill in Your Players</a></li>
	               <li><a href="staffs">Fill in Your Staff</a></li>
	               <li><a href="clubs">Fill in Your Club</a></li>
	               <li><a href="formations">Set Your First Formation</a></li>
	               <li><a href="invite_friends">Invite Friends</a></li>
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>EUR <?=number_format($team_bugdet)?></h1>
	            <h3 class="red">Est. Monthly Expenses</h3>
	            <h1>EUR 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->