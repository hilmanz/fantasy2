<div id="myClubPage">
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
    <div class="headbar tr">
        <div class="club-info fl">
            <a class="thumb-club fl"><img src="<?=$this->Html->url('/images/team/logo1.png')?>" /></a>
            <div class="fl club-info-entry">
                <h3 class="clubname"><?=h($club['team_name'])?></h3>
                <h3 class="datemember"><?=h(date("d-m-Y",strtotime($user['register_date'])))?></h3>
            </div>
        </div>
        <div class="club-money fr">
            <a href="<?=$this->Html->url('/manage/club#tabs-Players')?>" class="button">Back</a>
        </div>
    </div><!-- end .headbar -->
    <div id="thecontent">
        <div class="content">
            <div id="tabs-Info">
              <div class="avatar-big fl">
                  <img src="<?=$this->Html->url('/content/thumb/default_avatar.png')?>" />
              </div>
              <div class="user-details fl">
                  <h3 class="username"><?=h($data['player']['name'])?></h3>
                  <h3 class="useremail"><?=h($data['player']['position'])?></h3>
                
              </div><!-- end .row -->
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