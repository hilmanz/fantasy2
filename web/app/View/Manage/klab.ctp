<?php
//tokenized staff list
$staff_token = array();
$total_expenses = 0;
$total_expenses+= intval(@$finance['operating_cost']);
$total_expenses+= intval(@$finance['player_salaries']);
$total_expenses+= intval(@$finance['commercial_director']);
$total_expenses+= intval(@$finance['marketing_manager']);
$total_expenses+= intval(@$finance['public_relation_officer']);
$total_expenses+= intval(@$finance['head_of_security']);
$total_expenses+= intval(@$finance['football_director']);
$total_expenses+= intval(@$finance['chief_scout']);
$total_expenses+= intval(@$finance['general_scout']);
$total_expenses+= intval(@$finance['finance_director']);
$total_expenses+= intval(@$finance['tax_consultant']);
$total_expenses+= intval(@$finance['accountant']);
$total_expenses+= intval(@$finance['buy_player']);

$starting_balance = intval(@$finance['budget']) 
                    - intval(@$finance['total_earnings']) 
                    + abs(intval(@$total_expenses));
foreach($staffs as $staff){
  $staff_token[] = str_replace(" ","_",strtolower($staff['name']));
}
function isStaffExist($staff_token,$name){ 
  foreach($staff_token as $token){
    if($token==$name){
      return true;
    }
  }
}
?>
<div id="klabPage">
    <div id="thecontent">
    	<div class="row">
			<div id="clubtabs">
              <ul>
                <li><a href="#tabs-Info">Info</a></li>
                <li><a href="#tabs-Money">Keuangan</a></li>
                <li><a href="#tabs-Players">Pemain</a></li>
                <li><a href="#tabs-Staff">Staff</a></li>
              </ul>
              <div id="tabs-Info">

                <div class="row">
					<div class="col3 fl">
						<div class="widget RingkasanKlab">
							<h3>Ringkasan Klab</h3>
							<div class="entry tr">
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
									<td align="center"><a href="#"><img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/></a></td>
									<td>
										<span>Rank: <strong><?=number_format($USER_RANK)?></strong></span>
										<span>Uang: <strong>SS$ <?=number_format($team_bugdet)?></strong></span>
										<span>Point: <strong><?=number_format($USER_POINTS)?></strong></span>
									</td>
								  </tr>
								  <tr>
									<td colspan="2" class="pendapatan">
										<span class="fl">Gaji Mingguan:</span><strong class="fr">SS$ <?=number_format($weekly_salaries)?></strong>
										<span class="fl">Pendapatan Minggu lalu:</span> <strong class="fr">SS$ <?=number_format($last_earning)?></strong>
										<span class="fl">Pengeluaran Minggu lalu:</span><strong class="fr">SS$ <?=number_format($last_expenses)?></strong>
									</td>
								  </tr>
								</table>
							</div><!-- end .entry -->
						</div><!-- end .widget -->
					</div><!-- end .col3 -->
					<div class="col3 fl">
						<div class="widget PergerakanRanking">
							<h3>Pergerakan Poin</h3>
							<div class="entry tr">
								<div id="chart_ranking" class="chartbox">
									
								</div>
							</div><!-- end .entry -->
						</div><!-- end .widget -->
					</div><!-- end .col3 -->
					<div class="col3 fl">
						<div class="widget PergerakanKeuangan">
							<h3>Pergerakan Keuangan</h3>
							<div class="entry tr">
								<div id="chart_keuangan" class="chartbox">
									
								</div>
							</div><!-- end .entry -->
						</div><!-- end .widget -->
					</div><!-- end .col3 -->
				</div><!-- end .row -->
				<div class="row">
					<div class="col3 fl">
						<div class="widget PertandinganLalu">
							<h3>Pertandingan Lalu</h3>
							<div class="entry tr">
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <thead>
									<tr>
									  <th width="50">Game</th>
									  <th width="10">Points</th>
									  <th>Pendapatan</th>
									</tr>
								  </thead>
								  <tbody id="myplayerlist">
								  	<?php if(isset($matches)):foreach($matches as $m):?>
								  	<?php
								  	
								  		$hidden_params = encrypt_param(serialize($m));
								  		extract($m);
								  		if($home_id==$club['team_id']){
								  			$versus = $away_name;
								  		}else{
								  			$versus = $home_name;
								  		}
								  	?>
									<tr id="p50004" class="odd">
									  <td><a href="<?=$this->Html->url('/manage/matchinfo?game_id='.$game_id).'&r='.$hidden_params?>">Vs. <?=h($versus)?></a></td>
									  <td><?=number_format($points)?></td>
									  <td>SS$ <?=number_format($income)?></td>
									</tr>
									<?php endforeach;endif;?>
		                          </tbody>
		                       	</table>
							</div><!-- end .entry -->
						</div><!-- end .widget -->
					</div><!-- end .col3 -->
					<div class="col-content fl">
						<div class="widget PemainTerbaik">
							<h3>Pemain Terbaik</h3>
							<div class="entry tr">
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <thead>
									<tr>
									  <th width="50"></th>
									  <th width="210">Nama</th>
									  <th width="50">Umur</th>
									  <th width="70">Posisi</th>
									 
									  <th width="120">Poin</th>
									  <th width="120">Nilai</th>
									 
									</tr>
								  </thead>
								  <tbody id="myplayerlist">
									
									 <?php $n_best = 0;?>				  	
									  <?php foreach($best_players as $player):?>
					                  <?php
					                    switch($player['position']){
					                      case 'Goalkeeper':
					                        $player_pos = "Goalkeeper";
					                        $color = "grey";
					                      break;
					                      case 'Midfielder':
					                        $player_pos = "Midfielder";
					                        $color = "yellow";
					                      break;
					                      case 'Forward':
					                        $player_pos = "Forward";
					                        $color  = "red";
					                      break;
					                      default:
					                        $player_pos = "Defender";
					                        $color = "blue";
					                      break;
					                    }
					                    
					                   
					                  ?>
					                  <tr id="<?=$player['uid']?>">
					                    <td>
					                     <a class="thumbPlayers" href="<?=$this->Html->url('/manage/player/'.$player['uid'])?>"> <img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description=<?=str_replace('t','',$player['team_id'])?>&dimensions=103x155&id=<?=str_replace('p','',$player['uid'])?>"/></a>
					                    </td>
					                    <td>
					                      <a class="yellow" href="<?=$this->Html->url('/manage/player/'.$player['uid'])?>"><?=h($player['name'])?></a></td>
					                    
					                    <td><?=round((time()-strtotime($player['birth_date']))/(24*60*60*365))?></td>
					                   
					                    <td><?=$player_pos?></td>
					                   
					                    
					                    <?php
					                      if($player['points']>0){
					                        $performance_bonus = round(floatval($player['last_performance']/100) * 
					                                            intval($player['transfer_value']));
					                      }else{
					                        $performance_bonus = 0;
					                      }
					                    ?>
					                    <td><?=number_format(intval($player['points']))?></td>
					                    <td><?=number_format(intval($player['transfer_value'])+$performance_bonus)?></td>
					                    
					                  </tr>
					                  <?php if($n_best==4){break;}else{$n_best++;}?>
					                  <?php endforeach;?>
								  </tbody>
								</table>


							</div><!-- end .entry -->
						</div><!-- end .widget -->
					</div><!-- end .col-content -->
				</div><!-- end .row -->
              </div><!-- end #Info -->
              <div id="tabs-Money">
                    <table cellspacing="0" cellpadding="0" width="100%">
                      <tr class="head">
                        <td colspan="5">Neraca Minggu Lalu</td>
                        <td align="right" class="prevbalance">SS$ <?=number_format($starting_balance)?></td>
                      </tr>
                      <tr>
                        <td>Tiket</td>
                        <td></td>
                        <td></td>
                        <td>Pertandingan</td>
                        <td>Total Perolehan</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Tiket Terjual</td>
                        <td align="right"></td>
                        <td align="right"></td>
                        <td align="right"><?=@$finance['total_matches']?></td>
                        <td align="right">SS$ <?=number_format(@$finance['tickets_sold'])?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Pemasukan Tambahan</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php
                      if(isStaffExist($staff_token,'commercial_director')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Bonus Commercial Director</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['commercial_director_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php endif;?>
                      <?php
                      if(isStaffExist($staff_token,'marketing_manager')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Bonus Marketing Manager</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['marketing_manager_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php endif;?>
                       <?php
                      if(isStaffExist($staff_token,'public_relation_officer')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Bonus Public Relations</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['public_relation_officer_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                     <?php endif;?>
                      <tr>
                        <td>Sponsor</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['sponsorship']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php
                        if(isset($finance['player_sold'])):
                      ?>

                      <tr>
                        <td>Penjualan Pemain</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['player_sold']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php
                      endif;
                      ?>
                      <tr>
                        <td>Bonus</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Kemenangan</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['win_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Total Perolehan</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['total_earnings']))?></td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Biaya Operasional</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['operating_cost']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                     
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="6">Gaji</td>
                      </tr>
                      
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Gaji Pemain</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['player_salaries']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      
                       <?php
                      if(isStaffExist($staff_token,'commercial_director')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Commercial Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['commercial_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                       
                      <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'marketing_manager')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Marketing Manager</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['marketing_manager']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                       
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'public_relation_officer')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Public Relations</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['public_relation_officer']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'head_of_security')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Head of Security</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['head_of_security']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                        
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'football_director')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Footbal Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['football_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                        
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'chief_scout')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Chief Scout</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['chief_scout']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                        
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'general_scout')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>General Scout</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['general_scout']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                        
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'finance_director')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Finance Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['finance_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                       
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'tax_consultant')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Tax Consultant</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['tax_consultant']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                       
                       <?php endif;?>
                        <?php
                      if(isStaffExist($staff_token,'accountant')):
                      ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Accountant</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['accountant']))?></td>
                        <td align="right"></td>
                      </tr>
                        
                       <?php endif;?>
                       <?php
                        if(isset($finance['buy_player'])):
                      ?>

                      <tr>
                        <td>Pembelian Pemain</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">SS$ <?=number_format(abs(@$finance['buy_player']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <?php
                      endif;
                      ?>

                      <tr class="head">
                        <td colspan="4">Total Pengeluaran</td>
                        <td><td align="right">SS$ <?=number_format(@$total_expenses)?></td></td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Neraca Berjalan</td>
                        <td align="right">SS$ <?=number_format(@$finance['budget'])?></td>
                    </tr>
                   </table>
              </div><!-- end #tabs-Keuagan -->
              <div id="tabs-Players">
                <div class="player-list">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <thead>
                  <tr>
                    <th></th>
                    <th width="210">Nama</th>
                    <th width="50">Umur</th>
                    <th width="64">Tgl.Lahir</th>
                    <th width="64">Negara</th>
                    <th width="70">Posisi</th>
                   
                    <th width="120">Gaji*</th>
                    <th width="120">Poin</th>
                    <th width="120">Nilai</th>
                    <th style="text-align:center;">Tindakan</th>
                  </tr>
                 </thead>
                 <tbody  id="myplayerlist">
                  <?php foreach($players as $player):?>
                  <?php
                    switch($player['position']){
                      case 'Goalkeeper':
                        $player_pos = "Goalkeeper";
                        $color = "grey";
                      break;
                      case 'Midfielder':
                        $player_pos = "Midfielder";
                        $color = "yellow";
                      break;
                      case 'Forward':
                        $player_pos = "Forward";
                        $color  = "red";
                      break;
                      default:
                        $player_pos = "Defender";
                        $color = "blue";
                      break;
                    }
                  ?>
                  <tr id="<?=$player['uid']?>">
                    <td>
                     <a class="thumbPlayers" href="<?=$this->Html->url('/manage/player/'.$player['uid'])?>"> <img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description=<?=str_replace('t','',$player['team_id'])?>&dimensions=103x155&id=<?=str_replace('p','',$player['uid'])?>"/></a>
                    </td>
                    <td>
                      <a class="yellow" href="<?=$this->Html->url('/manage/player/'.$player['uid'])?>"><?=h($player['name'])?></a></td>
                    
                    <td><?=round((time()-strtotime($player['birth_date']))/(24*60*60*365))?></td>
                    <td><?=date("d-m-Y",strtotime($player['birth_date']))?></td>
                    <td><?=h($player['country'])?></td>
                    <td><?=$player_pos?></td>
                   
                    <td><?=number_format($player['salary'])?></td>
                    <?php
                      if($player['points']>0){
                        $last_performance = floatval($player['last_performance']);
                        
                        $performance_bonus = (((($last_performance / 10) * 1)/100) * 
                                            intval($player['transfer_value']));
                      }else{
                        $performance_bonus = 0;
                      }
                    ?>
                    <td><?=number_format(intval($player['points']))?></td>
                    <td><?=number_format(intval($player['transfer_value'])+$performance_bonus)?></td>
                    <td width="10"><a data-team-name="<?=h($club['team_name'])?>" data-player-name="<?=$player['name']?>" data-team="<?=$player['team_id']?>" data-player="<?=$player['uid']?>" id="btnSale" class="icon-cart buttons" href="#popup-messages"><span>Jual</span></a></td>
                  </tr>
                  <?php endforeach;?>
                  <tr>
                    <td colspan="10">*) Gaji Per Minggu</td>
                  </tr>
                 </tbody>
                </table>
                </div><!-- end .player-list -->
              </div><!-- end #tabs-Squad -->
              <div id="tabs-Staff">
                    <div class="staff-list">
                      <?php
                                foreach($staffs as $official):
                                  $img = str_replace(' ','_',strtolower($official['name'])).'.jpg';
                          ?>
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="<?=$this->Html->url('/content/thumb/'.$img)?>" />
                                </div><!-- end .avatar-big -->
                                <p><?=h($official['name'])?></p>
                                <div>
                                    SS$ <?=number_format($official['salary'])?> / minggu
                                </div>
                            </div><!-- end .thumbStaff -->
                            <?php
                                endforeach;
                            ?>
                    </div><!-- end .staff-list -->
                     <div class="row">
                        <a href="<?=$this->Html->url('/manage/hiring_staff')?>" class="button">Kelola Staff</a>
                    </div>
              </div><!-- end #tabs-Staff -->
            </div><!-- end #clubtabs -->
		</div>
		
		
    </div><!-- end #thecontent -->
