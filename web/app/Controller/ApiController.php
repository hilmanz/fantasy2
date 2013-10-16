<?php
/**
 * API controller.
 *
 * This file will serves as API endpoint
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('AppController', 'Controller');

class ApiController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Api';
	public $uses = array();
	private $weekly_balances = null;
	private $expenditures = null;
	private $starting_budget = 0;
	private $finance_total_items_raw = null;
	
	public function auth(){
		$fb_id = $this->request->query('fb_id');
		$user = $this->User->findByFb_id($fb_id);

		if(isset($user['User'])){
			$rs = $this->Apikey->findByApi_key($this->request->query['api_key']);
			if(isset($rs['Apikey']) && $rs['Apikey']['api_key']!=null){
				$access_token = encrypt_param(serialize(array('api_key'=>$rs['Apikey']['api_key'],
														  'valid_until'=>time()+24*60*60)));

				$this->redisClient->set($access_token,serialize(array('api_key'=>$rs['Apikey']['api_key'],
																	  'fb_id'=>$fb_id)));
				$this->redisClient->expire($access_token,24*60*60);//expires in 1 day
				$this->set('response',array('status'=>1,'access_token'=>$access_token));
			}else{
				$this->set('response',array('status'=>403,'error'=>'invalid api_key'));
			}
		}else{
			$this->set('response',array('status'=>400,'error'=>'user not found'));
		}
		
		$this->render('default');
	}
	public function index(){
		$this->set('response',array('status'=>1));
		$this->render('default');
	}
	
	public function team(){
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];

		$user = $this->User->findByFb_id($fb_id);
		if(strlen($user['User']['avatar_img'])<2){
			$user['User']['avatar_img'] = "http://graph.facebook.com/".$fb_id."/picture";
		}else{
			$user['User']['avatar_img'] = Configure::read('avatar_web_url').'120x120_'.$user['User']['avatar_img'];
		}
		$game_team = $this->Game->getTeam($fb_id);
		$this->loadModel('Point');

		$point = $this->Point->findByTeam_id($user['Team']['id']);

		$response['user'] = array('id'=>$user['User']['id'],
									'fb_id'=>$user['User']['fb_id'],
									'name'=>$user['User']['name'],
									'avatar_img'=>$user['User']['avatar_img']);

		$response['stats']['points'] = intval(@$point['Point']['points']);
		$response['stats']['rank'] = intval(@$point['Point']['rank']);



		//list of players
		$players = $this->Game->get_team_players($fb_id);
		$response['players'] = $players;

		//lineup starters
		$lineup = $this->Game->getLineup($game_team['id']);
		$response['lineup_settings'] = $lineup;
		
		//budget
		$budget = $this->Game->getBudget($game_team['id']);
		
		$response['budget'] = $budget;
		$response['stats']['club_value'] = intval($budget) + $response['stats']['points'];
		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		
		$response['club'] = array('id'=>$club['Team']['id'],
									'team_name'=>$club['Team']['team_name'],
									'team_id'=>$club['Team']['team_id'],
								  );

		//get original club
		$original_club = $this->Game->getClub($game_team['team_id']);
		
		$response['original_club'] = $original_club;

		$next_match = $this->Game->getNextMatch($game_team['team_id']);
		$next_match['match']['home_original_name'] = $next_match['match']['home_name'];
		$next_match['match']['away_original_name'] = $next_match['match']['away_name'];
		if($next_match['match']['home_id']==$game_team['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}
		$next_match['match']['match_date_ts'] = strtotime($next_match['match']['match_date']);
		$this->getCloseTime($next_match);

		$response['next_match'] = array('game_id'=>$next_match['match']['game_id'],
										'home_name'=>$next_match['match']['home_name'],
										'away_name'=>$next_match['match']['away_name'],
										'home_original_name'=>$next_match['match']['home_original_name'],
										'away_original_name'=>$next_match['match']['away_original_name'],
										'match_date'=>date("Y-m-d H:i:s",strtotime($next_match['match']['match_date'])),
										'match_date_ts'=>strtotime($next_match['match']['match_date'])
										);

		//match venue
		$match_venue = $this->Game->getVenue($next_match['match']['home_id']);
		$response['match_venue'] = $match_venue;

		//best match
		$best_match = $this->Game->getBestMatch($game_team['id']);
		$team_id = $game_team['team_id'];
		
		if($best_match['status']==0){
			$this->set('best_match','N/A');
			$response['stats']['best_match'] = 'N/A';
		}else{
			$best_match['data']['points'] = number_format($best_match['data']['points']);
			if($best_match['data']['match']['home_id']==$team_id){
				$against = $best_match['data']['match']['away_name'];
			}else if($best_match['data']['match']['away_id']==$team_id){
				$against = $best_match['data']['match']['home_name'];
			}
			
			$response['stats']['best_match'] = "VS. {$against} (+{$best_match['data']['points']})";
		}

		//last earnings
		$rs = $this->Game->getLastEarnings($game_team['id']);
		if($rs['status']==1){
			$this->set('last_earning',$rs['data']['total_earnings']);
			$response['stats']['last_earning'] = $rs['data']['total_earnings'];
		}else{
			$response['stats']['last_earning'] = 0;
		}

		//best player
		$rs = $this->Game->getBestPlayer($game_team['id']);
		
		if($rs['status']==1){
			$this->set('best_player',$rs['data']);
			$response['stats']['best_player'] = $rs['data'];
		}

		//close time
		$response['close_time'] = $this->closeTime;

		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}
	public function save_formation(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$this->loadModel('Info');
		if($this->request->is('post')){
			$api_session = $this->readAccessToken();
			$fb_id = $api_session['fb_id'];
			$user = $this->User->findByFb_id($fb_id);
			$game_team = $this->Game->getTeam($fb_id);

			$formation = $this->request->data['formation'];

			$players = array();
			foreach($this->request->data as $n=>$v){
				if(eregi('player-',$n)&&$v!=0){
					$players[] = array('player_id'=>str_replace('player-','',$n),'no'=>intval($v));
				}
			}
			$lineup = $this->Game->setLineup($game_team['id'],$formation,$players);
			
			if($lineup['status']==1){
				$msg = "@p1_".$user['User']['id']." telah menentukan formasinya.";
				$this->Info->write('set formation',$msg);
				$this->set('response',array('status'=>1,'message'=>'Formation is been saved successfully !'));
			}else{
				$this->set('response',array('status'=>0,'error'=>'There is an error in formation setup !'));
			}
			
		}else{
			$this->set('response',array('status'=>404,'error'=>'method not found'));
		}

		$this->render('default');
	}
	public function club(){

		$this->loadModel('Point');

		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		
		$user = $this->User->findByFb_id($fb_id);
		
		if(strlen($user['User']['avatar_img'])<2){
			$user['User']['avatar_img'] = "http://graph.facebook.com/".$fb_id."/picture";
		}else{
			$user['User']['avatar_img'] = Configure::read('avatar_web_url').'120x120_'.$user['User']['avatar_img'];
		}
		$game_team = $this->Game->getTeam($fb_id);
		
		$response = array();

		$point = $this->Point->findByTeam_id($user['Team']['id']);

		$response['user'] = array('id'=>$user['User']['id'],
									'fb_id'=>$user['User']['fb_id'],
									'name'=>$user['User']['name'],
									'avatar_img'=>$user['User']['avatar_img']);

		$response['stats']['points'] = intval(@$point['Point']['points']) + intval(@$point['Point']['extra_points']);
		$response['stats']['rank'] = intval(@$point['Point']['rank']);

		//budget
		$budget = $this->Game->getBudget($game_team['id']);
		$response['budget'] = $budget;

		$response['stats']['club_value'] = intval($budget) + $response['stats']['points'];

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$response['club'] = array('id'=>$club['Team']['id'],
									'team_name'=>$club['Team']['team_name'],
									'team_id'=>$club['Team']['team_id'],
								  );

		//get original club
		$original_club = $this->Game->getClub($club['Team']['team_id']);
		$this->set('original',$original_club);
		$response['original_club'] = $original_club;

		//list of players
		$players = $this->Game->get_team_players($fb_id);
		$response['players'] = $players;
		

		//players weekly salaries
		$weekly_salaries = 0;
		foreach($players as $p){
			$weekly_salaries += intval(@$p['salary']);
		}

		//lineup starters
		$lineup = $this->Game->getLineup($game_team['id']);
		$response['lineup_settings'] = $lineup;



		//list of staffs
		//get officials

		$officials = $this->Game->getAvailableOfficials($game_team['id']);
		
		$staffs = array();
		foreach($officials as $official){
			if(isset($official['hired'])){
				$staffs[] = $official;
			}
		}
		
		//staff's weekly salaries
		foreach($staffs as $p){
			$weekly_salaries += intval(@$p['salary']);
		}
		$response['weekly_salaries'] = $weekly_salaries;

		$response['staffs'] = $staffs;

		//financial statements
		$finance = $this->getFinancialStatements($fb_id);
		$financial_statement['finance'] = $finance;
		$financial_statement['weekly_balances'] = $this->weekly_balances;
		$financial_statement['total_items'] = $this->finance_total_items_raw;

		//last earnings
		$rs = $this->Game->getLastEarnings($game_team['id']);
		if($rs['status']==1){
			$financial_statement['last_earning'] = $rs['data']['total_earnings'];
		}else{
			$financial_statement['last_earning'] = 0;
		}

		//last expenses
		$rs = $this->Game->getLastExpenses($game_team['id']);
		if($rs['status']==1){
			$financial_statement['last_expenses'] = $rs['data']['total_expenses'];
		}else{
			$financial_statement['last_expenses'] = 0;
		}
		$financial_statement['expenditures'] = $this->expenditures;
		$financial_statement['starting_budget'] = $this->starting_budget;



		


		$response['finance'] = $finance;
		$response['finance_details'] = $financial_statement;

		$next_match = $this->Game->getNextMatch($game_team['team_id']);
		$next_match['match']['home_original_name'] = $next_match['match']['home_name'];
		$next_match['match']['away_original_name'] = $next_match['match']['away_name'];
		if($next_match['match']['home_id']==$game_team['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}

		$next_match['match']['match_date_ts'] = strtotime($next_match['match']['match_date']);
		$this->getCloseTime($next_match);

		$response['next_match'] = array('game_id'=>$next_match['match']['game_id'],
										'home_name'=>$next_match['match']['home_name'],
										'away_name'=>$next_match['match']['away_name'],
										'home_original_name'=>$next_match['match']['home_original_name'],
										'away_original_name'=>$next_match['match']['away_original_name'],
										'match_date'=>date("Y-m-d H:i:s",strtotime($next_match['match']['match_date'])),
										'match_date_ts'=>strtotime($next_match['match']['match_date'])
										);
		//match venue
		$match_venue = $this->Game->getVenue($next_match['match']['home_id']);
		$response['match_venue'] = $match_venue;

		//best match
		$best_match = $this->Game->getBestMatch($game_team['id']);
		$team_id = $game_team['team_id'];
		
		if($best_match['status']==0){
			$this->set('best_match','N/A');
			$response['stats']['best_match'] = 'N/A';
		}else{
			$best_match['data']['points'] = number_format($best_match['data']['points']);
			if($best_match['data']['match']['home_id']==$team_id){
				$against = $best_match['data']['match']['away_name'];
			}else if($best_match['data']['match']['away_id']==$team_id){
				$against = $best_match['data']['match']['home_name'];
			}
			
			$response['stats']['best_match'] = "VS. {$against} (+{$best_match['data']['points']})";
		}

		//last earnings
		$rs = $this->Game->getLastEarnings($game_team['id']);
		if($rs['status']==1){
			$this->set('last_earning',$rs['data']['total_earnings']);
			$response['stats']['last_earning'] = $rs['data']['total_earnings'];
		}else{
			$response['stats']['last_earning'] = 0;
		}

		//best player
		$rs = $this->Game->getBestPlayer($game_team['id']);
		
		if($rs['status']==1){
			$this->set('best_player',$rs['data']);
			$response['stats']['best_player'] = $rs['data'];
		}

		//close time
		$response['close_time'] = $this->closeTime;


		//weekly points and weekly balances

		//for weekly points, make sure the points from other player are included
		$this->loadModel('Weekly_point');
		$this->Weekly_point->virtualFields['TotalPoints'] = 'SUM(Weekly_point.points)';
		$options = array('fields'=>array('Weekly_point.id', 'Weekly_point.team_id', 
							'Weekly_point.game_id', 'Weekly_point.matchday', 'Weekly_point.matchdate', 
							'SUM(Weekly_point.points) AS TotalPoints', 'Team.id', 'Team.user_id', 
							'Team.team_id','Team.team_name'),
			'conditions'=>array('Weekly_point.team_id'=>$club['Team']['id']),
	        'limit' => 100,
	        'group' => 'Weekly_point.matchday',
	        'order' => array(
	            'matchday' => 'asc'
	        ));
		$weekly_points = $this->Weekly_point->find('all',$options);
		$weekly_team_points = array();
		while(sizeof($weekly_points) > 0){
			$p = array_shift($weekly_points);
			$weekly_team_points[] = array(
					'game_id'=>$p['Weekly_point']['game_id'],
					'matchday'=>$p['Weekly_point']['matchday'],
					'matchdate'=>$p['Weekly_point']['matchdate'],
					'points'=>$p[0]['TotalPoints']
				);
		}
		unset($weekly_points);


		$response['weekly_stats']['balances'] = $financial_statement['weekly_balances'];
		$response['weekly_stats']['points'] = $weekly_team_points;

		


		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}
	/*
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
	}*/
	private function getWeeklyFinancialStatement($weekly_finance){
		$weekly_statement = array();
		$total_items = array();
		while(sizeof($weekly_finance['transactions'])>0){
			$p = array_shift($weekly_finance['transactions']);
			$weekly_statement[$p['item_name']] = $p['amount'];
			$total_items[$p['item_name']] = $p['item_total'];
		}

		$weekly_statement['total_earnings'] = intval(@$weekly_statement['tickets_sold'])+
									intval(@$weekly_statement['commercial_director_bonus'])+
									intval(@$weekly_statement['marketing_manager_bonus'])+
									intval(@$weekly_statement['public_relation_officer_bonus'])+
									intval(@$weekly_statement['win_bonus'])+
									intval(@$weekly_statement['player_sold'])
									;
		return array('transaction'=>$weekly_statement,'total_items'=>$total_items);
	}
	private function getMatches($arr,$expenditures){
		
		$matches = array();
		if(sizeof($arr)>0){
			$game_ids = array();

			foreach($arr as $a){
				$game_ids[] = "'".$a['game_id']."'";
			}

			$a_game_ids = implode(',',$game_ids);
			$sql = "SELECT game_id,home_id,away_id,b.name AS home_name,c.name AS away_name,
					a.matchday,a.match_date,a.home_score,a.away_score
					FROM ffgame.game_fixtures a
					INNER JOIN ffgame.master_team b
					ON a.home_id = b.uid
					INNER JOIN ffgame.master_team c
					ON a.away_id = c.uid
					WHERE (a.home_id = '{$this->userData['team']['team_id']}' 
							OR a.away_id = '{$this->userData['team']['team_id']}')
					AND EXISTS (SELECT 1 FROM ffgame_stats.game_match_player_points d
								WHERE d.game_id = a.game_id 
								AND d.game_team_id = {$this->userData['team']['id']} LIMIT 1)
					ORDER BY a.game_id";
			$rs = $this->Game->query($sql);
			

			foreach($rs as $n=>$r){
				$points = 0;
				$balance = 0;
				foreach($arr as $a){
					if($r['a']['matchday']==$a['matchday']){
						$points = $a['points'];
						break;
					}
				}
				foreach($expenditures as $b){
					if($r['a']['game_id']==$b['game_id']){
						$income = $b['total_income'];
						break;
					}
				}
				$match = $r['a'];
				$match['home_name'] = $r['b']['home_name'];
				$match['away_name'] = $r['c']['away_name'];
				$match['points'] = $points;
				$match['income'] = $income;
				$matches[] = $match;
			}

			//clean memory
			$rs = null;
			unset($rs);
		}
		return $matches;
	}
	private function getFinancialStatements($fb_id){
		$finance = $this->Game->financial_statements($fb_id);
		
		$this->weekly_balances = @$finance['data']['weekly_balances'];
		$this->expenditures = @$finance['data']['expenditures'];
		$this->starting_budget = @intval($finance['data']['starting_budget']);

		if($finance['status']==1){

			$report = array('total_matches' => $finance['data']['total_matches'],
							'budget' => $finance['data']['budget']);
			$total_items = array();
			foreach($finance['data']['report'] as $n=>$v){
				$report[$v['item_name']] = $v['total'];
				$total_items[$v['item_name']] = $v['item_total'];
			}
			$report['total_earnings'] = intval(@$report['tickets_sold'])+
										intval(@$report['commercial_director_bonus'])+
										intval(@$report['marketing_manager_bonus'])+
										intval(@$report['public_relation_officer_bonus'])+
										intval(@$report['win_bonus'])+
										intval(@$report['player_sold'])
										;
			$this->finance_total_items_raw = $total_items;
			return $report;
		}
	}
	public function profile($act=null){
		$this->loadModel('User');
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		if(strlen($user['User']['avatar_img'])<2){
			$user['User']['avatar_img'] = "http://graph.facebook.com/".$fb_id."/picture";
		}else{
			$user['User']['avatar_img'] = Configure::read('avatar_web_url').'120x120_'.$user['User']['avatar_img'];
		}
		
		$game_team = $this->Game->getTeam($fb_id);
		//club
		$club = $this->Team->findByUser_id($user['User']['id']);

		$next_match = $this->Game->getNextMatch($game_team['team_id']);
		$next_match['match']['home_original_name'] = $next_match['match']['home_name'];
		$next_match['match']['away_original_name'] = $next_match['match']['away_name'];

		if($next_match['match']['home_id']==$game_team['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}
		$next_match['match']['match_date_ts'] = strtotime($next_match['match']['match_date']);
		$this->getCloseTime($next_match);
		
		if($act=='save'){
			if($this->request->is('post')){
				$data = array(
					'name'=>@$this->request->data['name'],
					'email'=>@$this->request->data['email'],
					'location'=>@$this->request->data['location']
				);
				$this->User->id = $user['User']['id'];
				$rs = $this->User->save($data);
				$rs['User']['next_match'] = array('game_id'=>$next_match['match']['game_id'],
										'home_name'=>$next_match['match']['home_name'],
										'away_name'=>$next_match['match']['away_name'],
										'home_original_name'=>$next_match['match']['home_original_name'],
										'away_original_name'=>$next_match['match']['away_original_name'],
										'match_date'=>date("Y-m-d H:i:s",strtotime($next_match['match']['match_date'])),
										'match_date_ts'=>strtotime($next_match['match']['match_date'])
										);
				$user['User']['close_time'] = $this->closeTime;
				$this->set('response',array('status'=>1,'data'=>$rs['User']));
			}else{
				$this->set('response',array('status'=>0,'error'=>'Cannot save profile'));
			}
			
		}else{
			$user['User']['next_match'] = array('game_id'=>$next_match['match']['game_id'],
										'home_name'=>$next_match['match']['home_name'],
										'away_name'=>$next_match['match']['away_name'],
										'home_original_name'=>$next_match['match']['home_original_name'],
										'away_original_name'=>$next_match['match']['away_original_name'],
										'match_date'=>date("Y-m-d H:i:s",strtotime($next_match['match']['match_date'])),
										'match_date_ts'=>strtotime($next_match['match']['match_date'])
										);
			$user['User']['close_time'] = $this->closeTime;
			$this->set('response',array('status'=>1,'data'=>$user['User']));
		}
		$this->render('default');
	}
	private function getCloseTime($nextMatch){
		
		$this->nextMatch = $nextMatch;

		$previous_close_dt = date("Y-m-d", strtotime("previous Saturday"))." 17:00:00";
		

		$close_dt = date("Y-m-d", strtotime("next Saturday"))." 17:00:00";
		
		$next_match_ts = $this->nextMatch['match']['match_date_ts'];
		if(date_default_timezone_get()=='Asia/Jakarta'){
		    $next_match_ts += 6*60*60;
		}
		
		if($next_match_ts > strtotime($close_dt)){
			$close_time = array("datetime"=>$close_dt,
							"ts"=>strtotime($close_dt));
		}else{
			$close_time = array("datetime"=>$previous_close_dt,
							"ts"=>strtotime($previous_close_dt));
		}
	
		$this->closeTime = $close_time;
	}

	public function test(){
		$this->set('response',array('status'=>1,'data'=>array()));
		$this->render('default');
	}
	private function getRingkasanClub(){

	}
}
