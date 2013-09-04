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
var club_url = "<?=$this->Html->url('/manage/club/?tab=2')?>";
<?php
if(isset($data['daily_stats'])):
?>
var daily_stats = JSON.parse('<?=json_encode($data['daily_stats'])?>');
<?php
else:
?>
var daily_stats = {};
<?php endif;?>

</script>
<?php
//mapping statistics
$map = array(
    'goals_and_assists'=>array(
                            'goals'=>'goals',
                            'assist'=>'goal_assist',
                            'clear_cut_chance_created'=>'big_chance_created',
                            'penalty_won'=>'penalty_won'
                        ),

    'shooting'=>array(
                    'shoot_on_target'=>'ontarget_scoring_att',
                    'on_target_shot_from_outside_the_box'=>'att_obox_target'
                ),

    'passing'=>array(
                'accurate_flick_on'=>'accurate_flick_on',
                'accurate_pass'=>'accurate_pass',
                'accurate_chipped_pass'=>'accurate_chipped_pass',
                'accurate_launches'=>'accurate_launches',
                'accurate_layoffs'=>'accurate_layoffs',
                'accurate_long_balls'=>'accurate_long_balls',
                'accurate_through_balls'=>'accurate_through_balls',
                'long_pass_to_opponents_half'=>'long_pass_own_to_opp_success',
                'accurate_crossing'=>'accurate_cross',
                'accurate_attacking_pass'=>'accurate_fwd_zone_pass',
                'accurate_free_kick_delivery'=>'accurate_freekick_cross'
            ),
    'defending'=>array(
                'Ball_recovery'=>'ball_recovery',
                'Duel_Won'=>'duel_won',
                'Aerial_Duel_Won'=>'aerial_won',
                'Tackle_won'=>'tackle_won',
                'Tackle_won_as_a_last_man'=>'last_man_contest',
                'Intercepted_passes'=>'Interception',
                'Intercepted_a_pass_inside_the_box'=>'interceptions_in_box',
                'Effective_clearance'=>'effective_clearence',
                'Blocked_a_cross'=>'effective_blocked_cross',
                'Blocked_a_shot'=>'effective_blocked_shot',
                'Blocked_a_shot_from_within_6_yards_box'=>'six_yard_block'

        ),

    'goalkeeping'=>array(
                'Penalty_Save'=>'penalty_save',
                'Diving_Save'=>'dive_save',
                'Diving_Save_and_Caught_the_ball'=>'dive_catch',
                'Standing_save'=>'stand_save',
                'Standing_save_and_Caught_the_ball'=>'stand_catch',
                'Claimed_a_cross'=>'good_claim',
                'Claimed_a_high_cross_into_the_box'=>'good_high_claim',
                'Punched_the_ball_away'=>'punches',
                'Won_a_1v1_challenge'=>'good_one_on_one',
                'Smothered_an_attack'=>'gk_smother'
        ),

    'discipline'=>array(
        'Yellow_Card'=>'yellow_card',
        'Red_Card'=>'red_card'
        ),

    'mistakes_and_errors'=>array(
        'Penalty_Conceded'=>'penalty_conceded',
        'Dispossessed'=>'dispossessed',
        'Error_that_led_to_a_goal'=>'error_lead_to_goal',
        'Error_that_lead_to_a_shot'=>'error_lead_to_shot',
        'Poor_pass'=>'poss_lost_ctrl',
        'Poor_touch'=>'unsuccessful_touch',
        'challenge_lost'=>'challenge_lost',
        'Cross_not_claimed'=>'cross_not_claimed'
    )
);
switch($data['player']['position']){
    case 'Forward':
        $pos = "f";
    break;
    case 'Midfielder':
        $pos = "m";
    break;
    case 'Defender':
        $pos = "d";
    break;
    default:
        $pos = 'g';
    break;
}
$total_points = 0;
$main_stats_vals = array('goals_and_assists'=>0,
                            'shooting'=>0,
                            'defending'=>0,
                            'passing'=>0,
                            'discipline'=>0,
                            'goalkeeping'=>0,
                            'mistakes_and_errors'=>0,
                         );
if(isset($data['overall_stats'])){
    foreach($data['overall_stats'] as $stats){
      
        foreach($map as $mainstats=>$substats){
            foreach($substats as $n=>$v){
                
                if($v==$stats['stats_name']){
                    if(!isset($main_stats_vals[$mainstats])){
                        $main_stats_vals[$mainstats] = 0;
                    }
                    $main_stats_vals[$mainstats] += ($stats['total'] *
                                                    getModifierValue($modifiers,
                                                                            $v,
                                                                            $pos));
                }
            }
        }
    }
    foreach($main_stats_vals as $n){
        $total_points += $n;
    }

}

