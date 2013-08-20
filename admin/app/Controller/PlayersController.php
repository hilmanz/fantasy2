<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');


class PlayersController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Players';

	public function index(){
		$this->loadModel('User');
		$this->loadModel('Point');
		$this->paginate = array('limit'=>20);
		$rs = $this->paginate('User');
		foreach($rs as $n=>$r){
			$point = $this->Point->findByTeam_id($r['Team']['id']);
			if(isset($point['Point'])){
				$rs[$n]['Point'] = $point['Point'];
			}
		}
		$this->set('rs',$rs);
	}
	public function view($user_id){
		$this->loadModel('User');
		$this->loadModel('Point');
		$user = $this->User->findById($user_id);
		$point = $this->Point->findByTeam_id($user['Team']['id']);
		$team_data = $this->User->query("SELECT * FROM ffgame.game_users a
											INNER JOIN ffgame.game_teams b
											ON a.id = b.user_id
											INNER JOIN ffgame.master_team c
											ON b.team_id = c.uid
											WHERE fb_id='{$user['User']['fb_id']}';");

		$budget = $this->User->query("SELECT (SUM(budget + expense)) AS current_budget
										FROM (
										SELECT budget,0 AS expense
										FROM ffgame.game_team_purse 
										WHERE game_team_id={$team_data[0]['b']['id']}
										UNION ALL
										SELECT 0,SUM(amount) AS total_balance 
										FROM ffgame.game_team_expenditures 
										WHERE game_team_id={$team_data[0]['b']['id']})
										a;");

		$matches = $this->User->query("SELECT COUNT(game_id) AS total_matches FROM 
										(SELECT game_id 
											FROM ffgame_stats.game_match_player_points 
											WHERE game_team_id={$team_data[0]['b']['id']} 
											GROUP BY game_id) a;");
		
		$squad = $this->User->query("SELECT b.* FROM ffgame.game_team_players a
										INNER JOIN ffgame.master_player b
										ON a.player_id = b.uid
										WHERE a.game_team_id = {$team_data[0]['b']['id']} 
										ORDER BY position,last_name
										LIMIT 1000;");

		foreach($squad as $n=>$s){
			$squad[$n]['stats'] = $this->getTeamPlayerDetail($team_data[0]['b']['id'],
															 $s['b']['uid']);
		}
		
		$this->set('budget',$budget[0][0]['current_budget']);
		$this->set('total_matches',$matches[0][0]['total_matches']);
		$this->set('team_data',$team_data[0]);
		$this->set('user',$user);
		$this->set('point',@$point['Point']);
		$this->set('squad',$squad);
	}
	private function getTeamPlayerDetail($game_team_id,$player_id){
		$stats = $this->User->query("SELECT COUNT(DISTINCT game_id) AS total_plays,SUM(points) AS total_points,
							SUM(performance) AS total_performance 
							FROM ffgame_stats.game_match_player_points 
							WHERE game_team_id = {$game_team_id} AND player_id='{$player_id}';");

		$last_performance = $this->User->query("
								SELECT game_id,SUM(points) AS total_points,
								SUM(performance) AS total_performance 
								FROM ffgame_stats.game_match_player_points 
								WHERE game_team_id = {$game_team_id} 
								AND player_id='{$player_id}' 
								GROUP BY game_id ORDER BY game_id DESC LIMIT 1;
							");
		
		$stats[0][0]['last_performance'] = @$last_performance[0][0];
		return $stats[0][0];
	}
}
