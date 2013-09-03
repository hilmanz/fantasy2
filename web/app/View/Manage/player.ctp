<script>
function profileLoaded(widget, data, id){
    $('.player-detail .opta-widget-container h2 span').html('Player Profile');
    $(".opta-widget-container div.profile-container div.profile dl").find('dt').each(
        function(k,item){
            if($(item).html()=='Name'){
                $(item).next().remove();
                $(item).remove();
            }
        });
}
_optaParams.callbacks = [profileLoaded];
</script>
<div id="myClubPage">
     <?php echo $this->element('infobar'); ?>
     <?php if($data['player']!=null):?>
    <div class="headbar tr">
        <div class="club-info fl">
            
            <div class="fl club-info-entry">
                <h3 class="clubname"><?=h($data['player']['name'])?></h3>
            </div>
        </div>
        <div class="club-money fr">
            <a href="#" class="button">JUAL</a>
        </div>
    </div><!-- end .headbar -->
    <div id="thecontent">
        <div class="content">
            <div id="tabs-Info">
                <div class="player-detail">
                    <opta widget="playerprofile" sport="football" competition="8" season="2013" 
                        team="<?=str_replace('t','',$data['player']['original_team_id'])?>" player="<?=str_replace("p","",$data['player']['player_id'])?>" show_image="true" show_nationality="true" opta_logo="false" narrow_limit="400"></opta>
                </div>
                
            	<div class="profileStats-container" style="display: block;">
                  <h2><span>Performance Stats</span></h2>
                  <div class="profileStatsContainer">
                    <div class="profileStats">

							<?php 
                                if(isset($data['overall_stats'])):
                                    foreach($data['overall_stats'] as $stats):
                                        $stats_name = ucfirst(str_replace("_"," ",
                                                                 $stats['stats_name']));
                            ?>
                              <dl>
                                <dt><p class="s-title"><?=$stats_name?></p></dt>
                                <dd class="tcenter">
                                    <a class="red-arrow"><?=number_format($stats['total'])?></a>
                                </dd>
                              </dl>
                            <?php
                                endforeach;
                                endif;
                            ?>
                    </div><!-- end .profileStats -->
                  </div><!-- end .profileStats-container -->
                </div><!-- end .profileStats-container -->     
            </div><!-- end #Info -->
            <div class="row">
                <div class="stats"></div>
            </div>
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
    <?php else:?>
    <div id="thecontent">
        <div class="content">
            <div>
                <h1 class="yellow">Pemain ini bukan anggota Klab.</h1>
               
               
            </div><!-- end #logoutpage -->
        </div>
    </div>
    <?php endif;?>
</div><!-- end #myClubPage -->
<?=$this->Html->script(array('highcharts'))?>
<script>
var stats  = <?=json_encode($data['stats']);?>;
var categories = [];
var values = [];
$.each(stats,function(k,v){
  categories.push(v.matchday);
  values.push(parseFloat(v.performance));
});
$('.stats').highcharts({
    chart: {
        type: 'line',
        backgroundColor:'transparent',
        style: {
            color: "#fff"
        },
    },
    title: {
        text: 'Performance Valuation',
        style: {
          color: '#fff'
        }
    },
   
    xAxis: {
        categories: categories,
        title:{
           text:'Matchday',
            style:{
              color:'#fff'
            }
        }
    },
    yAxis: {
        title: {
            text: 'Value',
            style:{
              color:'#fff'
            }
        },

    },
    tooltip: {
        enabled: true,
        formatter: function() {
            return 'Matchday '+this.x +': '+ this.y +'';
        }
    },
    plotOptions: {
        line: {
            dataLabels: {
                enabled: true
            },
           
        }
    },
    credits:false,
    series: [{
        name: 'Value',
        data: values
    }]
});
</script>