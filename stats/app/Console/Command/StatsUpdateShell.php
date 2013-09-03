<?php
class StatsUpdateShell extends AppShell{
	var $uses = array('Matchinfo','PlayerStats');

	public function main() {
       $this->dummy();
       $this->process_queue();
  }
  private function process_queue(){
    $queue = $this->getNewQueue();
   
    if(sizeof($queue)>0){
      $this->addToQueue($queue[0]['a']);
      $this->generateStats($queue[0]['a']);
      $this->flagDone($queue[0]['a']['game_id']);
      $this->process_queue();
    }
  }
  private function getNewQueue(){
    $sql = "SELECT game_id,home_team,away_team 
            FROM ".Configure::read('optadb').".matchinfo a 
            WHERE period = 'FullTime' 
            AND  NOT EXISTS
            (SELECT 1 FROM statsjob_queue b 
            WHERE b.game_id = a.game_id LIMIT 1) LIMIT 1";
    
   
    $rs = $this->Matchinfo->query($sql,false);

    
    return $rs;
  }
  private function addToQueue($queue){
    $sql = "INSERT IGNORE INTO ".Configure::read('optadb').".statsjob_queue
            (game_id,update_time,n_status)
            VALUES
            ('{$queue['game_id']}',NOW(),0)";
    
    $this->Matchinfo->query($sql);
  }
  private function flagDone($game_id){
    $sql = "UPDATE ".Configure::read('optadb').".statsjob_queue
            SET n_status=1
            WHERE game_id = '{$game_id}'";
    
    $this->Matchinfo->query($sql);
  }

  private function generateStats($game){
    $this->out('processing stats for game #'.$game['game_id']);
    $home_id = $game['home_team'];
    $away_id = $game['away_team'];
    
    //home stats
    $this->calculateTeamStats($game['game_id'],$home_id,$away_id);

    //away stats
    $this->calculateTeamStats($game['game_id'],$away_id,$home_id);
  }

