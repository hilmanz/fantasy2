<div id="leaderboardPage">
	
    <div class="headbar tr">
        <div class="leaderboard-head fl">
        	<h3>Leaderboard - Week XX</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <br />Nulla quam velit, vulputate eu pharetra nec, mattis ac neque.</p>
        </div>
        <div class="leaderboard-rank fr">
            <span>Your Rank:</span>
            <h3>345</h3>
            <span>Tier 2</span>
        </div>
    </div><!-- end .headbar -->
    <div id="thecontent">
        <div class="contents">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Club</th>
                        <th>Manager</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                    foreach($team as $n=>$t):
                      $params = $this->Paginator->params('Point');
                  ?>
                  <tr class="odd">
                    <td class="l-rank"><?=(($params['page']-1)*$params['limit'])+($n+1)?></td>
                    <td class="l-club"><?=h($t['Team']['team_name'])?></td>
                    <td class="l-manager"><?=h($t['Manager']['name'])?></td>
                    <td class="l-points"><?=number_format($t['Point']['points'])?></td>
                  </tr>
                  <?php
                  endforeach;
                  ?>
                </tbody>
            </table>
            <div class="widget action-button tr">
              <?php
              echo $this->Paginator->prev(__('PREV'), array(), null, 
                                          array('class' => 'prev'));
              ?>
              <?php
              echo $this->Paginator->next(__('NEXT'), array(), null, 
                                      array('class' => 'next'));
              ?>
             
            </div><!-- end .widget -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #leaderboardPage -->