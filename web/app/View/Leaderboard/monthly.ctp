<?php
$monthly = isset($monthly) ? "selected='selected'":"";
$weekly = isset($weekly) ? "selected='selected'":"";
$overall = isset($overall) ? "selected='selected'":"";
$months = array('','Januari','Februari','Maret','April','Mei',
                    'Juni','Juli','Agustus','September','Oktober',
                    'November','Desember');

$previous_month = ($current_month==1) ? 12 : $current_month - 1;
$previous_year = ($current_month==1) ? $current_year - 1 : $current_year;

$next_month = ($current_month==12) ? 1 : $current_month + 1;
$next_year = ($current_month==12) ? $current_year + 1 : $current_year;

function isMonthAvailable($available,$m,$y){
  foreach($available as $a){
    if($a[0]['bln'] == $m && $a[0]['thn'] == $y){
      return true;
    }
  }
}
?>
<div id="leaderboardPage">
    <div class="headbar tr"> 
       <?php if(isMonthAvailable($available_months,$previous_month,$previous_year)):?>
        <div class="fl">
          <a href="<?=$this->Html->url('/leaderboard/monthly?m='.
                                        ($previous_month).'&y='.$previous_year)?>" 
            class="button"><?=$months[$previous_month]?> <?=$previous_year?></a>
        </div>
        <?php endif;?>

        <?php if(isMonthAvailable($available_months,$next_month,$next_year)):?>
        <div class="fl">
          <a href="<?=$this->Html->url('/leaderboard/monthly?m='.
                                        ($next_month).'&y='.$next_year)?>" 
            class="button"><?=$months[$next_month]?> <?=$next_year?></a>
        </div>
        <?php endif;?>
        
      <div class="fr">
        <form action="<?=$this->Html->url('/leaderboard')?>" 
          method="get" enctype="application/x-www-form-urlencoded">
          <select name="period">
              <option value="weekly" <?=$weekly?>>Mingguan</option>
              <option value="monthly" <?=$monthly?>>Bulanan</option>
              <option value="overall" <?=$overall?>>Keseluruhan</option>
          </select>
        </form>
      </div>
    </div>
    <div class="headbar tr">
        <div class="leaderboard-head fl">
         
        	<h3>Papan Peringkat – Bulan <?=$months[$current_month]?> <?=$current_year?></h3>
            <p>Daftar urutan manajer berdasarkan poin tertinggi tiap bulan.<br />Diperbaharui secara mingguan. </p>
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
                        <th>Jumlah Poin</th>
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