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
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Team Name</th>
                        <th>Games Played</th>
                        <th>Games Won</th>
                        <th>Games Drawn</th>
                        <th>Games Lost</th>
                        <th>Games Scored</th>
                        <th>Goal Conceded</th>
                        <th>Top Scorer</th>
                        <th>Top Assist</th>
                        <th></th>
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
            <div class="row">
                <p>Untuk memperlancar pembelian atau pencarian pemain performa tinggi, pekerjakanlah General Scount atau Chief Scout</p>
                <div class="row">
                    <div class="avatar-big fl">
                        <img src="<?=$this->Html->url('/content/thumb/general_scout.jpg')?>"/>
                    </div>
                    <div class="avatar-big fl">
                        <img src="<?=$this->Html->url('/content/thumb/chief_scout.jpg')?>"/>
                    </div>
                </div>
                <div class="rowBtn">
                    <a href="#" class="button">Rekrut Staff</a>
                </div>
            </div>
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #leaderboardPage -->