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
        <div class="box4 fl">
            <div class="widget tr match-date">
                <h2>next match</h2>
                <span class="date yellow">Tuesday 20 June 2013</span>
            </div><!-- end .widget -->
            <div class="widget tr match-team">
                <div class="col3 home-team">
                    <a href="#" class="team-logo"><img src="<?=$this->Html->url('/images/team/logo1.png')?>" /></a>
                    <h3>Teamjason FC</h3>
                </div><!-- end .col3 -->
                <div class="col3 vs">
                    <h2>Vs</h2>
                </div><!-- end .col3 -->
                <div class="col3 away-team">
                    <a href="#" class="team-logo"><img src="<?=$this->Html->url('/images/team/logo2.png')?>" /></a>
                    <h3>K United</h3>
                </div><!-- end .col3 -->
            </div><!-- end .widget -->
            <div class="widget tr match-place">
                <p class="stadion">Old Trafford</p>
                <p class="attendance">+- 34,000 Attendance</p>
                <p class="gbpoint">1,642,758 GPB +-</p>
                <a class="view-more" href="#">See All Match</a>
            </div><!-- end .widget -->
            <div class="widget tr perform-team">
                <h2>your perfomance</h2>
                <h3><span class="span1">League Rank</span>:<span class="span2">44</span></h3>
                <h3><span class="span1">Last Earning</span>:<span class="span2">12</span></h3>
                <h3><span class="span1">Best PLayer</span>:<span class="span2">5</span></h3>
                <h3><span class="span1">Best Match</span>:<span class="span2">34</span></h3>
                <h3><span class="span1">Club Value</span>:<span class="span2">5.032.000</span></h3>
                <a class="view-more" href="#">View Leaderboard</a>
            </div><!-- end .widget -->
            <div class="widget tr action-button">
                <a class="button" href="#">Save Formations</a>
            </div><!-- end .widget -->
        </div><!-- end .box4 -->
        <div class="box3 tr fl">
            <div class="field-container">
                <div class="selectFormation">
                <select name="formations" id="formation-select" class="styled">
                    <option>Select Formation</option>
                    <option>4-4-2</option>
                    <option>4-4-2-A</option>
                    <option>4-3-3</option>
                    <option>4-2-3-1</option>
                    <option>3-5-2</option>
                </select>
                </div>
                <div class="field-formation">
                    <div id="the-formation">
                        
                    </div><!-- end .my-formation -->
                </div><!-- end .field-formation -->
            </div><!-- end .field-container -->
        </div><!-- end .box3 -->
        <div class="box4 fr">
            <div class="widget tr squad-team-name">
                <h2>TEAMjason FC</h2>
                <h3><a href="#" class="yellow">Team</a> | <a href="#" class="red">SUBS</a></h3>
            </div><!-- end .widget -->
            <div class="widget tr squad-team">
                <?php
                foreach($players as $player):
                    switch($player['position']){
                      case 'Goalkeeper':
                        $player_pos = "G";
                        $color = "grey";
                      break;
                      case 'Midfielder':
                        $player_pos = "M";
                        $color = "yellow";
                      break;
                      case 'Forward':
                        $player_pos = "F";
                        $color  = "red";
                      break;
                      default:
                        $player_pos = "D";
                        $color = "blue";
                      break;
                    }
                ?>
                <div class="jersey-player">
                    <div class="jersey j-<?=$color?>"><?=$player_pos?></div>
                    <div class="player-info">
                        <span class="player-name"><?=h($player['name'])?></span>
                        <span class="player-status">Playable</span>       
                    </div><!-- end .player-info -->
                </div><!-- end .jersey-player -->
                <?php endforeach;?>
            </div><!-- end .widget -->
            <div class="widget tr action-button">
                <a class="prev" href="#">PREV</a>
                <a class="next" href="#">NEXT</a>
            </div><!-- end .widget -->
        </div><!-- end .box4 -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<script>
$(document).ready(function(){
  function getLineUp(){
        api_call('<?=$this->Html->url("/game/lineup")?>',function(data){
            $("#formation-select option").filter(function() {
                return $(this).text() == data.formation; 
            }).prop('selected', true);
            $("#the-formation").removeClass().addClass('formation-'+data.formation);
            selectedVal['formations'] = {label:data.formation,
                                        value:data.formation};
            if(data.lineup.length==0){
                render_view(defaultformation,'#the-formation',{});
            }
        });
    }
    getLineUp();  
});
</script>
<script type="text/template" id="defaultformation">
<div class="jersey-player p11">
<div class="jersey j-red"></div>
<span class="player-name">11</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p10">
<div class="jersey j-red"></div>
<span class="player-name">10</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p9">
<div class="jersey j-yellow"></div>
<span class="player-name">9</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p8">
<div class="jersey j-yellow"></div>
<span class="player-name">8</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p7">
<div class="jersey j-yellow"></div>
<span class="player-name">7</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p6">
<div class="jersey j-yellow"></div>
<span class="player-name">6</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p5">
<div class="jersey j-blue"></div>
<span class="player-name">5</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p4">
<div class="jersey j-blue"></div>
<span class="player-name">4</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p3">
<div class="jersey j-blue"></div>
<span class="player-name">3</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p2">
<div class="jersey j-blue"></div>
<span class="player-name">2</span>
</div><!-- end .jersey-player -->
<div class="jersey-player p1">
<div class="jersey j-grey"></div>
<span class="player-name">1</span>
</div><!-- end .jersey-player -->
</script>