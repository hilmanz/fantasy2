<?php
/**
 * Market Controller

 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class MarketController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Market';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	public function beforeFilter(){
		parent::beforeFilter();
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		if(!$this->hasTeam()){
			$this->redirect('/login/expired');
		}
	}
	public function hasTeam(){
		$userData = $this->getUserData();
		if(is_array($userData['team'])){
			return true;
		}
	}

	public function index(){
		$teams = $this->Game->getMatchResultStats();
		$this->set('teams',$teams['data']);
	}
	public function team($team_id){
		$club = $this->Game->getClub($team_id);
		$this->set('club',$club);

		$players = $this->Game->getMasterTeam($team_id);

		$this->set('players',$players);
	}
	public function player($player_id){
		$userData = $this->userData;
		//user data
		$user = $this->User->findByFb_id($userData['fb_id']);
		$this->set('user',$user['User']);

		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$this->set('club',$club['Team']);

		//player detail : 
		$rs = $this->Game->get_player_info($player_id);
		
		if($rs['status']==1){
			if(isset($rs['data']['daily_stats'])&&sizeof($rs['data']['daily_stats'])>0){
				foreach($rs['data']['daily_stats'] as $n=>$v){
					$fixture = $this->Team->query("SELECT matchday,match_date,
										UNIX_TIMESTAMP(match_date) as ts
										FROM ffgame.game_fixtures 
										WHERE game_id='{$n}' 
										LIMIT 1");
					
					$rs['data']['daily_stats'][$n]['fixture'] = $fixture[0]['game_fixtures'];
					$rs['data']['daily_stats'][$n]['fixture']['ts'] = $fixture[0][0]['ts'];
				}
			}
			
			$this->set('data',$rs['data']);
		}
		
		//stats modifier
		$modifier = $this->Game->query("SELECT * FROM ffgame.game_matchstats_modifier as Modifier");
		$this->set('modifiers',$modifier);

		//enable OPTA Widget
		$this->set('ENABLE_OPTA',true);
		$this->set('OPTA_CUSTOMER_ID',Configure::read('OPTA_CUSTOMER_ID'));
		//-->

	}
	public function error(){
		$this->render('error');
	}
	public function team_error(){
		$this->set('error_type','team');
		$this->render('error');
	}
	public function success(){
		$this->render('success');
	}
}
