<div id="fillDetailsPage">
	
    <div id="thecontent">
        <div id="content">
            <div class="content">
                <div class="row-2">
                    <h1 class="red">Fill in Your Teams</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <div class="select-team">
                    <form class="theForm" action="<?=$this->Html->url('/profile/register_team')?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <label>Personalize Your Team Name</label>
                            <?php
                            $init_team_name = (isset($previous_team)) ? $previous_team['team_name'] : $USER_DATA['name'].' FC';
                            ?>
                            <input type="text" name="team_name" value="<?=htmlspecialchars($init_team_name)?>"/>
                           
                            <span class="icon_available check"></span>

                        </div><!-- end .row -->
                        <h3>Choose your team:</h3>
                        <div class="row">
                        	<?php $previous_team_id = "";?>
                            <?php foreach($team_list as $team):?>
                            <?php
                            	$selected = "";
                            	if(isset($previous_team) &&
                            			$team['uid']==$previous_team['team_id']){
                            		$previous_team_id = $team['uid'];
                            		$selected = "selected";
                            	}
                            ?>
                            <a class="teamBox <?=$selected?>" no="<?=$team['uid']?>" href="#/selectTeam/<?=$team['uid']?>" title="<?=$team['name']?>">
                               
                                <img style="height:40px" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace("t","",$team['uid'])?>.png"/>
                                <div class="team-name"><?=$team['name']?></div>
                            </a><!-- end .teamBox -->
                            <?php endforeach;?>
                        </div><!-- end .row -->
                       
                        <div class="row">
                            <input type="hidden" name="fb_id" value="<?=$USER_DATA['fb_id']?>"/>
                            <input type="hidden" name="team_id" value="<?=$previous_team_id?>"/>
                            <input type="hidden" name="create_team" value="1"/>
                            <input type="button" value="Save &amp; Continue" class="button" id="btnsave"/>
                        </div><!-- end .row -->
                    </form>
                </div><!-- end .select-team -->
            </div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
		<div class="widget tr videotutorial">
			<h2>BINGUNG?</h2>
			<span class="yellow">Mainkan video dibawah untuk petunjuk mengisi form ini</span>
			<div id="vidbox">
				<iframe width="100%" height="200" src="//www.youtube.com/embed/EE_V-mSnH3M" frameborder="0" allowfullscreen></iframe>
			</div><!-- end #vidbox -->
		</div><!-- end .videotutorial -->
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li><span>Fill in Your Detail</span></li>
	               <li class="current"><span>Fill in Your Team</span></li>
	               <li><span>Fill in Your Players</span></li>
	               <li><span>Fill in Your Staff</span></li>
	              
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>SS$ <?=number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. Weekly Expenses</h3>
	            <h1 class="expenses">SS$ 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->

<!--popups-->
<div class="popup">
    <div class="popupContainer popup-small" id="popup-messages">
        <div class="popupHeader">
        </div><!-- END .popupHeader -->
        <div class="popupContent">
            <div class="entry-popup">
                <h1>Name Taken</h1>
		        <p>Your team name has been taken by somebody else, please input another name.</p>
            </div><!--END .entry-popup-->
        </div><!-- END .popupContent -->
    </div><!-- END .popupContainer -->
</div><!-- END .popup --> 
<a class="fancy" href="#popup-messages"></a>
<?php 
    if(!is_array($team_list)) $team_list = array();
?>
<script>
var team_list = <?=json_encode($team_list)?>;
var ktimer;
function check_name(){
	check_team_name($("input[name='team_name']").val(),function(exists){
		$("span.check").removeClass('icon_available');
		$("span.check").removeClass('icon_unvailable');
		if(!exists){
			$("span.check").html('Available');
			$("span.check").addClass('icon_available');
		}else{
			$("span.check").addClass('icon_unvailable');
			$("span.check").html('Not Available');
		}
	});
}
$(document).ready(function(){

	check_name();
    $("input[name='team_name']").keyup(function(){
    	if(typeof ktimer !== 'undefined'){
    		clearTimeout(ktimer);
    	}
    	ktimer = setTimeout(function(){
    		//console.log();
    		check_name();
    	},1000);
    	
	});
    $(".fancy").fancybox();
	$("#btnsave").click(function(){
		if(typeof tmp.team_name_taken !== 'undefined'){
			if(tmp.team_name_taken == false){
				$('.theForm').submit();
			}else{
				
				$(".fancy").trigger('click');
			}
		}
	});
}); 
</script>