  private function calculateTeamStats($game_id,$teamA,$teamB){
    $this->out('generating stats for team#'.$teamA);
    //get players
    $players = $this->getPlayers($game_id,$teamA);
    foreach($players as $player){
      $this->processPlayer($game_id,$player['player_stats']['player_id'],$teamA,$teamB);
    }

  }
  private function processPlayer($game_id,$player_id,$teamA,$teamB){
     $this->out('processing player #'.$player_id);

     $stats = $this->Matchinfo->query("SELECT stats_name,stats_value 
                                      FROM player_stats 
                                      WHERE game_id='{$game_id}' AND 
                                      team_id='{$teamA}' AND 
                                      player_id = '{$player_id}' 
                                      LIMIT 1000;",
                                      false);
     $player_stats = array();
     while(sizeof($stats)>0){
        $st = array_shift($stats);
        $player_stats[$st['player_stats']['stats_name']] = $st['player_stats']['stats_value'];
     }
     $player['most_influence'] = $this->most_influence($game_id,$teamA,$player_id,$player_stats);
     $player['def_influence'] = $this->def_influence($game_id,$teamA,$player_id,$player_stats);
     $player['mid_influence'] = $this->mid_influence($game_id,$teamA,$player_id,$player_stats);
     $player['fw_influence'] = $this->fw_influence($game_id,$teamA,$player_id,$player_stats);
     $player['def_score'] = $this->def_score($game_id,$teamA,$player_id,$player_stats);
     $player['atk_score'] = $this->atk_score($game_id,$teamA,$player_id,$player_stats);
     $player['creativity'] = $this->creativity($game_id,$teamA,$player_id,$player_stats);
     $player['most_accurate_pass'] = $this->most_accurate_pass($game_id,$teamA,$player_id,$player_stats);
     $player['least_accurate_pass'] = $this->least_accurate_pass($game_id,$teamA,$player_id,$player_stats);
     $player['shoot_accuracy'] = $this->shoot_accuracy($game_id,$teamA,$player_id,$player_stats);
     $player['chance_created'] = $this->chance_created($game_id,$teamA,$player_id,$player_stats);
     $player['dangerous_pass'] = $this->dangerous_pass($game_id,$teamA,$player_id,$player_stats);
     $player['assist'] = $this->assist($game_id,$teamA,$player_id,$player_stats);
     $player['best_cross_percentage'] = $this->best_cross_percentage($game_id,$teamA,$player_id,$player_stats);
     $player['worst_cross_percentage'] = $this->worst_cross_percentage($game_id,$teamA,$player_id,$player_stats);
     $player['ball_wins'] = $this->ball_wins($game_id,$teamA,$player_id,$player_stats);
     $player['def_fails'] = $this->def_fails($game_id,$teamA,$player_id,$player_stats);
     $player['liable'] = $this->liable($game_id,$teamA,$player_id,$player_stats);
     $player['gk_score'] = $this->gk_score($game_id,$teamA,$player_id,$player_stats,$teamB);
     $player['shot_stopping_percentage'] = $this->shot_stopping_percentage($game_id,$teamA,$player_id,$player_stats,$teamB);
     $player['best_at_crosses'] = $this->best_at_crosses($game_id,$teamA,$player_id,$player_stats);
     $player['one_v_one'] = $this->one_v_one($game_id,$teamA,$player_id,$player_stats);
     $player['deadkick'] = $this->deadkick($game_id,$teamA,$player_id,$player_stats);

     
     $this->PlayerStats->query(
      "INSERT INTO master_player_summary
       (game_id,team_id,player_id,
        most_influence,
        def_influence,
        mid_influence,
        fw_influence,
        def_score,
        atk_score,
        creativity,
        most_accurate_pass,
        least_accurate_pass,
        shoot_accuracy,
        chance_created,
        dangerous_pass,
        assist,
        best_cross_percentage,
        worst_cross_percentage,
        ball_wins,
        def_fails,
        liable,
        gk_score,
        shot_stopping_percentage,
        best_at_crosses,
        one_v_one,
        deadkick)
       VALUES
       ('{$game_id}','{$teamA}','{$player_id}',
        '{$player['most_influence']}',
        '{$player['def_influence']}',
        '{$player['mid_influence']}',
        '{$player['fw_influence']}',
        '{$player['def_score']}',
        '{$player['atk_score']}',
        '{$player['creativity']}',
        '{$player['most_accurate_pass']}',
        '{$player['least_accurate_pass']}',
        '{$player['shoot_accuracy']}',
        '{$player['chance_created']}',
        '{$player['dangerous_pass']}',
        '{$player['assist']}',
        '{$player['best_cross_percentage']}',
        '{$player['worst_cross_percentage']}',
        '{$player['ball_wins']}',
        '{$player['def_fails']}',
        '{$player['liable']}',
        '{$player['gk_score']}',
        '{$player['shot_stopping_percentage']}',
        '{$player['best_at_crosses']}',
        '{$player['one_v_one']}',
        '{$player['deadkick']}')
        ON DUPLICATE KEY UPDATE
        most_influence = VALUES(most_influence),
        def_influence = VALUES(def_influence),
        mid_influence = VALUES(mid_influence),
        fw_influence = VALUES(fw_influence),
        def_score = VALUES(def_score),
        atk_score = VALUES(atk_score),
        creativity = VALUES(creativity),
        most_accurate_pass = VALUES(most_accurate_pass),
        least_accurate_pass = VALUES(least_accurate_pass),
        shoot_accuracy = VALUES(shoot_accuracy),
        chance_created = VALUES(chance_created),
        dangerous_pass = VALUES(dangerous_pass),
        assist = VALUES(assist),
        best_cross_percentage = VALUES(best_cross_percentage),
        worst_cross_percentage = VALUES(worst_cross_percentage),
        ball_wins = VALUES(ball_wins),
        def_fails = VALUES(def_fails),
        liable = VALUES(liable),
        gk_score = VALUES(gk_score),
        shot_stopping_percentage = VALUES(shot_stopping_percentage),
        best_at_crosses = VALUES(best_at_crosses),
        one_v_one = VALUES(one_v_one),
        deadkick = VALUES(deadkick)
        ;"
     );  
  }
  private function getPlayers($game_id,$team_id){
    $sql = "SELECT player_id FROM player_stats 
            WHERE game_id='{$game_id}' AND team_id='{$team_id}' 
            GROUP BY player_id LIMIT 100;";
    return $this->Matchinfo->query($sql,false); 
  }
  private function most_influence($game_id,$team_id,$player_id,$stats){
    return $this->getTotalValuesFromAttributes("accurate_fwd_zone_pass,ontarget_scoring_att,effective_clearance,won_tackle,goal_assist, goals, aerial_won, interception_won, big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,outfielder_block,penalty_won,fouled_final_third,last_man_contest,last_man_tackle,offside_provoked,ontarget_scoring_att,accurate_cross,accurate_through_ball,ball_recovery,clearance_off_line,saves,gk_smother,good_high_claim,good_one_on_one,interceptions_in_box,penalty_won,second_goal_assist,total_att_assist,won_contest,accurate_keeper_sweeper,total_attacking_pass",
                                                $stats);
  }
  function def_influence($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass, ontarget_scoring_att, effective_clearance,won_tackle,goal_assist, goals, aerial_won, interception_won, big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,outfielder_block,penalty_won,fouled_final_third,last_man_contest,last_man_tackle,offside_provoked,ontarget_scoring_att,acurate_cross,accurate_through_ball,ball_recovery,clearance_off_line,saves,interceptions_in_box,penalty_won,second_goal_assist,total_att_assist,won_contest,total_attacking_pass,accurate_pull_back";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function mid_influence($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass, ontarget_scoring_att, effective_clearance,won_tackle,goal_assist, goals, aerial_won, interception_won, big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,outfielder_block,penalty_won,fouled_final_third,last_man_contest,last_man_tackle,offside_provoked,ontarget_scoring_att,accurate_cross,accurate_through_ball,ball_recovery,clearance_off_line,saves,interceptions_in_box,penalty_won,second_goal_assist,total_att_assist,won_contest,total_attacking_pass,accurate_pull_back";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function fw_influence($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass, ontarget_scoring_att, effective_clearance,won_tackle,goal_assist, goals, aerial_won, interception_won, big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,outfielder_block,penalty_won,fouled_final_third,last_man_contest,last_man_tackle,offside_provoked,ontarget_scoring_att,accurate_cross,accurate_through_ball,ball_recovery,clearance_off_line,saves,interceptions_in_box,penalty_won,second_goal_assist,total_att_assist,won_contest,accurate_layoffs,accurate_flick_on,total_attacking_pass,accurate_pull_back";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function def_score($game_id,$team_id,$player_id,$stats){
    $str = "interception_won,won_tackle,outfielder_block,effective_clearance,last_man_tackle,interceptions_in_box,offside_provoked,aerial_won,accurate_launches,ball_recovery,clearance_off_line";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function atk_score($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass,goal_assist,goals,ontarget_scoring_att,big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,accurate_through_ball,aerial_won,second_goal_assist,total_att_assist,total_attacking_pass,won_contest,penalty_won,fouled_final_third,last_man_contest,accurate_pull_back";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function creativity($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass,big_chance_created,big_chance_scored,big_chance_missed,accurate_cross,accurate_through_ball,second_goal_assist,total_att_assist,goal_assist,total_attacking_pass,won_contest,accurate_pull_back";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function most_accurate_pass($game_id,$team_id,$player_id,$stats){
    $p1 = "accurate_fwd_zone_pass,accurate_launches,accurate_long_balls,accurate_through_ball,accurate_chipped_pass,accurate_pull_back";
    $p2 = "total_fwd_zone_pass,total_launches,total_long_balls,total_through_ball,total_chipped_pass,total_pull_back";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
    if($score2==0){
      return 0;
    }else{
      return ($score1/$score2);  
    }
  }
  function least_accurate_pass($game_id,$team_id,$player_id,$stats){
    $p1 = "total_fwd_zone_pass,total_launches,total_long_balls,total_through_ball,total_chipped_pass";
    $p2 = "accurate_fwd_zone_pass,accurate_launches,accurate_long_balls,accurate_through_ball,accurate_chipped_pass";
    $p3 = "total_fwd_zone_pass,total_launches,total_long_balls,total_through_ball,total_chipped_pass";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
    $score3 = $this->getTotalValuesFromAttributes($p3,$stats);
    if($score3==0){
      return 0;
    }else{
      return (($score1-$score2)/$score3);  
    }
  }
  function shoot_accuracy($game_id,$team_id,$player_id,$stats){
      //ontarget_scoring_att-total_scoring_att
      $p1 = "ontarget_scoring_att";
      $p2 = "total_scoring_att";
      $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
      $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
      return ($score1 - $score2);
  }
  function chance_created($game_id,$team_id,$player_id,$stats){
    $str = "big_chance_created,big_chance_scored,big_chance_missed,att_assist_openplay,att_assist_setplay,second_goal_assist";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function dangerous_pass($game_id,$team_id,$player_id,$stats){
    $str = "accurate_fwd_zone_pass,accurate_through_ball,long_pass_own_to_opp_success,total_attacking_pass,successful_final_third_passes,big_chance_created,big_chance_scored,big_chance_missed,att_assist_openplay,att_assist_setplay,second_goal_assist";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function assist($game_id,$team_id,$player_id,$stats){
    $str = "goal_assist_openplay";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function best_cross_percentage($game_id,$team_id,$player_id,$stats){
    //accurate_cross_nocorner/total_cross_nocorner
    $p1 = "accurate_cross_nocorner";
    $p2 = "total_cross_nocorner";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
    print "accurate_cross_no_corner/total_cross_nocorner = ".$score1."/".$score2.PHP_EOL;
    if($score2==0){
      return 0;
    }else{
      return ($score1/$score2);  
    }
  }
  function worst_cross_percentage($game_id,$team_id,$player_id,$stats){
    //(total_cross_nocorner-accurate_cross_nocorner)/total_cross_nocorner
    $p1 = "total_cross_nocorner";
    $p2 = "accurate_cross_nocorner";
    $p3 = "total_cross_nocorner";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
    $score3 = $this->getTotalValuesFromAttributes($p3,$stats);

    print "total_cross_nocorner - accurate_cross_nocorner/total_cross_nocorner = (".$score1."-".$score2.")/".$score3.PHP_EOL;
    if($score3==0){
      return 0;
    }else{
      return (($score1-$score2)/$score3);  
    }
  }
  function ball_wins($game_id,$team_id,$player_id,$stats){
    $str = "won_tackle,interception_won,ball_recovery,duel_won,last_man_tackle";
    return $this->getTotalValuesFromAttributes($str,$stats);
  }
  function def_fails($game_id,$team_id,$player_id,$stats){
    $str="duel_lost,challenge_lost";
    return $this->getTotalValuesFromAttributes($str,$stats); 
  }
  function liable($game_id,$team_id,$player_id,$stats){
    $str = "dangerous_play,red_card,second_yellow,yellow_card,penalty_conceded,fk_foul_lost,error_lead_to_goal,error_lead_to_shot,error_lead_to_shot,dispossessed,unsuccessful_touch";
    return $this->getTotalValuesFromAttributes($str,$stats); 
  }
  function gk_score($game_id,$team_id,$player_id,$stats,$teamB){
    //p1 / (p3 + p2)
    $p1 = "good_high_claim,good_one_on_one,accurate_keeper_sweeper,saves";
    $p2 = "total_one_on_one,total_high_claim";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);
    $score3 = $this->getOtherTeamStats($game_id,$teamB,"ontarget_scoring_att");
    $this->out('punches,good_high_claim,good_one_on_one,accurate_keeper_sweeper,saves,gk_smother');
    $this->out('score1 / (score2+score3) -> '.$score1.'/('.$score2.'+'.$score3.')');
    $sum = $score2 + $score3;
    if($sum>0){
      return $score1 / $sum;
    }else{
      return 0;
    }
  }
  function shot_stopping_percentage($game_id,$team_id,$player_id,$stats,$teamB){
    $p1 = "saves";
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getOtherTeamStats($game_id,$teamB,"ontarget_scoring_att");
    $this->out('score1 / (score2) -> '.$score1.'/('.$score2.')');
    if($score2>0){
      return $score1 / $score2;
    }else{
      return 0;
    }
  }
  function best_at_crosses($game_id,$team_id,$player_id,$stats){
    //(good_high_claim/total_high_claim)
    $p1 = "good_high_claim";
    $p2 = "total_high_claim";
   
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);


    if($score2==0){
      return 0;
    }else{
      return ($score1/$score2);  
    }
  }
  function one_v_one($game_id,$team_id,$player_id,$stats){
    $p1 = "good_one_on_one,accurate_keeper_sweeper";
    $p2 = "total_one_on_one,total_keeper_sweeper";
   
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);


    if($score2==0){
      return 0;
    }else{
      return ($score1/$score2);  
    }
  }
  function deadkick($game_id,$team_id,$player_id,$stats){
    //(att_freekick_goal)/(att_freekick_total)
    $p1 = "att_freekick_goal";
    $p2 = "att_freekick_total";
   
    $score1 = $this->getTotalValuesFromAttributes($p1,$stats);
    $score2 = $this->getTotalValuesFromAttributes($p2,$stats);


    if($score2==0){
      return 0;
    }else{
      return ($score1/$score2);  
    }
  }
  function getTotalValuesFromAttributes($str,$stats){
    $arr = explode(",",$str);
    $score = 0;
    foreach($arr as $a){
      if(isset($stats[strtolower(trim($a))])){
        $score += $stats[strtolower(trim($a))];
      }
    }
    $arr = null;
    $str = null;
    unset($arr);
    unset($str);
    return $score;
  }
  function getOtherTeamStats($game_id,$team_id,$statsName){
    $sql = "SELECT stats_name,stats_value
            FROM 
            optadb.team_stats 
            WHERE 
            game_id = '{$game_id}' 
            AND team_id='{$team_id}' 
            AND stats_name = '{$statsName}' 
            LIMIT 1;";
    $rs = $this->Matchinfo->query($sql,false);
    if(sizeof($rs)>0){
      return $rs[0]['team_stats']['stats_value'];
    }else{
      return 0;
    }
  }
  private function dummy(){
    $sql = 'TRUNCATE TABLE statsjob_queue';
    $this->Matchinfo->query($sql);
  }
}