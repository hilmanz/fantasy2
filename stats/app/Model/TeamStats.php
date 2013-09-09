<?php
App::uses('AppModel', 'Model');

class TeamStats extends Stats {
	public $useTable = false;
	public $useDbConfig = 'opta';
	/**
	* get best players across the league
	*/
	public function getReports($team_id){
		$game_ids = $this->getGameIds(Configure::read('competition_id'),
												Configure::read('season_id'));

		$stats = $this->getStats($team_id,$game_ids);
		$teamBStats = $this->getTeamBStats($team_id,$game_ids);

		$rs = array('team'=>$this->getTeam($team_id),
					'most_influential_player'=>$this->getMostInfluencePlayer($team_id,$game_ids),
					'top_assist'=>$this->top_assist($team_id,$game_ids),
					'top_scorer'=>$this->top_scorer($team_id,$game_ids),
					'dangerous_passer'=>$this->dangerous_passer($team_id,$game_ids),
					'greatest_liability'=>$this->greatest_liability($team_id,$game_ids),
					'attacking_play'=>$this->attacking_play($team_id,$game_ids,$stats),
					'attacking_style'=>$this->attacking_style($team_id,$game_ids,$stats,$teamBStats),
					'dribbling'=>$this->dribbling($team_id,$game_ids,$stats,$teamBStats),
					'passing_style'=>$this->passing_style($team_id,$game_ids,$stats,$teamBStats)
					);

		pr($rs);
		return $rs;
	}
	private function getTeam($team_id){
		$rs = $this->query("SELECT * FROM master_team WHERE uid='{$team_id}' LIMIT 1");
		return $rs[0]['master_team'];
	}
	private function getMostInfluencePlayer($team_id,$game_ids){
		$sql = "SELECT player_id,SUM(most_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND most_influence > 0
				AND a.team_id='{$team_id}'
				GROUP BY player_id 
				ORDER BY total DESC LIMIT 5;";
		$rs = $this->query($sql);
		$players  = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$player = $a['b'];
			$player['team_name'] = $a['c']['team_name'];
			$player['total'] = $a[0]['total'];
			$player['player_id'] = $a['a']['player_id'];
			$players[] = $player;
		}
		return $players;
	}
	private function top_assist($team_id,$game_ids){
		$sql = "SELECT a.player_id,SUM(stats_value) AS total,b.name,b.position,b.jersey_num 
				FROM player_stats a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				WHERE a.team_id='{$team_id}' AND stats_name='goal_assist' 
				AND game_id IN (".$this->arrayToSql($game_ids).")
				GROUP BY player_id LIMIT 5";
		$rs = $this->query($sql);
		
		$players  = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$player = $a['b'];
			
			$player['total'] = $a[0]['total'];
			$player['player_id'] = $a['a']['player_id'];
			$players[] = $player;
		}
		return $players;
	}
	private function top_scorer($team_id,$game_ids){
		$sql = "SELECT a.player_id,SUM(stats_value) AS total,b.name,b.position,b.jersey_num 
				FROM player_stats a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				WHERE a.team_id='{$team_id}' AND stats_name='goals' 
				AND game_id IN (".$this->arrayToSql($game_ids).")
				GROUP BY player_id LIMIT 5";
		$rs = $this->query($sql);
		
		$players  = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$player = $a['b'];
			
			$player['total'] = $a[0]['total'];
			$player['player_id'] = $a['a']['player_id'];
			$players[] = $player;
		}
		return $players;
	}
	private function dangerous_passer($team_id,$game_ids){
		$sql = "SELECT player_id,SUM(dangerous_pass) AS total,b.name,
				b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND dangerous_pass > 0
				AND a.team_id='t1'
				GROUP BY player_id 
				ORDER BY total DESC LIMIT 5;";
		$rs = $this->query($sql);
		
		$players  = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$player = $a['b'];
			$player['total'] = $a[0]['total'];
			$player['player_id'] = $a['a']['player_id'];
			$players[] = $player;
		}
		return $players;
	}
	private function greatest_liability($team_id,$game_ids){
		$sql = "SELECT player_id,SUM(liable) AS total,b.name,
				b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND liable > 0
				AND a.team_id='t1'
				GROUP BY player_id 
				ORDER BY total DESC LIMIT 5;";
		$rs = $this->query($sql);
		
		$players  = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$player = $a['b'];
			
			$player['total'] = $a[0]['total'];
			$player['player_id'] = $a['a']['player_id'];
			$players[] = $player;
		}
		return $players;
	}
	private function attacking_style($team_id,$game_ids,$stats,$teamB){
		$deep_cross = intval(@$teamB['crosses_18yardplus']);
		$deep_cross_avg = intval(@$teamB['crosses_18yardplus']) / (intval(@$teamB['crosses_18yardplus']) + intval(@$teamB['crosses_18yard']) +intval(@$stats['total_pull_back']) + intval(@$stats['total_through_ball']));
		$cross_from_18yd = intval(@$teamB['crosses_18yard']);
		$cross_from_18yd_avg = intval(@$teamB['crosses_18yard']) / (intval(@$teamB['crosses_18yardplus']) + intval(@$teamB['crosses_18yard']) +intval(@$stats['total_pull_back']) + intval(@$stats['total_through_ball']));

		$cutbacks = intval(@$stats['total_pull_back']);
		$cutbacks_avg = intval(@$stats['total_pull_back']) / (intval(@$teamB['crosses_18yardplus']) + intval(@$teamB['crosses_18yard']) +intval(@$stats['total_pull_back']) + intval(@$stats['total_through_ball']));
		
		$through_ball = intval(@$stats['total_through_ball']);
		$through_ball_avg = intval(@$stats['total_through_ball']) / (intval(@$teamB['crosses_18yardplus']) + intval(@$teamB['crosses_18yard']) +intval(@$stats['total_pull_back']) + intval(@$stats['total_through_ball']));
		

		$through_ball = intval(@$stats['total_through_ball']);
		$through_ball_avg = intval(@$stats['total_through_ball']) / (intval(@$teamB['crosses_18yardplus']) + intval(@$teamB['crosses_18yard']) +intval(@$stats['total_pull_back']) + intval(@$stats['total_through_ball']));
		
		$accurate_cutbacks = intval(@$stats['accurate_pull_back']);
		$accurate_cutbacks_avg = floatval(@(intval(@$stats['accurate_pull_back']) / intval(@$stats['total_pull_back'])));

		$accurate_through_ball = intval(@$stats['accurate_through_ball']);
		$accurate_through_ball_avg = floatval(@(intval(@$stats['accurate_through_ball']) / intval(@$stats['total_through_ball'])));

		$accurate_through_ball = intval(@$stats['accurate_through_ball']);
		$accurate_through_ball_avg = floatval(@(intval(@$stats['accurate_through_ball']) / intval(@$stats['total_through_ball'])));

		$chances_from_crosses = intval(@$stats['att_hd_total']);

		$shots_from_ibox = intval(@$att_ibox_blocked) + intval(@$att_ibox_goal) + intval(@$att_ibox_miss) + intval(@$att_ibox_target);
		$shots_from_ibox_avg = $shots_from_ibox / ($chances_from_crosses + $shots_from_ibox + $shots_from_obox);
		$shots_from_obox = intval(@$att_obox_blocked) + intval(@$att_obox_goal) + intval(@$att_obox_miss) + intval(@$att_obox_target);
		$shots_from_obox_avg = $shots_from_obox / ($chances_from_crosses + $shots_from_ibox + $shots_from_obox);
		
		$chances_from_crosses_avg = $chances_from_crosses / ($chances_from_crosses + $shots_from_ibox + $shots_from_obox);

		$goals_from_shot_ibox = intval(@$stats['att_ibox_goal']);
		$goals_from_shot_obox = intval(@$stats['att_obox_goal']);
		$goals_from_crosses = intval(@$stats['att_obox_goal']);
		$goals_from_shot_ibox_avg = $goals_from_shot_ibox / ($goals_from_shot_ibox+$goals_from_shot_obox+$goals_from_crosses);
		$goals_from_shot_obox_avg = $goals_from_shot_obox / ($goals_from_shot_ibox+$goals_from_shot_obox+$goals_from_crosses);
		$goals_from_crosses_avg = $goals_from_crosses / ($goals_from_shot_ibox+$goals_from_shot_obox+$goals_from_crosses);
		return array('deep_cross'=>array('total'=>$deep_cross,
										 'average'=>$deep_cross_avg),
					  'cross_from_18yd'=>array('total'=>$cross_from_18yd,
					  							'average'=>$cross_from_18yd_avg),

					  'cutbacks'=>array('total'=>$cutbacks,
					  							'average'=>$cutbacks_avg),

					  'through_ball'=>array('total'=>$through_ball,
					  							'average'=>$through_ball_avg),

					  'accurate_cutbacks'=>array('total'=>$accurate_cutbacks,
					  							'average'=>$accurate_cutbacks_avg),

					  'accurate_through_ball'=>array('total'=>$accurate_through_ball,
					  							'average'=>$accurate_through_ball_avg),

					  'chances_from_crosses'=>array("total"=>$chances_from_crosses,
					  								"average"=>$chances_from_crosses_avg),

					  'shots_from_ibox'=>array("total"=>$shots_from_ibox,
					  								"average"=>$shots_from_ibox_avg),
					  'shots_from_obox'=>array("total"=>$shots_from_obox,
					  								"average"=>$shots_from_obox_avg),

					   'goals_from_shot_ibox'=>array("total"=>$goals_from_shot_ibox,
					  								"average"=>$goals_from_shot_ibox_avg),

					   'goals_from_shot_obox'=>array("total"=>$goals_from_shot_obox,
					  								"average"=>$goals_from_shot_obox_avg),

					   'goals_from_crosses'=>array("total"=>$goals_from_crosses,
					  								"average"=>$goals_from_crosses_avg),

					);
	}

	private function dribbling($team_id,$game_ids,$stats,$teamB){
		return array('beating_an_opponent'=>intval(@$stats['won_contest']),
					  'beating_last_defender'=>intval(@$stats['last_man_contest']),
					  'fouled_in_attacking_3rd'=>intval(@$stats['fouled_final_third']),
					);
	}
	private function passing_style($team_id,$game_ids,$stats,$teamB){
		$long_ball = intval(@$stats['total_longballs']);
		$short_passes = intval(@$stats['total_pass']) - intval(@$stats['total_longballs']);
		$launches = intval(@$stats['total_launches']);
		$through_balls = intval(@$stats['total_through_ball']);
		$chipped_passes = intval(@$stats['total_chipped_pass']);
		$forward_passes = intval(@$stats['total_fwd_zone_pass']);
		$total_pass = $long_ball + $short_passes + $launches + $through_balls 
						+ $chipped_passes + $forward_passes;
		$leftside_pass = intval(@$stats['leftside_pass']);
		$rightside_pass = intval(@$stats['rightside_pass']);

		//average
		$long_ball_avg = $long_ball / $total_pass;
		$short_passes_avg = $short_passes / $total_pass;
		$launches_avg = $launches / $total_pass;
		$through_balls_avg = $through_balls / $total_pass;
		$chipped_passes_avg = $chipped_passes / $total_pass;
		$forward_passes_avg = $forward_passes / $total_pass;
		
		$leftside_pass_avg = $leftside_pass / ($leftside_pass+$rightside_pass);
		$rightside_pass_avg = $rightside_pass / ($leftside_pass+$rightside_pass);

		//accuracy
		$long_ball_acc = intval(@$stats['accurate_long_balls']) / $long_ball;
		$short_passes_acc = 1;
		$launches_acc = intval(@$stats['accurate_launches']) / $launches;
		$through_balls_acc =  intval(@$stats['accurate_through_ball']) / $through_balls;
		$chipped_passes_acc =  intval(@$stats['accurate_chipped_pass']) / $chipped_passes;
		$forward_passes_acc =  intval(@$stats['accurate_fwd_zone_pass']) / $forward_passes;

		return array('long_ball'=>array('total'=>$long_ball,
										 'average'=>$long_ball_avg,
										 'accuracy'=>$long_ball_acc),

					'short_passes'=>array('total'=>$short_passes,
										 'average'=>$short_passes_avg,
										 'accuracy'=>$short_passes_acc),


					'launches'=>array('total'=>$launches,
										 'average'=>$launches_avg,
										 'accuracy'=>$launches_acc),

					'through_balls'=>array('total'=>$through_balls,
										 'average'=>$through_balls_avg,
										 'accuracy'=>$through_balls_acc),

					'chipped_passes'=>array('total'=>$chipped_passes,
										 'average'=>$chipped_passes_avg,
										 'accuracy'=>$chipped_passes_acc),

					'forward_passes'=>array('total'=>$forward_passes,
										 'average'=>$forward_passes_avg,
										 'accuracy'=>$chipped_passes_acc),


					'leftside_pass'=>array('total'=>$leftside_pass,
										 'average'=>$leftside_pass_avg,
										 'accuracy'=>0),

					'rightside_pass'=>array('total'=>$rightside_pass,
										 'average'=>$rightside_pass_avg,
										 'accuracy'=>0),


				);



	}
	/*
	* Frequency | Efficiency | Chances | Goals | Conversion Rate | Average/Game
	* Open Play, Set Pieces, Counter Attack
	*/
	private function attacking_play($team_id,$game_ids,$stats){
		$rs = $this->query("SELECT COUNT(*) AS total FROM (SELECT game_id 
							FROM team_stats 
							WHERE game_id IN (".$this->arrayToSql($game_ids).")
							AND team_id='{$team_id}' GROUP BY game_id) a;");
		$total_games = $rs[0][0]['total'];
		//openplay
		$openplay['frequency'] = $this->openplay_frequency($stats);
		$openplay['efficiency'] = $this->openplay_efficiency($stats);
		$openplay['chances'] = $this->openplay_chances($stats);
		$openplay['goals'] = $this->openplay_goals($stats);
		$openplay['conversion_rate'] = $this->openplay_conversion($stats);
		$openplay['average'] = $this->openplay_average_per_game($stats,$total_games);

		$setpieces['frequency'] = $this->setpieces_frequency($stats);
		$setpieces['efficiency'] = $this->setpieces_efficiency($stats);
		$setpieces['chances'] = $this->setpieces_chances($stats);
		$setpieces['goals'] = $this->setpieces_goals($stats);
		$setpieces['conversion_rate'] = $this->setpieces_conversion($stats);
		$setpieces['average'] = $this->setpieces_average_per_game($stats,$total_games);

		$counter['frequency'] = $this->counter_frequency($stats);
		$counter['efficiency'] = $this->counter_efficiency($stats);
		$counter['chances'] = $this->counter_chances($stats);
		$counter['goals'] = intval(@$stats['goal_fastbreak']);
		$counter['conversion_rate'] = @floatval($this->counter_conversion($stats));
		$counter['average'] = $this->counter_average_per_game($stats,$total_games);

		return array('openplay'=>$openplay,
					 'setpieces'=>$setpieces,
					 'counter_attack'=>$counter);
	}
	private function counter_average_per_game($stats,$total_games){
		$chances = intval(@$stats['total_fastbreak']);
		return $chances/$total_games;

	}
	private function counter_conversion($stats){
		return intval(@$stats['goal_fastbreak'])/intval(@$stats['att_fastbreak']);
	}
	private function counter_chances($stats){
		return intval(@$stats['shot_fastbreak']);
	}
	private function counter_efficiency($stats){
		return (intval(@$stats['goal_fastbreak']) + intval(@$stats['shot_fastbreak']) + intval(@$stats['att_fastbreak'])) / (intval(@$stats['goals']) + intval(@$stats['total_scoring_att']));

	}
	private function counter_frequency($stats){
		return (intval(@$stats['goal_fastbreak']) + intval(@$stats['shot_fastbreak']) +intval(@$stats['att_fastbreak']))/ ((intval(@$stats['goals_openplay']) + intval(@$stats['att_openplay'])) + ((intval(@$stats['goals']) - intval(@$stats['goals_openplay'])) +(intval(@$stats['total_scoring_att']) - intval(@$stats['att_openplay'])) )+(intval(@$stats['goal_fastbreak']) + intval(@$stats['shot_fastbreak']) +intval(@$stats['att_fastbreak'])));

	}
	private function setpieces_average_per_game($stats,$total_games){
		$chances = $this->setpieces_chances($stats);
		return $chances/$total_games;

	}
	private function setpieces_conversion($stats){
		return ($stats['goals'] - $stats['goals_openplay']) / ($stats['total_scoring_att'] - $stats['att_openplay']);
	}
	private function setpieces_goals($stats){
		return ($stats['goals'] - $stats['goals_openplay']);

	}
	private function setpieces_frequency($stats){
		//((goals - goals_openplay) +(total_scoring_att - att_openplay) / ((goals_openplay + att_openplay) + ((goals - goals_openplay) +(total_scoring_att - att_openplay) +(goal_fastbreak + shot_fastbreak +att_fastbreak))
		$a = $this->getStatsValue('goals',$stats) - $this->getStatsValue('goals_openplay',$stats);
		$b = $this->getStatsValue('total_scoring_att',$stats) - $this->getStatsValue('att_openplay',$stats);
		$c = $this->getTotalValuesFromAttributes('goals_openplay,att_openplay',$stats);
		$d = $this->getStatsValue('goals',$stats) - $this->getStatsValue('goals_openplay',$stats);
		$e = $this->getStatsValue('total_scoring_att',$stats) - $this->getStatsValue('att_openplay',$stats);
		$f = $this->getTotalValuesFromAttributes('goal_fastbreak,shot_fastbreak,att_fastbreak',$stats);
		$n = ($c + $d + $e +$f);
		if($n>0){
			return ($a+$b) / $n;
		}
		return 0;
	}
	private function setpieces_efficiency($stats){
		//((goals - goals_openplay) +(total_scoring_att - att_openplay)) / (goals + total_scoring_att)
		return (($stats['goals'] - $stats['goals_openplay']) +($stats['total_scoring_att'] - $stats['att_openplay'])) / ($stats['goals'] + $stats['total_scoring_att']);
		
	}
	private function setpieces_chances($stats){
		return $stats['total_scoring_att'] - $stats['att_openplay'];
	}
	private function openplay_average_per_game($stats,$total_games){
		$goals = $this->getTotalValuesFromAttributes('goals',$stats);
		
		if($total_games>0){
			return $goals / $total_games;
		}
		return 0;
	}
	private function openplay_goals($stats){
		return $this->getTotalValuesFromAttributes('goals_openplay',$stats);
	}
	
	private function openplay_conversion($stats){
		$s1 = $this->getTotalValuesFromAttributes('goals',$stats);
		$s2 = $this->getTotalValuesFromAttributes('total_scoring_att',$stats);
		if($s2>0){
			return ($s1/$s2);
		}
		return 0;
	}
	private function openplay_frequency($stats){
		$s1 = $this->getTotalValuesFromAttributes('goals_openplay,att_openplay',$stats);
		$goals = $this->getTotalValuesFromAttributes('goals',$stats);
		$goals_openplay = $this->getTotalValuesFromAttributes('goals_openplay',$stats);
		$total_scoring_att = $this->getTotalValuesFromAttributes('total_scoring_att',$stats);
		$att_openplay = $this->getTotalValuesFromAttributes('att_openplay',$stats);
		$s2 = $this->getTotalValuesFromAttributes('goal_fastbreak,shot_fastbreak,att_fastbreak',$stats);
		$total = ($s1) / ($s1+ ($goals - $goals_openplay) + ($total_scoring_att - $att_openplay) + $s2);
		return $total;
	}
	private function openplay_efficiency($stats){
		$s1 = $this->getTotalValuesFromAttributes('goals_openplay,att_openplay',$stats);
		$s2 = $this->getTotalValuesFromAttributes('goals,total_scoring_att',$stats);
		
		if($s2>0){
			return ($s1/$s2);
		}
		return 0;
	}
	private function openplay_chances($stats){
		$s1 = $this->getTotalValuesFromAttributes('att_openplay',$stats);
		return $s1;
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
	private function getStatsValue($statsName,$stats){
		return $stats[$statsName];
	}
	private function getStats($team_id,$game_ids){
		$sql = "SELECT stats_name,SUM(stats_value) AS total 
				FROM team_stats 
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND team_id='{$team_id}'
				GROUP BY stats_name;";
		$rs = $this->query($sql);
		$stats = array();
		while(sizeof($rs)>0){
			$st = array_shift($rs);
			$stats[$st['team_stats']['stats_name']] = $st[0]['total'];
		}

		return $stats;
	}
	private function getTeamBStats($team_id,$game_ids){

		//1 get the related game_ids
		$sql = "SELECT game_id FROM matchinfo 
				WHERE (home_team = '{$team_id}' OR away_team='{$team_id}') 
				AND game_id IN (".$this->arrayToSql($game_ids).")
				";
		$rs = $this->query($sql);
		
		$teamB_game_ids = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$teamB_game_ids[] = $a['matchinfo']['game_id'];
		}
		//2 get team B lists
		$sql = "SELECT team_id FROM team_stats 
				WHERE game_id IN (".$this->arrayToSql($teamB_game_ids).") 
				AND team_id <> '{$team_id}' GROUP BY team_id;";
		$rs = $this->query($sql);
		
		$teamBIds = array();
		while(sizeof($rs)>0){
			$a = array_shift($rs);
			$teamBIds[] = $a['team_stats']['team_id'];
		}

		//get the accumulated stats
		$sql = "SELECT stats_name,SUM(stats_value) AS total 
				FROM team_stats 
				WHERE game_id IN (".$this->arrayToSql($teamB_game_ids).") 
				AND team_id IN (".$this->arrayToSql($teamBIds).")
				GROUP BY stats_name;";

		$rs = $this->query($sql);
		$stats = array();
		while(sizeof($rs)>0){
			$st = array_shift($rs);
			$stats[$st['team_stats']['stats_name']] = $st[0]['total'];
		}

		return $stats;
	}
}