<div id="leaderboardPage">
	 <?php echo $this->element('infobar'); ?>
    <div class="headbar tr">
        <div class="leaderboard-head">
        	<h3>Bursa Transfer</h3>
            <p>Bursa Transfer <span class="yellow">Supersoccer Football Manager</span> adalah tempat
                dimana kamu bisa beli pemain untuk ditambahkan ke line-up team mu.</p>
        </div>
    </div><!-- end .headbar -->
   
    <div id="thecontent">
        <div class="headbars tr">
            <h3>Pilih Team</h3>
        </div>
        <div class="contents tr">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable footable">
                <thead>
                    <tr>
                        <th data-class="expand"></th>
                        <th>Team Name</th>
                        <th data-hide="phone,tablet" class="aligncenter">Games Played</th>
                        <th data-hide="phone,tablet" class="aligncenter">Games Won</th>
                        <th data-hide="phone,tablet" class="aligncenter">Games Drawn</th>
                        <th data-hide="phone,tablet" class="aligncenter">Games Lost</th>
                        <th data-hide="phone,tablet" class="aligncenter">Games Scored</th>
                        <th data-hide="phone,tablet" class="aligncenter">Goal Conceded</th>
                        <th data-hide="phone,tablet" class="aligncenter">Top Scorer</th>
                        <th data-hide="phone,tablet" class="aligncenter">Top Assist</th>
                        <th data-hide="phone"></th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if(isset($teams)):
                    foreach($teams as $team):
                        $urlto= $this->Html->url('/market/team/'.$team['team']['team_id']);
                ?>
                  <tr>  
                        <td><a href="<?=$urlto?>"><img style="height:46px" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$team['team']['team_id'])?>.png"/></a></td>
                        <td><a class="yellow" href="<?=$urlto?>"><?=h($team['team']['name'])?></a></td>
                        <td align="center"><?=number_format(@$team['stats']['games'])?></td>
                        <td align="center"><?=number_format(@$team['stats']['wins'])?></td>
                        <td align="center"><?=number_format(@$team['stats']['draws'])?></td>
                        <td align="center"><?=number_format(@$team['stats']['loses'])?></td>
                        <td align="center"><?=number_format(@$team['stats']['goals'])?></td>
                        <td align="center"><?=number_format(@$team['stats']['condeded'])?></td>
                        <td align="center"><?=h(@$team['stats']['top_score']['name'])?></td>
                        <td align="center"><?=h(@$team['stats']['top_assist']['name'])?></td>
                        <td align="center">
                            <a href="<?=$urlto?>" 
                                class="button">PILIH</a>
                        </td>
                  </tr>
                <?php endforeach;endif;?>
                </tbody>
            </table>
            
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #leaderboardPage -->