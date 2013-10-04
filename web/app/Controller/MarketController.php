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
		$user = $this->userDetail;
		$this->set('user',$user['User']);
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

		foreach($teams['data'] as $n=>$v){
			$teams['data'][$n]['stats']['points_earned'] = ($v['stats']['wins'] * 3) + 
															($v['stats']['draws']);
		}
		$this->set('teams',$this->sortTeamByPoints($teams['data']));
	}
	private function sortTeamByPoints($teams){
		
		$changes = false;
		$n = sizeof($teams);
		for($i=1;$i<sizeof($teams);$i++){
			$swap = false;
			$p = $teams[$i-1];
			$q = $teams[$i];
			
			if($q['stats']['points_earned'] > $p['stats']['points_earned']){
				$swap = true;
			}else if($q['stats']['points_earned'] == $p['stats']['points_earned']){
				//the most goals wins
				if(($q['stats']['goals'] - $q['stats']['conceded']) > ($p['stats']['goals'] - $p['stats']['conceded'])){
					$swap = true;
				}else if(($q['stats']['goals'] - $q['stats']['conceded']) == ($p['stats']['goals'] - $p['stats']['conceded'])){
					if($q['stats']['goals'] > $p['stats']['goals']){
						$swap = true;
					}
				}
			}
			
			if($swap){
				$changes = true;
				$teams[$i] = $p;
				$teams[$i-1] = $q;
			}

		}
		if($changes){
			return $this->sortTeamByPoints($teams);
		}
		return $teams;

	}
	public function team($team_id){
		$userData = $this->getUserData();
		$club = $this->Game->getClub($team_id);
		$this->set('club',$club);

		$players = $this->Game->getMasterTeam($team_id);

		//list of players
		$my_players = $this->Game->get_team_players($userData['fb_id']);

		$player_list = array();
		while(sizeof($players)>0){
			$p = array_shift($players);
			if(!$this->isMyPlayer($p['uid'],$my_players)){
				$player_list[] = $p;
			}
		}
		
		$this->set('players',$player_list);

	}
	private function isMyPlayer($player_id,$my_players){
		foreach($my_players as $m){
				if($m['uid']==$player_id){
					return true;
				}
			}
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
