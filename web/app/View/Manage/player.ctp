<script>
function profileLoaded(widget, data, id){
    $('.player-detail .opta-widget-container h2 span').html('Player Profile');
}
_optaParams.callbacks = [profileLoaded];
</script>
<div id="myClubPage">
     <?php echo $this->element('infobar'); ?>
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
                    <opta widget="playerprofile" sport="football" competition="8" season="2013" team="1" player="<?=str_replace("p","",$data['player']['player_id'])?>" show_image="true" show_nationality="true" opta_logo="false" narrow_limit="400"></opta>
                </div>
            </div><!-- end #Info -->
            <div id="tabs-Info">
                <table width="300" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                <thead>
                      <tr>
                        <th>Performance Stats</th>
                        <th></th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php 
                        if(isset($data['overall_stats'])):
                            foreach($data['overall_stats'] as $stats):
                    ?>
                      <tr>
                        <td><p class="s-title"><?=$stats['stats_name']?></p></td>
                        <td class="tcenter">
                            <a class="red-arrow"><?=number_format($stats['total'])?></a>
                        </td>
                      </tr>
                    <?php
                        endforeach;
                        endif;
                    ?>
                  </tbody>                    
                </table>
                    
            </div><!-- end #Info -->
            <div class="row">
              <div class="stats">
              
              </div>
            </div>
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
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