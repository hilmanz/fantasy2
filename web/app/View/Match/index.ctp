<div id="fillDetailsPage">
     <?php echo $this->element('infobar'); ?>
    <div id="thecontent">
        <div id="content">
            <div class="content">
                <div class="row-2">
                    <h1 class="red">Daftar Pertandingan</h1>
                    <p>Lihat info lengkap daftar seluruh pertandingan yang telah/sedang/akan berjalan. Dari waktu tanding, status kandang/tandang masing-masing tim, hingga rincian statistik tiap pertandingan, semua bisa didapatkan di sini.</p>
                </div><!-- end .row-2 -->
                <div class="row">
                    <table cellspacing="0" cellpadding="0" width="100%">
                        <tr class="head">
                            <td>Hari Tanding</td><td>Tim Kandang</td><td width="50px" align="center"></td><td>Tim Tandang</td><td>Status</td><td>Rincian</td>
                        </tr>
                        <?php
                            foreach($matches as $match):
                        ?>
                        <?php if($match['my_match']):?>
                        <tr class="mymatch">
                        <?php else:?>
                        <tr>
                        <?php endif;?>
                            <td><?=$match['matchday']?></td>
                            <td><?=$match['home_name']?></td>
                            <td align="center">
                                <?php
                                if($match['period']=='PreMatch'){
                                    print "? - ?";
                                }else{
                                    print intval($match['home_score']).' - '.intval($match['away_score']);
                                }
                                ?>
                            </td>
                            <td><?=$match['away_name']?></td><td><?=$match['period']?></td>
                            <td><a href="<?=$this->Html->url('/match/details/'.$match['game_id'])?>">Lihat</a></td>
                        </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            </div><!-- end .content -->
        </div><!-- end #content -->
    <div id="sidebar" class="tr">
        
    </div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->