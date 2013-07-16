<?php
/**
 * Manage Controller

 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class ManageController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Manage';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	private $userData;
	public function beforeFilter(){
		parent::beforeFilter();
		$this->loadModel('Team');
		$this->loadModel('User');

		$this->userData = $this->getUserData();
		if(!$this->hasTeam()){
			$this->redirect('/login/expired');
		}
	}
	public function hasTeam(){
		$userData = $this->userData;
		if(is_array($userData['team'])){
			return true;
		}
	}
	public function index(){
		$this->redirect('/manage/club');
	}
	public function club(){
		
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

		//list of players
		$players = $this->Game->get_team_players($userData['fb_id']);
		$this->set('players',$players);

		//list of staffs
		//get officials
		$officials = $this->Game->getAvailableOfficials($userData['team']['id']);
		$staffs = array();
		foreach($officials as $official){
			if(isset($official['hired'])){
				$staffs[] = $official;
			}
		}
		$this->set('staffs',$staffs);

		//financial statements
		$finance = $this->getFinancialStatements($userData['fb_id']);
		
		$this->set('finance',$finance);
	}
	private function getFinancialStatements($fb_id){
		$finance = $this->Game->financial_statements($fb_id);
		if($finance['status']==1){

			$report = array('total_matches' => $finance['data']['total_matches'],
							'budget' => $finance['data']['budget']);
			foreach($finance['data']['report'] as $n=>$v){
				$report[$v['item_name']] = $v['total'];
			}
			$report['total_earnings'] = intval(@$report['tickets_sold'])+
										intval(@$report['commercial_director_bonus'])+
										intval(@$report['marketing_manager_bonus'])+
										intval(@$report['public_relation_officer_bonus'])+
										intval(@$report['win_bonus']);
			return $report;
		}
	}
	public function hiring_staff(){
		
		$userData = $this->userData;

		if(isset($this->request->query['hire'])){
			$official_id = intval($this->request->query['id']);
			if($official_id>0){
				$this->Game->hire_staff($userData['team']['id'],$official_id);
			}
		}
		if(isset($this->request->query['dismiss'])){
			$official_id = intval($this->request->query['id']);
			if($official_id>0){
				$this->Game->dismiss_staff($userData['team']['id'],$official_id);
			}
		}
		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//get officials
		$officials = $this->Game->getAvailableOfficials($userData['team']['id']);

		//estimated costs
		$total_weekly_salary = 0;
		foreach($officials as $official){
			if(isset($official['hired'])){
				$total_weekly_salary+=$official['salary'];
			}
		}
		$this->set('officials',$officials);
		$this->set('weekly_salaries',$total_weekly_salary);
	}
	public function team(){
		
		$userData = $this->userData;
		//list of players
		$players = $this->Game->get_team_players($userData['fb_id']);
		
		$this->set('players',$players);

		//user data
		$user = $this->User->findByFb_id($userData['fb_id']);
		$this->set('user',$user['User']);

		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$this->set('club',$club['Team']);
		

		
		$next_match = $this->Game->getNextMatch($userData['team']['team_id']);
		if($next_match['match']['home_id']==$userData['team']['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}
		
		$this->set('next_match',$next_match['match']);

		//match venue
		$match_venue = $this->Game->getVenue($next_match['match']['home_id']);
		$this->set('venue',$match_venue);

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
		$rs = $this->Game->get_team_player_info($userData['fb_id'],$player_id);
		if($rs['status']==1){
			$this->set('data',$rs['data']);
		}
		
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
