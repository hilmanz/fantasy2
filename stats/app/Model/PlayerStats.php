<?php
App::uses('AppModel', 'Model');

class PlayerStats extends Stats {
	public $useTable = false;
	public $useDbConfig = 'opta';
	/**
	* get best players across the league
	*/
	public function last_week_report(){
		$last_match = $this->getLastWeekMatches(Configure::read('competition_id'),
												Configure::read('season_id'));

		$rs = array('best_players'=>$this->best_players($last_match),
					'worst_players'=>$this->worst_players($last_match),
					'dangerous_passers'=>$this->lastWeekMostDangerousPassers($last_match),
					'best_crossers'=>$this->lastWeekBestCrossers($last_match),
					'sharpest_shooters'=>$this->SharpestShooters($last_match),
					'best_ball_winners'=>$this->BestBallWinners($last_match),
					'best_goalkeeper'=>$this->BestGoalKeeper($last_match),
					'best_shotstoppers'=>$this->BestShotStoppers($last_match),
					'weakest_defenders'=>$this->WeakestDefenders($last_match),
					'most_liable'=>$this->MostLiable($last_match),
					);
		
		return $rs;
	}
	public function cumulative_reports(){
		$game_ids = $this->getGameIds(Configure::read('competition_id'),
												Configure::read('season_id'));
		
		$rs = array('best_players'=>$this->best_players($game_ids),
					'worst_players'=>$this->worst_players($game_ids),
					'dangerous_passers'=>$this->lastWeekMostDangerousPassers($game_ids),
					'best_crossers'=>$this->lastWeekBestCrossers($game_ids),
					'sharpest_shooters'=>$this->SharpestShooters($game_ids),
					'best_ball_winners'=>$this->BestBallWinners($game_ids),
					'best_goalkeeper'=>$this->BestGoalKeeper($game_ids),
					'best_shotstoppers'=>$this->BestShotStoppers($game_ids),
					'weakest_defenders'=>$this->WeakestDefenders($game_ids),
					'most_liable'=>$this->MostLiable($game_ids),
					);
		
		return $rs;
	}
	public function best_players($last_match){
		

		$rs = array('overall'=>$this->lastWeekBestPlayerInfluence($last_match),
					'defender'=>$this->lastWeekBestDefender($last_match),
					'midfielder'=>$this->lastWeekBestMidfielder($last_match),
					'forward'=>$this->lastWeekBestForward($last_match)
					);

		return $rs;
		
	}
	public function worst_players($last_match){
		$last_match = $this->getLastWeekMatches(Configure::read('competition_id'),
												Configure::read('season_id'));

		$rs = array('overall'=>$this->lastWeekWorstPlayerInfluence($last_match),
					'defender'=>$this->lastWeekWorstDefender($last_match),
					'midfielder'=>$this->lastWeekWorstMidfielder($last_match),
					'forward'=>$this->lastWeekWorstForward($last_match)
					);

		return $rs;
		
	}
	private function lastWeekBestPlayerInfluence($game_ids){
		$sql = "SELECT player_id,SUM(most_influence) AS total,b.name,b.position,
				b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
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
	private function lastWeekBestDefender($game_ids){
		$sql = "SELECT player_id,SUM(def_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Defender'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND def_influence > 0
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
	private function lastWeekBestMidfielder($game_ids){
		$sql = "SELECT player_id,SUM(mid_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Midfielder'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND mid_influence > 0
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
	private function lastWeekBestForward($game_ids){
		$sql = "SELECT player_id,SUM(fw_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Forward'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND fw_influence > 0
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
	private function lastWeekWorstDefender($game_ids){
		$sql = "SELECT player_id,SUM(def_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Defender'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND def_influence > 0
				GROUP BY player_id 
				ORDER BY total ASC LIMIT 5;";
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
	private function lastWeekWorstMidfielder($game_ids){
		$sql = "SELECT player_id,SUM(mid_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Midfielder'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND mid_influence > 0
				GROUP BY player_id 
				ORDER BY total ASC LIMIT 5;";
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
	private function lastWeekWorstForward($game_ids){
		$sql = "SELECT player_id,SUM(fw_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Forward'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND fw_influence > 0
				GROUP BY player_id 
				ORDER BY total ASC LIMIT 5;";
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
	private function lastWeekWorstPlayerInfluence($game_ids){
		$sql = "SELECT player_id,SUM(most_influence) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND most_influence > 0
				GROUP BY player_id 
				ORDER BY total ASC LIMIT 5;";
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
	public function lastWeekMostDangerousPassers($game_ids){
		$sql = "SELECT player_id,SUM(chance_created) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND chance_created > 0
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
	public function lastWeekBestCrossers($game_ids){
		$sql = "SELECT player_id,((SUM(best_cross_percentage)/COUNT(a.id))*100) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND best_cross_percentage > 0
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
	public function SharpestShooters($game_ids){
		$sql = "SELECT player_id,((SUM(shoot_accuracy)/COUNT(a.id))*100) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND shoot_accuracy > 0
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
	public function BestBallWinners($game_ids){
		$sql = "SELECT player_id,SUM(ball_wins) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND ball_wins > 0
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
	public function BestGoalKeeper($game_ids){
		$sql = "SELECT player_id,SUM(gk_score) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position = 'Goalkeeper'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND gk_score > 0
				GROUP BY player_id 
				ORDER BY total DESC LIMIT 5;
				";
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
	public function BestShotStoppers($game_ids){
		$sql = "SELECT player_id,(SUM(shot_stopping_percentage)/COUNT(a.id)) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND shot_stopping_percentage > 0
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
	public function WeakestDefenders($game_ids){
		$sql = "SELECT player_id,SUM(def_fails) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid AND b.position='Defender'
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND def_fails > 0
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
	public function MostLiable($game_ids){
		$sql = "SELECT player_id,SUM(liable) AS total,b.name,b.position,b.jersey_num,b.team_id,
				c.name AS team_name
				FROM master_player_summary a
				INNER JOIN master_player b
				ON a.player_id = b.uid
				INNER JOIN master_team c
				ON b.team_id = c.uid
				WHERE game_id IN (".$this->arrayToSql($game_ids).")
				AND liable > 0
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
}