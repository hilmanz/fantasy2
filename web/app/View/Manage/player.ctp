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
              <div class="avatar-big fl">
                  <img src="<?=$this->Html->url('/content/thumb/default_avatar.png')?>" />
              </div>
              <div class="user-details fl">
                  <table width="100%">
                        <tr>
                            <td>Posisi</td>
                            <td><?=h($data['player']['position'])?></td>
                        </tr>
                        <tr>
                            <td>Umur</td>
                            <td>
                                <?php
                                    $t = strtotime($data['player']['birth_date']);
                                    $d = time() - $t;
                                    $age = round($d/(24*60*60*365));
                                    echo $age;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Tgl Lahir</td>
                            <td><?=date("d/m/Y",strtotime($data['player']['birth_date']))?></td>
                        </tr>
                        <tr>
                            <td>Negara</td>
                            <td><?=h($data['player']['country'])?></td>
                        </tr>
                        <tr>
                            <td>Salary</td>
                            <td><?=number_format($data['player']['salary'])?></td>
                        </tr>
                  </table>
              </div><!-- end .row -->
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