function getModifierValue($modifiers,$statsName,$pos){
    foreach($modifiers as $m){
        if($m['Modifier']['name']==$statsName){
            return abs($m['Modifier'][$pos]);
        }
    }
    return 0;
}
function getStats($category,$pos,$modifiers,$map,$stats){
    
    
    $statTypes = $map[$category];
    //pr($statTypes);
    $collection = array();
    foreach($stats as $s){
        foreach($statTypes as $n=>$v){
            if(!isset($collection[$n])){
                $collection[$n] = 0;
            }
            if($s['stats_name'] == $v){
                $collection[$n] = $s['total'] * getModifierValue($modifiers,$v,$pos);
            }
        }
    }
    
    return $collection;
}


?>
<div id="myClubPage">
     <?php echo $this->element('infobar'); ?>
     <?php if($data['player']!=null):?>
    <div class="headbar tr">
        <div class="club-info fl player-club">
            <div class="fl club-info-entry">
                <h3 class="clubname"><?=h($data['player']['name'])?></h3>
            </div>
        </div>
        <div class="club-info fl">
            <p>Gaji</p>
            <h4><?=number_format($data['player']['salary'])?></h4>
        </div>
        <div class="club-info fl">
            <p>Nilai Transfer</p>
            <h4>
                <?php
                    if(sizeof($data['stats'])>0){
                        $performance = $data['stats'][sizeof($data['stats'])-1]['performance'];
                    }else{
                        $performance = 0;
                    }
                    $bonus = round($data['player']['transfer_value'] * ($performance/100));
                    $transfer_value = $data['player']['transfer_value'] + $bonus;
                    echo number_format($transfer_value);
                ?>
            </h4>
        </div>
        <div class="club-info fl">
            <p>Status</p>
            <h4>N/A</h4>
        </div>
        <div class="club-info fl">
            <h5>
                <?php
                    print number_format($total_points);
                ?>
                Poin
            </h5>
        </div>
        <div class="club-money fr">
            <a data-team-name="<?=h($club['team_name'])?>" data-player-name="<?=$data['player']['name']?>" data-team="<?=$data['player']['original_team_id']?>" data-player="<?=$data['player']['player_id']?>" id="btnSale" class="icon-cart buttons" href="#popup-messages"><span>JUAL</span></a>
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
                  <h2><span>Weekly Performance</span></h2>
                  <div class="profileStatsContainer">
                    <div class="profileStats" style="overflow:hidden;">
                        <a href="#/stats_detail/0" class="statsbox">
                            <h4>Goals &amp; Assists</h4>
                            <p><?=number_format($main_stats_vals['goals_and_assists'])?></p>
                        </a>
                        <a href="#/stats_detail/1" class="statsbox">
                            <h4>Shooting</h4>
                            <p><?=number_format($main_stats_vals['shooting'])?></p>
                        </a>
                        <a href="#/stats_detail/2" class="statsbox">
                            <h4>Passing</h4>
                            <p><?=number_format($main_stats_vals['passing'])?></p>
                        </a>
                        <?php 
                        if($data['player']['position']!='Goalkeeper'):
                        ?>
                        <a href="#/stats_detail/3" class="statsbox">
                            <h4>Defending</h4>
                            <p><?=number_format($main_stats_vals['defending'])?></p>
                        </a>
                        <?php
                        endif;
                        ?>
                        <?php 
                        if($data['player']['position']=='Goalkeeper'):
                        ?>
                        <a href="#/stats_detail/3" class="statsbox">
                            <h4>Goalkeeping</h4>
                            <p><?=number_format($main_stats_vals['goalkeeping'])?></p>
                        </a>
                        <?php endif;?>
                        <a href="#/stats_detail/4" class="statsbox">
                            <h4>Discipline</h4>
                            <p><?=number_format($main_stats_vals['discipline'])?></p>
                        </a>
                        <a href="#/stats_detail/5" class="statsbox">
                            <h4>Mistakes &amp; Errors</h4>
                            <p><?=number_format($main_stats_vals['mistakes_and_errors'])?></p>
                        </a>
                    </div><!-- end .profileStats -->
                  </div><!-- end .profileStats-container -->
                </div><!-- end .profileStats-container -->     
            </div><!-- end #Info -->
            <div id="chartbox" class="row">
                <div class="stats"></div>
            </div>
            <div id="profiletabs" style="display:none">
              <h3 class="tabtitle">Defending</h3>
              <div class="fr">
                <a href="#/close_detail" class="button">KEMBALI</a>
                </div>
              <ul>
                <li><a href="#tabs-Goals">Goals and Shootings</a></li>
                <li><a href="#tabs-Shooting">Shooting</a></li>
                <li><a href="#tabs-Passing">Passing</a></li>
                <?php if($data['player']['position']=='Goalkeeper'):?>
                <li><a href="#tabs-Goalkeeping">Goalkeeping</a></li>
                <?php else: ?>
                <li><a href="#tabs-Defending">Defending</a></li>
                <?php endif;?>
                <li><a href="#tabs-Discipline">Discipline</a></li>

                <li><a href="#tabs-Mistakes">Mistakes &amp; Errors</a></li>
              </ul>
              <div id="tabs-Goals">
                <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('goals_and_assists',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #Info -->
              <div id="tabs-Shooting">
                  <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('shooting',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Keuagan -->
              <div id="tabs-Passing">
               <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('passing',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Squad -->
              <?php if($data['player']['position']=='Goalkeeper'):?>
              <div id="tabs-Goalkeeping">
                   <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('goalkeeping',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Staff -->
              <?php else:?>
              <div id="tabs-Defending">
                  <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('defending',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Staff -->
              <?php endif;?>
              <div id="tabs-Discipline">
                   <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('discipline',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Staff -->
              <div id="tabs-Mistakes">
                   <div class="profileStatsContainer">
                    <div class="profileStats">
                        <?php 
                            $profileStats = getStats('mistakes_and_errors',$pos,$modifiers,$map,$data['overall_stats']);
                            if(isset($profileStats)):
                                foreach($profileStats as $statsName=>$statsVal):
                                    $statsName = ucfirst(str_replace('_',' ',$statsName));
                        ?>
                          <dl>
                            <dt><p class="s-title"><?=$statsName?></p></dt>
                            <dd class="tcenter">
                                <a class="red-arrow"><?=number_format($statsVal)?></a>
                            </dd>
                          </dl>
                        <?php
                            endforeach;
                            endif;
                        ?>
                    </div><!-- end .profileStats -->
                </div><!-- end .profileStats-container -->
              </div><!-- end #tabs-Staff -->
            </div><!-- end #clubtabs -->
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




<?=$this->Html->script(array('highcharts'))?>
<script>
var stats  = [];
for(var i in daily_stats){
    stats.push({
        ts:daily_stats[i].fixture.ts*1000,
        goals_and_assists:daily_stats[i].goals_and_assists,
        shooting:daily_stats[i].shooting,
        passing:daily_stats[i].passing,
        defending:daily_stats[i].defending,
        goalkeeping:daily_stats[i].goalkeeping,
        discipline:daily_stats[i].discipline,
        mistakes:daily_stats[i].mistakes
    });
}
var categories = [];
var goals_and_assists = [];
var shooting = [];
var passing = [];
var defending = [];
var goalkeeping = [];
var discipline = [];
var mistakes = [];

$.each(stats,function(k,v){
  categories.push(v.ts);
  goals_and_assists.push(parseFloat(v.goals_and_assists));
  shooting.push(parseFloat(v.shooting));
  passing.push(parseFloat(v.passing));
  defending.push(parseFloat(v.defending));
  goalkeeping.push(parseFloat(v.goalkeeping));
  discipline.push(parseFloat(v.discipline));
  mistakes.push(parseFloat(v.mistakes));
});
$('.stats').highcharts({
    chart: {
        type: 'area',
        backgroundColor:'#000',
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
        type: 'datetime',
        dateTimeLabelFormats: { // don't display the dummy year
            day: '%e. %b',
            month: '%e. %b',
            year: '%b'
        },
        categories: categories,
        labels: {
            align:'left',
            rotation:60,
            formatter: function() {
                
                return Highcharts.dateFormat('%d/%m', this.value);
            }
        }
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
            console.log(this);
            return '<strong>'+Highcharts.dateFormat('%d/%m/%Y', this.x)+'</strong><br/>'+
                    this.series.name+': '+ this.y +'';
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
            name: 'Goals and Assists',
            data: goals_and_assists
        },
        {
            name: 'Shooting',
            data: shooting
        },
        {
            name: 'Defending',
            data: defending
        },
        {
            name: 'Passing',
            data: passing
        },
        {
            name: 'Goalkeeping',
            data: goalkeeping
        },
        {
            name: 'Discipline',
            data: discipline
        },
        {
            name: 'Mistakes and Errors',
            data: mistakes
        },

    ]
});
</script>
<script>
$( "#profiletabs" ).tabs({
    create:function(event,ui){
        var thisTab = $(ui.tab).find('a').html();
        $("#profiletabs h3").html(thisTab);
        console.log('create');
    },
    activate:function(event,ui){
        var thisTab = $(ui.newTab).find('a').html();
        $("#profiletabs h3").html(thisTab);
        console.log('activate');
    }
});


</script>

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
        <p><a href="#/sale/<%=player_id%>/1" class="button">Jual</a>
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