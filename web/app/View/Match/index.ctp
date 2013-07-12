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
                    <h1 class="red">MATCHES</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <div class="row">
                    <table cellspacing="0" cellpadding="0" width="100%">
                        <tr class="head">
                            <td>Matchday</td><td>Home</td><td width="50px" align="center"></td><td>Away</td><td>Status</td><td>Details</td>
                        </tr>
                        <?php
                            foreach($matches as $match):
                        ?>
                        <tr>
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
                            <td><a href="<?=$this->Html->url('/match/details/'.$match['game_id'])?>">View</a></td>
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