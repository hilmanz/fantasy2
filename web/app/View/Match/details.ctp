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
        <div id="content">
            <div class="content">
                <div class="row-2">
                    <h1 class="red">MATCH DETAILS</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <div class="row">
                    <table width="100%">
                        <tr>
                            <td><?=$o['data'][0]['name']?></td>
                            <td><?=$o['data'][0]['score']?> - <?=$o['data'][1]['score']?></td>
                            <td><?=$o['data'][1]['name']?></td>
                        </tr>
                        <tr>
                            <td valign="top">
                                <table width="100%">
                                    <?php foreach($o['data'][0]['overall_stats'] as $stats=>$val):?>
                                    <tr>
                                        <td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </table>
                                <table width="100%">
                                    <?php foreach($o['data'][0]['player_stats'] as $player=>$data):?>
                                    <tr style="background-color:#353535;color:white;padding:5px">
                                        <td><?=$data['name']?></td><td><?=$data['position']?></td>
                                    </tr>
                                        <?php
                                            foreach($data['stats'] as $stats=>$val):
                                        ?>
                                        <tr>
                                            <td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
                                        </tr>
                                        <?php endforeach;?>
                                    <?php endforeach;?>
                                </table>
                            </td>
                            <td>
                                
                            </td>
                            <td valign="top">
                                <table width="100%">
                                    <?php foreach($o['data'][1]['overall_stats'] as $stats=>$val):?>
                                    <tr>
                                        <td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
                                    </tr>
                                    <?php endforeach;?>
                                </table>
                                <table width="100%">
                                    
                                    <?php foreach($o['data'][1]['player_stats'] as $player=>$data):?>
                                    <tr style="background-color:#353535;color:white;padding:5px">
                                        <td><?=$data['name']?></td><td><?=$data['position']?></td>
                                    </tr>
                                        <?php
                                            foreach($data['stats'] as $stats=>$val):
                                        ?>
                                        <tr>
                                            <td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
                                        </tr>
                                        <?php endforeach;?>
                                    <?php endforeach;?>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="row">
                        <a href="<?=$this->Html->url('/match')?>" class="button">Back to List</a>
                    </div>
                </div>
            </div><!-- end .content -->
        </div><!-- end #content -->
    <div id="sidebar" class="tr">
        
    </div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->