<div id="leaderboardPage">
	
    <div class="headbar tr">
        <div class="leaderboard-head fl">
          <?php

            if($matchday==1){
              $week = 1;
            }else{
              $week = ($matchday - 1);
            }
          ?>
        	<h3>Papan Peringkat – Minggu ke <?=$week?></h3>
            <p>Daftar urutan manajer berdasarkan poin tertinggi.<br />Diperbaharui secara mingguan. </p>
        </div>
        <div class="leaderboard-rank fr">
            <span>Peringkat Anda:</span>
            <h3><?=number_format($rank)?></h3>
            <span>Tier 2</span>
        </div>
    </div><!-- end .headbar -->
    <div id="thecontent">
        <div class="contents">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Klab</th>
                        <th>Manajer</th>
                        <th>Jumlah Poin</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                    foreach($team as $n=>$t):
                      $params = $this->Paginator->params('Point');
                  ?>
                  <tr class="odd">
                    <td class="l-rank"><?=h($t['Point']['rank'])?></td>
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
              echo $this->Paginator->prev(__('Sebelumnya'), array(), null, 
                                          array('class' => 'prev'));
              ?>
              <?php
              echo $this->Paginator->next(__('Berikutnya'), array(), null, 
                                      array('class' => 'next'));
              ?>
             
            </div><!-- end .widget -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #leaderboardPage -->