</div><!-- end #faqPage -->

<!--popups-->
<div class="popup">
    <div class="popupContainer popup-small" id="popup-messages">
        <div class="popupHeader">
        </div><!-- END .popupHeader -->
        <div class="popupContent">
            <div class="entry-popup">
                yellow
            </div><!--END .entry-popup-->
        </div><!-- END .popupContent -->
    </div><!-- END .popupContainer -->
</div><!-- END .popup --> 


<script>
$("#btnSale").fancybox({
    beforeLoad : function(){
      $("#popup-messages .popupContent .entry-popup").html('');
      $('.saving').hide();
      $('.confirm').show();
      $('.success').hide();
      $('.failure').hide();
      render_view(tplsale,"#popup-messages .popupContent .entry-popup",{
        player_id:$(this.element).data('player'),
        team_id:$(this.element).data('team'),
        player_name:$(this.element).data('player-name'),
        team_name:$(this.element).data('team-name'),
      });
      $jqOpta.widgetStart(_optaParams);
    },
});
</script>
<script type="text/template" id="tplsale">
    <%
      var uid = player_id.replace('p','');
      var team = team_id.replace('t','');
    %>
    <div class="confirm">
        <h1>Apakah kamu ingin menjual pemain ini?</h1>
        <h3>Pemain yang sudah dijual akan hilang dari lineup dan tidak dapat di undo</h3>
        <opta widget="playerprofile" sport="football" competition="8" season="2013" team="<%=team%>" 
          player="<%=uid%>" show_image="true" show_nationality="true" opta_logo="false" 
          narrow_limit="400"></opta>
        <p><a href="#/sale/<%=player_id%>/0" class="button">Jual</a>
            <a href="#" class="button" onclick="$.fancybox.close();return false;">Batal</a></p>
    </div>
    <div class="saving" style="display:none;">
        <h1>Menjual Pemain.</h1>
        <h3>Harap tunggu sebentar..</h3>
        <p><img src="<?=$this->Html->url('/css/fancybox/fancybox_loading@2x.gif')?>"/></p>
    </div>
    <div class="success" style="display:none;">
        <h1>Penjualan Berhasil</h1>
        <h3><%=player_name%> sudah dijual dari <%=team_name%></h3>
    </div>
    <div class="failure" style="display:none;">
        <h1>Penjualan Tidak Berhasil</h1>
        <h3>Silahkan coba kembali !</h3>
    </div>
