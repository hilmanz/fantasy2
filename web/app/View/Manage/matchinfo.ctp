<?php

if($club['team_id'] == $match['home_id']){
    $home = $club['team_name'];
    $away = $match['away_name'];
}else{
    $away = $club['team_name'];
    $home = $match['home_name'];
}

function getPoin($position,$stats_name,$modifier){
   
    return intval(@$modifier[$stats_name][$position]);
}
function getTotalPoints($str,$stats){
    $arr = explode(",",$str);
    $total = 0;
    foreach($arr as $a){
        $total += intval(@$stats[$a]);
    }
    return $total;
}
?>
<div id="myClubPage">
    <?php echo $this->element('infobar'); ?>
    <div class="row">
        <a href="<?=$this->Html->url('/manage/club')?>" class="button">Kembali</a>
    </div>
    <div id="thecontent">
        <div class="row">
            <div><?=h($home)?> vs <?=h($away)?></div>
            <div><?=intval($match['home_score'])?>   -  <?=intval($match['away_score'])?></div>
            <div>Total Poin</div>
            <div><?=number_format($match['points'])?></div>
        </div>
        <table>
            <tr>
                <td colspan="2">Player</td>
                <td>Posisi</td>
                <td>Play</td>
                <td>Attacking &amp; Passing</td>
                <td>Defending</td>
                <td>Goalkeeping</td>
                <td>Mistakes and Errors</td>
                <td>Poin</td>
            </tr>
            <?php
                $overall_points = 0;

                foreach($players as $player_id=>$detail):
                    foreach($detail['stats'] as $n=>$v){
                        $detail['stats'][$n] = $detail['stats'][$n] * 
                                                getPoin($detail['position'],
                                                        $n,
                                                        $modifier);
                    }
                    
                    $games = getTotalPoints('game_started,total_sub_on',$detail['stats']);

                    
                    $attacking_and_passing = getTotalPoints('att_freekick_goal,att_ibox_goal,att_obox_goal,att_pen_goal,att_freekick_post,ontarget_scoring_att,att_obox_target,big_chance_created,big_chance_scored,goal_assist,total_att_assist,second_goal_assist,final_third_entries,fouled_final_third,pen_area_entries,won_contest,won_corners,penalty_won,last_man_contest,accurate_corners_intobox,accurate_cross_nocorner,accurate_freekick_cross,accurate_launches,long_pass_own_to_opp_success,successful_final_third_passes,accurate_flick_on',
                                            $detail['stats']);
                    $defending = getTotalPoints('aerial_won,ball_recovery,duel_won,effective_blocked_cross,effective_clearance,effective_head_clearance,interceptions_in_box,interception_won,poss_won_def_3rd,poss_won_mid_3rd,poss_won_att_3rd,won_tackle,offside_provoked,last_man_tackle,outfielder_block',$detail['stats']);

                    $goalkeeping = getTotalPoints('dive_catch,dive_save,stand_catch,stand_save,cross_not_claimed,good_high_claim,punches,good_one_on_one,accurate_keeper_sweeper,gk_smother,saves,goals_conceded',$detail['stats']);
                    $mistakes_and_errors = getTotalPoints('penalty_conceded,red_card,yellow_card,challenge_lost,dispossessed,fouls,overrun,total_offside,unsuccessful_touch,error_lead_to_shot,error_lead_to_goal',$detail['stats']);

                    $total_poin = $games + $attacking_and_passing + $defending +
                                  $goalkeeping + $mistakes_and_errors;

                    $overall_points += $total_poin;
                   
            ?>
            <tr>
                <td>
                    <a class="thumbPlayers" href="<?=$this->Html->url('/manage/player/'.$player_id)?>"> <img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description=<?=str_replace('t','',$detail['original_team_id'])?>&dimensions=103x155&id=<?=str_replace('p','',$player_id)?>"/></a>
                </td>
                <td><?=h($detail['name'])?></td>
                <td><?=h($detail['position'])?></td>
                <td><?=$games?></td>
                <td><?=$attacking_and_passing?></td>
                <td><?=$defending?></td>
                <td><?=$goalkeeping?></td>
                <td><?=$mistakes_and_errors?></td>
                <td><?=$total_poin?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>