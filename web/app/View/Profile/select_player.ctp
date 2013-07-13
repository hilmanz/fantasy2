<div id="fillPlayersPage">
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
                    <h1 class="red">Choose Your Players</h1>
                    <p>Please choose 11 initial players for your team !</p>
    			</div><!-- end .row-2 -->
                <form class="theForm" method="post" action="<?=$this->Html->url('/profile/create_team')?>" enctype="multipart/form-data">
                    <div class="row-2">
                        <div class="col2">
                            <select class="styled" name="select_team">
                                <option value="-">Select Team</option>
                                <?php
                                    foreach($team_list as $team):
                                        $selected = "";
                                        if($team['uid']==$selected_team['team_id'])
                                            $selected = "selected='selected'";
                                ?>
                                <option value="<?=h($team['uid'])?>" <?=$selected?>><?=h($team['name'])?></option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div><!-- end .col2 -->
                        <div class="col2">
                            <!--
                            <div class="searchBox">
                                <input type="text" value="Search" />
                                <input type="submit" value="&nbsp;" />
                            </div>
                            -->
                        </div>
                    </div><!-- end .row-2 -->
                    <div class="row-2 titles">
                        <div class="col2">
                            <h2>Select Your Player</h2>
                        </div><!-- end .col2 -->
                        <div class="col2">
                            <h2>Selected</h2>
                        </div>
                    </div><!-- end .row-2 -->
                    <div class="row-2">
                        <!-- available players -->
                        <div class="col2 player-list" id="available">
                            &nbsp;
                        </div><!-- end .col2 -->
                        <!-- selected players -->
                        <div class="col2 player-list" id="selected">
                            &nbsp;
                        </div>
                    </div><!-- end .row-2 -->
                    <!--
                    <div class="row-2">
                        <div class="col2">
                            <div class="widget action-button">
                                <a class="prev" href="#">PREV</a>
                                <a class="next" href="#">NEXT</a>
                            </div>
                        </div>
                        <div class="col2">
                            <div class="widget action-button">
                                <a class="prev" href="#">PREV</a>
                                <a class="next" href="#">NEXT</a>
                            </div>
                        </div>
                    </div>
                    -->
                    <div class="row-2">
                        <input type="hidden" name="players" value="">
                        <input type="button" value="Save &amp; Continue" class="button" onclick="create_team();return false;"/>
                    </div><!-- end .row-2 -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li><span>Fill in Your Detail</span></li>
	               <li><span>Fill in Your Team</span></li>
	               <li class="current"><span>Fill in Your Players</span></li>
	               <li><span>Fill in Your Staff</span></li>
	              
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>EUR <?=number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. Monthly Expenses</h3>
	            <h1>EUR <span class="expense">0</span></h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<script>
function create_team(){
    populate_selected(function(s){
        $("input[name='players']").val(s);
        $("form.theForm").submit();
    });
}
//EVENTS
$("select[name='select_team']").on('custom_select', function(event, data) {
   getPlayers(data.value);
});
function getPlayers(team_id){
      $("#available").html('Please wait...');
     api_call("<?=$this->Html->url('/game/players/')?>"+team_id,function(response){
        $("#available").html('&nbsp;');
        if(response.length>0){
            tmp['available_teams'] = response;
            $.each(response,function(k,v){
                append_view(player,'#available',v);
            });
        }
    });
}
$(document).ready(function(){
  getPlayers("<?=$selected_team['team_id']?>");  
});
</script>
<script type="text/template" id="player">
<div class="jersey-player ">
<a href="#/select_player/<%=uid%>" id="<%=uid%>">
  <%
        var jersey_color = 'j-red';
        var pos_code = 'F';
        switch(position){
            case 'Goalkeeper':
                pos_code = 'G';
                jersey_color = 'j-grey';
            break;
            case 'Defender':
                pos_code = 'D';
                jersey_color = 'j-blue';
            break;
            case 'Midfielder':
                pos_code = 'M';
                jersey_color = 'j-yellow';
            break;
            case 'Forward':
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
            default:
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
        }
    %>
    <div class="jersey <%=jersey_color%>"><%=pos_code%></div>
    <span class="player-name"><%=name%></span>
</a>
</div>
</script>
<script type="text/template" id="player_selected">
<div class="jersey-player ">
<a href="#/unselect_player/<%=uid%>" id="<%=uid%>">
    <%
        var jersey_color = 'j-red';
        var pos_code = 'F';
        switch(position){
            case 'Goalkeeper':
                pos_code = 'G';
                jersey_color = 'j-grey';
            break;
            case 'Defender':
                pos_code = 'D';
                jersey_color = 'j-blue';
            break;
            case 'Midfielder':
                pos_code = 'M';
                jersey_color = 'j-yellow';
            break;
            case 'Forward':
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
            default:
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
        }
    %>
    <div class="jersey <%=jersey_color%>"><%=pos_code%></div>
    <span class="player-name"><%=name%></span>
</a>
</div>
</script>