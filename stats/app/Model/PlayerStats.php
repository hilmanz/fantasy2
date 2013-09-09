<?php
App::uses('AppModel', 'Model');

class PlayerStats extends Stats {
	public $useTable = false;
	public $useDbConfig = 'opta';
	/**
	* get best players across the league
	*/
	public function best_players(){
		$last_match = $this->getLastWeekMatches(Configure::read('competition_id'),
												Configure::read('season_id'));

		$rs = array('overall'=>$this->lastWeekBestPlayerInfluence($last_match),
					'defender'=>$this->lastWeekBestDefender($last_match),
					'midfielder'=>$this->lastWeekBestMidfielder($last_match),
					'forward'=>$this->lastWeekBestForward($last_match)
					);

		return $rs;
		
	}
	public function worst_players(){
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
}