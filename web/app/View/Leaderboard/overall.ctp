<?php
$monthly = isset($monthly) ? "selected='selected'":"";
$weekly = isset($weekly) ? "selected='selected'":"";
$overall = isset($overall) ? "selected='selected'":"";

?>
<div id="leaderboardPage">
    <div class="headbar tr">  
      <div class="fl">
        <form action="<?=$this->Html->url('/leaderboard')?>" 
          method="get" enctype="application/x-www-form-urlencoded">
          <select name="period" class="styled">
              <option value="weekly" <?=$weekly?>>Mingguan</option>
              <option value="monthly" <?=$monthly?>>Bulanan</option>
              <option value="overall" <?=$overall?>>Keseluruhan</option>
          </select>
        </form>
      </div>
    </div>
    <div class="headbar tr">
        <div class="leaderboard-head fl">
        	<h3>Papan Peringkat Keseluruhan</h3>
            <p>Daftar urutan manajer berdasarkan poin tertinggi secara keseluruhan.<br />Diperbaharui secara mingguan. </p>
        </div>
        <div class="leaderboard-rank fr">
            <span>Peringkat Anda:</span>
            <h3><?=number_format(@$rank)?></h3>
            <span>Tier <?=$tier?></span>
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
                        <th class="alignright">Jumlah Poin</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                  $params = $this->Paginator->params('Weekly_point');
                  
                    foreach($team as $n=>$t):
                      $no = $n+1 + (($params['page']-1) * $params['limit']);

                  ?>
                  <tr class="odd">
                    <td class="l-rank"><?=number_format($no)?></td>
                    <td class="l-club"><?=h($t['Team']['team_name'])?></td>
                    <td class="l-manager"><?=h($t['Manager']['name'])?></td>
                    <td class="l-points alignright"><?=number_format($t['Point']['points'])?></td>
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

<script>
$("select[name='period']").change(function(){
  
  switch($(this).val()){
    case 'monthly':
      document.location="<?=$this->Html->url('/leaderboard/monthly')?>";
    break;
    case 'overall':
      document.location="<?=$this->Html->url('/leaderboard/overall')?>";
    break;
    default:
      document.location="<?=$this->Html->url('/leaderboard')?>";
    break;
  }
});
</script>