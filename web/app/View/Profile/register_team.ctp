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
                    <form class="theForm" action="<?=$this->Html->url('/profile/register_team')?>" method="post" enctype="multipart/form-data">
                        
                        <h3>Choose your team:</h3>
                        <div class="row">
                            <?php foreach($team_list as $team):?>
                            <a class="teamBox" href="#/selectTeam/<?=$team['uid']?>" title="<?=$team['name']?>">
                                <img src="<?=$this->Html->url('/images/team/logo'.rand(1,2).'.png')?>" />
                            </a><!-- end .teamBox -->
                            <?php endforeach;?>
                        </div><!-- end .row -->
                        <div class="row">
                            <label>Personalize Your Team Name</label>
                            <input type="text" name="team_name"/>
                        </div><!-- end .row -->
                        <div class="row">
                            <input type="hidden" name="fb_id" value="<?=$USER_DATA['fb_id']?>"/>
                            <input type="hidden" name="team_id" value=""/>
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
	               <li><a href="#">Fill in Your Detail</a></li>
	               <li class="current"><a href="#">Fill in Your Team</a></li>
	               <li><a href="#">Fill in Your Players</a></li>
	               <li><a href="#">Fill in Your Staff</a></li>
	              
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>EUR <?=number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. Monthly Expenses</h3>
	            <h1>EUR 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<?php 
    if(!is_array($team_list)) $team_list = array();
?>
<script>
    var team_list = <?=json_encode($team_list)?>;
</script>