</script>

<?php if(isset($tab)):?>
<script>
$( "#clubtabs" ).tabs({active:<?=intval($tab)?>});
</script>
<?php endif;?>

<?=$this->Html->script(array('highcharts'))?>

<script>
var stats = <?=json_encode($weekly_points)?>;

var categories = [];
var data = [];
$.each(stats,function(k,v){
  categories.push(v.matchday);
  data.push(parseFloat(v.points));
});


$('#chart_ranking').highcharts({
	colors: ['#c00', '#e12626', '#999999'],
    chart: {
        type: 'area',
        backgroundColor:'#000',
        style: {
            color: "#fff"
        },
    },
    title: {
        text: '',
        style: {
          color: '#fff'
        }
    },
   
    xAxis: {
        
        categories: categories,
       	title: {
            text: 'Minggu',
            style:{
              color:'#fff'
            }
        },
    },
    yAxis: {
        title: {
            text: 'Total',
            style:{
              color:'#fff'
            }
        },

    },
    tooltip: {
        enabled: true,
        formatter: function() {
            return '<strong>Minggu '+this.x+'</strong><br/>'+
                    this.series.name+': '+ Highcharts.numberFormat(this.y,0) +'';
        }
    },
    plotOptions: {
        area: {
            stacking: 'normal',
            lineColor: '#666666',
            lineWidth: 1,
            marker: {
                lineWidth: 1,
                lineColor: '#666666'
            }
        }
    },
    legend: {
      enabled: false
    },
    credits:false,
    series: [
        {
            name: 'Poin',
            data: data
        },
    ]
});

