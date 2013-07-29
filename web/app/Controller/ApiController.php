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
		
		$rs = $this->Apikey->findByApi_key($this->request->query['api_key']);
		if(isset($rs['Apikey']) && $rs['Apikey']['api_key']!=null){
			$access_token = encrypt_param(serialize(array('api_key'=>$rs['Apikey']['api_key'],
													  'valid_until'=>time()+24*60*60)));

			$this->redisClient->set($access_token,serialize(array('api_key'=>$rs['Apikey']['api_key'])));
			$this->redisClient->expire($access_token,24*60*60);//expires in 1 day
			$this->set('response',array('status'=>1,'access_token'=>$access_token));
		}else{
			$this->set('response',array('status'=>403,'error'=>'invalid api_key'));
		}
		
		$this->render('default');
	}
	public function index(){
		$this->set('response',array('status'=>1));
		$this->render('default');
	}
	public function available_teams(){

	}
	public function player(){

	}
	public function team(){
		$fb_id = $this->request->query['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		$game_team = $this->Game->getTeam($fb_id);
		$this->loadModel('Point');

		$point = $this->Point->findByTeam_id($user['Team']['id']);

		$response['user'] = $user;

		$response['stats']['points'] = intval($point['Point']['points']);
		$response['stats']['rank'] = intval($point['Point']['rank']);

		//list of players
		$players = $this->Game->get_team_players($fb_id);
		$response['players'] = $players;

		//lineup starters
		$lineup = $this->Game->getLineup($game_team['id']);
		$response['lineup_settings'] = $lineup;
		
		//budget
		$budget = $this->Game->getBudget($game_team['id']);
		
		$response['budget'] = $budget;
		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		
		$response['club'] = $club;

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

		$response['next_match'] = $next_match['match'];

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

	}
	public function club(){
		$this->loadModel('Point');
		$fb_id = $this->request->query['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		$game_team = $this->Game->getTeam($fb_id);
		
		$response = array();

		$point = $this->Point->findByTeam_id($user['Team']['id']);

		$response['user'] = $user;
		$response['stats']['points'] = $point['Point']['points'];
		$response['stats']['rank'] = $point['Point']['rank'];

		//budget
		$budget = $this->Game->getBudget($game_team['id']);
		$response['budget'] = $budget;

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$response['club'] = $club;

		//get original club
		$original_club = $this->Game->getClub($club['Team']['team_id']);
		$this->set('original',$original_club);
		$response['original_club'] = $original_club;

		//list of players
		$players = $this->Game->get_team_players($fb_id);
		$response['players'] = $players;
		

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
	public function finance(){

	}
	public function register(){

		$this->set('response',array('status'=>1));
		$this->render('default');
	}
}
