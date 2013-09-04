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
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->loadModel('Team');
		$this->loadModel('User');

		
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
		$user = $this->userDetail;

		$this->set('user',$user['User']);

		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$this->set('club',$club['Team']);

		//get original club
		$original_club = $this->Game->getClub($club['Team']['team_id']);
		$this->set('original',$original_club);
		
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


		//enable OPTA Widget
		$this->set('ENABLE_OPTA',true);
		$this->set('OPTA_CUSTOMER_ID',Configure::read('OPTA_CUSTOMER_ID'));
		//-->

		
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
				$rs = $this->Game->hire_staff($userData['team']['id'],$official_id);
				if($rs['status']==1){
					$msg = "@p1_".$this->userDetail['User']['id']." telah merekrut {$rs['officials']['name']} baru.";
					$this->Info->write('set formation',$msg);
				}
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
		$user = $this->userDetail;
		$this->set('user',$user['User']);
		

		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$this->set('club',$club['Team']);
	
		$next_match = $this->nextMatch;
		$next_match['match']['home_original_name'] = $next_match['match']['home_name'];
		$next_match['match']['away_original_name'] = $next_match['match']['away_name'];
		if($next_match['match']['home_id']==$userData['team']['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}
		
		$this->set('next_match',$next_match['match']);

		//match venue
		$match_venue = $this->Game->getVenue($next_match['match']['home_id']);
		$this->set('venue',$match_venue);

		//best match
		$best_match = $this->Game->getBestMatch($userData['team']['id']);
		$team_id = $userData['team']['team_id'];

		if($best_match['status']==0){
			$this->set('best_match','N/A');
		}else{
			$best_match['data']['points'] = number_format($best_match['data']['points']);
			if($best_match['data']['match']['home_id']==$team_id){
				$against = $best_match['data']['match']['away_name'];
			}else if($best_match['data']['match']['away_id']==$team_id){
				$against = $best_match['data']['match']['home_name'];
			}
			$this->set('best_match',"VS. {$against} (+{$best_match['data']['points']})");
		}

		//last earnings
		$rs = $this->Game->getLastEarnings($userData['team']['id']);
		if($rs['status']==1){
			$this->set('last_earning',$rs['data']['total_earnings']);
		}else{
			$this->set('last_earning',0);
		}

		//best player
		$rs = $this->Game->getBestPlayer($userData['team']['id']);
		
		if($rs['status']==1){
			$this->set('best_player',$rs['data']);
		}

		$this->set('first_time',$this->Session->read('first_time'));
		$this->Session->write('first_time',false);

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

		//$this->set('tab',@$this->request->query['tab']);
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
	/**
	*	fungsi2 dibawah ini harus dimatiin di production
	*/
	public function reset(){
		if(Configure::read('debug')>0){
			if(@$this->request->query['confirm']==1){
				//perform deletion here
				// remove di database game
				$user_id = $this->userData['team']['user_id'];
				$team_id = $this->userData['team']['id'];
				$this->User->query("DELETE FROM ffgame.game_users WHERE id ={$user_id};");
				$this->User->query("DELETE FROM ffgame.game_teams WHERE id = {$team_id};");
				$this->User->query("DELETE FROM ffgame.game_team_players WHERE game_team_id = {$team_id};");
				//remove di database frontend.

				$user = $this->User->findByFb_id($this->userData['fb_id']);
				
				$id = $user['User']['id'];
				$club = $this->Team->findByUser_id($user['User']['id']);
				$this->Team->delete($club['Team']['id']);
				$this->User->delete($id);
				//hapus session
				$this->Session->destroy();
				$this->set('confirm',1);
			}else if(@$this->request->query['confirm']==2){
				$this->redirect('/manage/team');
			}else{
				$this->set('confirm',0);	
			}
		}else{
			$this->redirect('/manage/team');
		}
	}
	public function play_match(){
		if(Configure::read('debug')>0){
			$userData = $this->userData;
			$user = $this->User->findByFb_id($userData['fb_id']);
			$club = $this->Team->findByUser_id($user['User']['id']);
			$next_match = $this->Game->getNextMatch($userData['team']['team_id']);
			$this->loadModel('Team');
			$rs = $this->Team->query("UPDATE ffgame.game_fixtures SET is_processed = 0 
								WHERE id={$next_match['match']['id']}");
		}else{
			$this->redirect('/manage/team');
		}
	}
	public function reset_matches(){
		if(Configure::read('debug')>0){
			$this->loadModel('Team');
			$rs = $this->Team->query("UPDATE ffgame.game_fixtures 
										SET period='PreMatch',is_processed = 1");
		}else{
			$this->redirect('/manage/team');
		}
	}
	public function reset_finance(){
		if(Configure::read('debug')>0){
			$userData = $this->userData;
			$user = $this->User->findByFb_id($userData['fb_id']);
			$club = $this->Game->getTeam($userData['fb_id']);
			
			$this->loadModel('Team');
			$rs = $this->Team->query("DELETE FROM ffgame.game_team_expenditures
								WHERE game_team_id={$club['id']}");
		}else{
			$this->redirect('/manage/team');
		}
	}
	public function new_user_event(){
		if(Configure::read('debug')>0){
			$this->loadModel('Info');
			$msg = "@p1_".$this->userDetail['User']['id']." telah bergabung ke dalam liga.";
			$this->Info->write('new player',$msg);
			$this->redirect('/');
		}else{
			$this->redirect('/manage/team');
		}
	}
}
