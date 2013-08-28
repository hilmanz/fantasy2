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
		if($user['User']['avatar_img']==null){
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
		if($user['User']['avatar_img']==null){
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
		$response['stats']['points'] = intval(@$point['Point']['points']);
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
		
		$response['staffs'] = $staffs;

		//financial statements
		$finance = $this->getFinancialStatements($fb_id);
		
		$response['finance'] = $finance;

		$next_match = $this->Game->getNextMatch($game_team['team_id']);
		$next_match['match']['home_original_name'] = $next_match['match']['home_name'];
		$next_match['match']['away_original_name'] = $next_match['match']['away_name'];
		if($next_match['match']['home_id']==$game_team['team_id']){
			$next_match['match']['home_name'] = $club['Team']['team_name'];
		}else{
			$next_match['match']['away_name'] = $club['Team']['team_name'];
		}

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

		
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
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
	public function profile($act=null){
		$this->loadModel('User');
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		if($user['User']['avatar_img']==null){
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
		
		
		if($act=='save'){
			if($this->request->is('post')){
				$data = array(
					'name'=>@$this->request->data['name'],
					'email'=>@$this->request->data['email'],
					'city'=>@$this->request->data['city']
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
			$this->set('response',array('status'=>1,'data'=>$user['User']));
		}
		$this->render('default');
	}

}
