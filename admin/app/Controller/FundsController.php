<?php

App::uses('AppController', 'Controller');


class FundsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Funds';

	public function index(){

	}
	//all id_list are actually the masked user ids,
	//to get the original id, we must substract the masked one with Configure::read('RANK_RANDOM_NUM')
	public function add(){
		$arr = explode(PHP_EOL,$this->request->data['id_list']);
		$team_ids = array();
		//assign real IDs
		while(sizeof($arr)){
			$team_ids[] = intval(array_shift($arr)) - intval(Configure::read('RANK_RANDOM_NUM'));
		}	
		$teams = $this->Game->query("SELECT * FROM ffgame.game_teams game_team
									INNER JOIN ffgame.game_users game_users
									ON game_users.id = game_team.user_id
									INNER JOIN users user
									ON user.fb_id = game_users.fb_id
									INNER JOIN teams teams
									ON teams.user_id = user.id
									INNER JOIN ffgame.master_team master_team
									ON master_team.uid = teams.team_id
									WHERE game_team.id IN (".implode(',',$team_ids).")");
		$this->set('teams',$teams);
	}

}
