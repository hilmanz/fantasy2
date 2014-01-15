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
            <h3 class="fl"><?=h($club['name'])?></h3>
        	<div class="fr"><a href="<?=$this->Html->url('/market')?>" class="button">KEMBALI</a></div>
        </div>
        <div class="contents tr">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable footable">
                <thead>
                    <tr>
                        <th data-class="expand" width="1"></th>
                        <th>Name</th>
                        <th data-hide="phone,tablet" class="aligncenter">Position</th>
                        <th data-hide="phone,tablet" class="alignright">Gaji</th>
                       
                        <th data-hide="phone,tablet" class="alignright">Nilai Transfer</th>
                        <th data-hide="phone" width="1"></th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if(isset($players)):
                    foreach($players as $player):
                       
                        if($player['transfer_value']>0):
                            $urlto = $this->Html->url('/market/player/'.$player['uid']);
                            
                            if(intval(@$player['stats']['last_point'])!=0){
                                $player['transfer_value'] = $player['transfer_value'] + 
                                                            getTransferValueBonus(
                                                                floatval(@$player['stats']['performance']),
                                                                $player['transfer_value']);
                            }
                        
                ?>
                  <tr>
                        <td width="1"><a class="thumbPlayersSmall" href="<?=$urlto?>"><img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description=<?=str_replace('t','',$club['uid'])?>&dimensions=103x155&id=<?=str_replace('p','',$player['uid'])?>"/></a></td>
                        <td><a href="<?=$urlto?>" class="yellow"><?=h($player['name'])?></a></td>
                        <td class="aligncenter"><?=h($player['position'])?></td>
                        <td class="alignright">SS$ <?=number_format($player['salary'])?></td>
                       
                        <td class="alignright">SS$ <?=number_format($player['transfer_value'])?></td>
                        <td>
                            <a href="<?=$urlto?>" 
                                class="button">LIHAT</a>
                        </td>
                  </tr>
                <?php endif;endforeach;endif;?>
                </tbody>
            </table>
            
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #leaderboardPage -->