var stats = <?=json_encode($weekly_balances)?>;

categories = [];
data = [];
$.each(stats,function(k,v){
  categories.push(v.week);
  data.push(parseFloat(v.balance));
});

$('#chart_keuangan').highcharts({
	colors: ['#c00', '#e12626', '#999999'],
    chart: {
        type: 'area',
        backgroundColor:'#000',
        style: {
            color: "#fff"
        },
    },
    title: {
        text: '',
        style: {
          color: '#fff'
        }
    },
   
    xAxis: {
        
        categories: categories,
       	title: {
            text: 'Minggu',
            style:{
              color:'#fff'
            }
        },
    },
    yAxis: {
        title: {
            text: 'Saldo',
            style:{
              color:'#fff'
            }
        },

    },
    legend: {
      enabled: false
    },
    tooltip: {
        enabled: true,
        formatter: function() {
            return '<strong>Minggu '+this.x+'</strong><br/>'+
                    ': SS$'+ Highcharts.numberFormat(this.y,0) +'';
        }
    },
    plotOptions: {
        area: {
            stacking: 'normal',
            lineColor: '#666666',
            lineWidth: 1,
            marker: {
                lineWidth: 1,
                lineColor: '#666666'
            }
        }
    },
    credits:false,
    series: [
        {
            name: 'Saldo',
            data: data
        },
    ]
});

</script>