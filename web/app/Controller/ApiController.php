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
App::uses('Sanitize', 'Utility');
require_once APP . 'Vendor' . DS. 'Thumbnail.php';

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
	private $tickets_sold = null;
	public function auth(){
		$fb_id = $this->request->query('fb_id');
		$user = $this->User->findByFb_id($fb_id);
		$check_current_session = $this->Session->read('API_CURRENT_ACCESS_TOKEN');
		if(strlen($fb_id) > 2 && $this->validateAPIAccessToken($check_current_session)){
			$this->gameApiAccessToken = $check_current_session;
			$api_session = $this->readAccessToken();
			$session_fb_id = $api_session['fb_id'];

			if($fb_id==$session_fb_id){
				$this->set('response',array('status'=>1,'access_token'=>$check_current_session));	
			}else{
				$this->set('response',array('status'=>400,'error'=>'user not found'));
			}
			
		}else{
			if(strlen($fb_id)>2 && isset($user['User'])){
				$rs = $this->Apikey->findByApi_key($this->request->query['api_key']);
				if(isset($rs['Apikey']) && $rs['Apikey']['api_key']!=null){
					$access_token = encrypt_param(serialize(array('fb_id'=>$fb_id,
															'api_key'=>$rs['Apikey']['api_key'],
															  'valid_until'=>time()+24*60*60)));

					$this->redisClient->set($access_token,serialize(array('api_key'=>$rs['Apikey']['api_key'],
																		  'fb_id'=>$fb_id)));
					$this->redisClient->expire($access_token,24*60*60);//expires in 1 day
					$this->Session->write('API_CURRENT_ACCESS_TOKEN',$access_token);
					$this->set('response',array('status'=>1,'access_token'=>$access_token));
				}else{
					$this->set('response',array('status'=>403,'error'=>'invalid api_key'));
				}
			}else{
				$this->set('response',array('status'=>400,'error'=>'user not found'));
			}
		}
		CakeLog::write('MOBILE', 'auth - '.$fb_id.' - '.$this->Session->read('API_CURRENT_ACCESS_TOKEN'));
		$this->gameApiAccessToken = $this->Session->read('API_CURRENT_ACCESS_TOKEN');
		$api_session = $this->readAccessToken();
		$session_fb_id = $api_session['fb_id'];
		if($fb_id!=$session_fb_id){
			CakeLog::write('AUTH_ERROR', 'auth - '.$fb_id.' - '.$session_fb_id.' - '.$this->Session->read('API_CURRENT_ACCESS_TOKEN'));
			$this->gameApiAccessToken = null;
			$this->Session->write('API_CURRENT_ACCESS_TOKEN',null);
			CakeLog::write('AUTH_ERROR', 'auth - '.$fb_id.' - '.$session_fb_id.' - '.$this->Session->read('API_CURRENT_ACCESS_TOKEN').' DELETED');

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

		$response['stats']['points'] = ceil(floatval(@$point['Point']['points']));
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
			$best_match['data']['points'] = ceil($best_match['data']['points']);
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
		//can updte formation
		if($this->closeTime > time() && $this->openTime < time()){
			$response['can_update_formation'] = 1;	
		}else{
			$response['can_update_formation'] = 0;
		}
		
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}
	public function save_formation(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$this->loadModel('Info');

		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		$game_team = $this->Game->getTeam($fb_id);


		//can updte formation
		$can_update_formation = true;


		
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


		
		if(time() > $this->closeTime['ts'] && Configure::read('debug') == 0){
		    
		    $can_update_formation = false;
		    if(time() > $this->openTime){
		       
		        $can_update_formation = true;
		    }
		}else{
		    if(time() < $this->openTime){
		       
		        $can_update_formation = false;
		    }
		}

		if($can_update_formation){
			
			if($this->request->is('post')){
				
				

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
		}else{
			$this->set('response',array('status'=>0,'error'=>'you cannot update formation at these moment, please wait until the matches is over.'));
		}

		$this->render('default');
	}
	public function club(){

		$this->loadModel('Point');

		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		
		$user = $this->User->findByFb_id($fb_id);
		

		CakeLog::write('AUTH_ERROR', 'browse - '.$fb_id.' - club - '.$this->request->query['access_token']);

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

		$response['stats']['points'] = ceil(floatval(@$point['Point']['points']) + floatval(@$point['Point']['extra_points']));
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

		foreach($players as $n=>$p){
			$last_performance = floatval($p['last_performance']);
			$performance_bonus = getTransferValueBonus($last_performance,intval($p['transfer_value']));
			$player[$n]['base_transfer_value'] = $p['transfer_value'];
			$players[$n]['transfer_value'] = intval($p['transfer_value']) + $performance_bonus;

		}

		$response['players'] = $players;
		
		$best_players = subval_rsort($players,'points');

		if($best_players[0]['points'] == 0){
			$best_players = array();
		}
		$response['best_players'] = $best_players;
		if(sizeof($best_players)==0){
			$response['stats']['best_player'] = new stdClass();
		}
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
		$financial_statement['tickets_sold'] = $this->tickets_sold;
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
		$next_match['match']['last_match_ts'] = strtotime($next_match['match']['last_match']);

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
			$best_match['data']['points'] = ceil($best_match['data']['points']);
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

		//can updte formation
		if($this->closeTime > time() && $this->openTime < time()){
			$response['can_update_formation'] = 1;	
		}else{
			$response['can_update_formation'] = 0;
		}

		//weekly points and weekly balances

		//for weekly points, make sure the points from other player are included
		$this->loadModel('Weekly_point');
		$this->Weekly_point->virtualFields['TotalPoints'] = 'SUM(Weekly_point.points + Weekly_point.extra_points)';
		$options = array('fields'=>array('Weekly_point.id', 'Weekly_point.team_id', 
							'Weekly_point.game_id', 'Weekly_point.matchday', 'Weekly_point.matchdate', 
							'SUM(Weekly_point.points + Weekly_point.extra_points) AS TotalPoints', 'Team.id', 'Team.user_id', 
							'Team.team_id','Team.team_name'),
			'conditions'=>array('Weekly_point.team_id'=>$club['Team']['id']),
	        'limit' => 100,
	        'group' => 'Weekly_point.matchday',
	        'order' => array(
	            'matchday' => 'asc'
	        ));
		$weekly_points = $this->Weekly_point->find('all',$options);
		if(sizeof($weekly_points) > 0){
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
		}else{
			$weekly_team_points[] = array(
						'game_id'=>'',
						'matchday'=>$p['Weekly_point']['matchday'],
						'matchdate'=>$p['Weekly_point']['matchdate'],
						'points'=>$p[0]['TotalPoints']
					);
		}
		

		unset($weekly_points);


		$response['weekly_stats']['balances'] = $financial_statement['weekly_balances'];
		$response['weekly_stats']['points'] = $weekly_team_points;


		//matches
		$matches = $this->getMatches($game_team['id'],$game_team['team_id'],
										$weekly_team_points,
										$financial_statement['expenditures'],
										$financial_statement['tickets_sold']);

		$response['previous_matches'] = $matches;


		//user's coin
		//get recent cash
		$response['coins'] = intval($this->Game->getCash($game_team['id']));

		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}

	public function matchinfo($game_id){
		$game_id = Sanitize::paranoid($game_id);

		$api_session = $this->readAccessToken();

		$fb_id = $api_session['fb_id'];
		
		$user = $this->User->findByFb_id($fb_id);
		
		
		$game_team = $this->Game->getTeam($fb_id);
		$response = array();
		
		//match details

		$players = $this->Game->getMatchDetailsByGameTeamId($game_team['id'],$game_id);

		
		
		
		//poin modifiers
		$rs = $this->Team->query("SELECT name,
										g as Goalkeeper,
										d as Defender,
										m as Midfielder,
										f as Forward
										FROM ffgame.game_matchstats_modifier as stats;");

		$modifier = array();
		foreach($rs as $r){
			$modifier[$r['stats']['name']] = $r['stats'];
		}
		$rs = null;
		unset($rs);

		$fixture  = $this->Game->query("SELECT a.*,b.name as home_name,c.name as away_name
										FROM ffgame.game_fixtures a
										INNER JOIN ffgame.master_team b
										ON a.home_id = b.uid
										INNER JOIN ffgame.master_team c
										ON a.away_id = c.uid
										WHERE a.game_id='{$game_id}'
										LIMIT 1");
		$match = $fixture[0]['a'];
		$match['home_name'] = $fixture[0]['b']['home_name'];
		$match['away_name'] = $fixture[0]['c']['away_name'];
		
		if($user['Team']['team_id'] == $match['home_id']){
		    $home = $user['Team']['team_name'];
		    $away = $match['away_name'];
		}else{
		    $away = $user['Team']['team_name'];
		    $home = $match['home_name'];
		}

		$response['home'] = $home;
		$response['away'] = $away;
		$response['players'] = $this->compilePlayerPerformance($players['data']);
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	
	}
	private function compilePlayerPerformance($players){
		$overall_points = 0;

        foreach($players as $player_id=>$detail){
            $games = $this->getTotalPoints('game_started,total_sub_on',$detail['ori_stats']);

            
            $attacking_and_passing = $this->getTotalPoints('att_freekick_goal,att_ibox_goal,att_obox_goal,att_pen_goal,att_freekick_post,ontarget_scoring_att,att_obox_target,big_chance_created,big_chance_scored,goal_assist,total_att_assist,second_goal_assist,final_third_entries,fouled_final_third,pen_area_entries,won_contest,won_corners,penalty_won,last_man_contest,accurate_corners_intobox,accurate_cross_nocorner,accurate_freekick_cross,accurate_launches,long_pass_own_to_opp_success,successful_final_third_passes,accurate_flick_on',
                                    $detail['ori_stats']);
            $defending = $this->getTotalPoints('aerial_won,ball_recovery,duel_won,effective_blocked_cross,effective_clearance,effective_head_clearance,interceptions_in_box,interception_won,poss_won_def_3rd,poss_won_mid_3rd,poss_won_att_3rd,won_tackle,offside_provoked,last_man_tackle,outfielder_block',$detail['ori_stats']);

            $goalkeeping = $this->getTotalPoints('dive_catch,dive_save,stand_catch,stand_save,cross_not_claimed,good_high_claim,punches,good_one_on_one,accurate_keeper_sweeper,gk_smother,saves,goals_conceded',$detail['ori_stats']);
            $mistakes_and_errors = $this->getTotalPoints('penalty_conceded,red_card,yellow_card,challenge_lost,dispossessed,fouls,overrun,total_offside,unsuccessful_touch,error_lead_to_shot,error_lead_to_goal',$detail['ori_stats']);

            $total_poin = $games + $attacking_and_passing + $defending +
                          $goalkeeping + $mistakes_and_errors;

            $overall_points += $total_poin;
            $players[$player_id]['statistics'] = array('games'=>$games,
            											'attacking_and_passing'=>$attacking_and_passing,
            											'defending'=>$defending,
            											'goalkeeping'=>$goalkeeping,
            											'mistakes_and_errors'=>$mistakes_and_errors,
            											'total_poin'=>$total_poin);
        }
        return $players;
	}
	private function getPoin($position,$stats_name,$modifier){
   
	    return intval(@$modifier[$stats_name][$position]);
	}
	private function getTotalPoints($str,$stats){
	    $arr = explode(",",$str);
	    $total = 0;
	    foreach($arr as $a){
	        $total += floatval(@$stats[$a]['points']);
	    }
	    return $total;
	}
	public function player($player_id){
		require_once APP . 'Vendor' . DS. 'stats.locale.php';
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

		$response['stats']['points'] = ceil(floatval(@$point['Point']['points']) + floatval(@$point['Point']['extra_points']));
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


		//player detail : 
		$rs = $this->Game->get_team_player_info($fb_id,$player_id);

		
		//stats modifier
		$modifiers = $this->Game->query("SELECT * FROM ffgame.game_matchstats_modifier as Modifier");
		
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

			//generate stats from overall data.

			

		}
		$games = array(
		        'game_started'=>'game_started',
		        'sub_on'=>'total_sub_on'
		    );

		$passing_and_attacking = array(
		        'Freekick Goal'=>'att_freekick_goal',
		        'Goal inside the box'=>'att_ibox_goal',
		        'Goal Outside the Box'=>'att_obox_goal',
		        'Penalty Goal'=>'att_pen_goal',
		        'Freekick Shots'=>'att_freekick_post',
		        'On Target Scoring Attempt'=>'ontarget_scoring_att',
		        'Shot From Outside the Box'=>'att_obox_target',
		        'big_chance_created'=>'big_chance_created',
		        'big_chance_scored'=>'big_chance_scored',
		        'goal_assist'=>'goal_assist',
		        'total_assist_attempt'=>'total_att_assist',
		        'Second Goal Assist'=>'second_goal_assist',
		        'final_third_entries'=>'final_third_entries',
		        'fouled_final_third'=>'fouled_final_third',
		        'pen_area_entries'=>'pen_area_entries',
		        'won_contest'=>'won_contest',
		        'won_corners'=>'won_corners',
		        'penalty_won'=>'penalty_won',
		        'last_man_contest'=>'last_man_contest',
		        'accurate_corners_intobox'=>'accurate_corners_intobox',
		        'accurate_cross_nocorner'=>'accurate_cross_nocorner',
		        'accurate_freekick_cross'=>'accurate_freekick_cross',
		        'accurate_launches'=>'accurate_launches',
		        'long_pass_own_to_opp_success'=>'long_pass_own_to_opp_success',
		        'successful_final_third_passes'=>'successful_final_third_passes',
		        'accurate_flick_on'=>'accurate_flick_on'
		    );


		$defending = array(
		        'aerial_won'=>'aerial_won',
		        'ball_recovery'=>'ball_recovery',
		        'duel_won'=>'duel_won',
		        'effective_blocked_cross'=>'effective_blocked_cross',
		        'effective_clearance'=>'effective_clearance',
		        'effective_head_clearance'=>'effective_head_clearance',
		        'interceptions_in_box'=>'interceptions_in_box',
		        'interception_won' => 'interception_won',
		        'possession_won_def_3rd' => 'poss_won_def_3rd',
		        'possession_won_mid_3rd' => 'poss_won_mid_3rd',
		        'possession_won_att_3rd' => 'poss_won_att_3rd',
		        'won_tackle' => 'won_tackle',
		        'offside_provoked' => 'offside_provoked',
		        'last_man_tackle' => 'last_man_tackle',
		        'outfielder_block' => 'outfielder_block'
		    );

		$goalkeeper = array(
		                'dive_catch'=> 'dive_catch',
		                'dive_save'=> 'dive_save',
		                'stand_catch'=> 'stand_catch',
		                'stand_save'=> 'stand_save',
		                'cross_not_claimed'=> 'cross_not_claimed',
		                'good_high_claim'=> 'good_high_claim',
		                'punches'=> 'punches',
		                'good_one_on_one'=> 'good_one_on_one',
		                'accurate_keeper_sweeper'=> 'accurate_keeper_sweeper',
		                'gk_smother'=> 'gk_smother',
		                'saves'=> 'saves',
		                'goals_conceded'=>'goals_conceded'
		                    );


		$mistakes_and_errors = array(
		            'penalty_conceded'=>'penalty_conceded',
		            'red_card'=>'red_card',
		            'yellow_card'=>'yellow_card',
		            'challenge_lost'=>'challenge_lost',
		            'dispossessed'=>'dispossessed',
		            'fouls'=>'fouls',
		            'overrun'=>'overrun',
		            'total_offside'=>'total_offside',
		            'unsuccessful_touch'=>'unsuccessful_touch',
		            'error_lead_to_shot'=>'error_lead_to_shot',
		            'error_lead_to_goal'=>'error_lead_to_goal'
		            );
		$map = array('games'=>$games,
		              'passing_and_attacking'=>$passing_and_attacking,
		              'defending'=>$defending,
		              'goalkeeper'=>$goalkeeper,
		              'mistakes_and_errors'=>$mistakes_and_errors
		             );

		$data = $rs['data'];
		switch($data['player']['position']){
		    case 'Forward':
		        $pos = "f";
		    break;
		    case 'Midfielder':
		        $pos = "m";
		    break;
		    case 'Defender':
		        $pos = "d";
		    break;
		    default:
		        $pos = 'g';
		    break;
		}
		$total_points = 0;
		$main_stats_vals = array('games'=>0,
		                            'passing_and_attacking'=>0,
		                            'defending'=>0,
		                            'goalkeeper'=>0,
		                            'mistakes_and_errors'=>0,
		                         );


		
		if(isset($data['overall_stats'])){
			foreach($data['overall_stats'] as $stats){
		        $total_points += $stats['points'];
		        $main_stats_vals[$stats['stats_category']]+= $stats['points'];
		    }
		    

			
		    $profileStats = $this->getStatsIndividual('games',$pos,$modifiers,$map,$data['overall_stats']);
		    $games = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$games[$statsName] = $statsVal;
		    }
			$profileStats = $this->getStatsIndividual('passing_and_attacking',$pos,$modifiers,$map,$data['overall_stats']);
		    $passing_and_attacking = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$passing_and_attacking[$statsName] = $statsVal;
		    }
		    $profileStats = $this->getStatsIndividual('defending',$pos,$modifiers,$map,$data['overall_stats']);
		    $defending = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$defending[$statsName] = $statsVal;
		    }
           
		    $profileStats = $this->getStatsIndividual('goalkeeper',$pos,$modifiers,$map,$data['overall_stats']);
		    $goalkeeper = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$goalkeeper[$statsName] = $statsVal;
		    }

		    $profileStats = $this->getStatsIndividual('mistakes_and_errors',$pos,$modifiers,$map,$data['overall_stats']);
		    $mistakes_and_errors = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$mistakes_and_errors[$statsName] = $statsVal;
		    }

			$stats = array(
				'games'=>$games,
				'passing_and_attacking'=>$passing_and_attacking,
				'defending'=>$defending,
				'goalkeeping'=>$goalkeeper,
				'mistakes_and_errors'=>$mistakes_and_errors,

			);
		}else{
			$stats = array();
			$main_stats_vals = array();
		}


		$performance = 0;

        if(sizeof($data['stats'])>0){
            if(intval(@$data['stats'][sizeof($data['stats'])-1]['points'])!=0){

            	$performance = getTransferValueBonus(
                                $data['stats'][sizeof($data['stats'])-1]['performance'],
                                $data['player']['transfer_value']);
            }  
        }
        
        $data['player']['transfer_value'] = $data['player']['transfer_value'] + $performance;

		$response['player'] = array('info'=>$data['player'],
									 'summary'=>$main_stats_vals,
										'stats'=>$stats);

		
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}

	private function getModifierValue($modifiers,$statsName,$pos){
	    foreach($modifiers as $m){
	        if($m['Modifier']['name']==$statsName){
	            return ($m['Modifier'][$pos]);
	        }
	    }
	    return 0;
	}
	private function getStatsIndividual($category,$pos,$modifiers,$map,$stats){
	    $collection = array();
	    $statTypes = $map[$category];
	    foreach($stats as $st){
	        if($st['stats_category']==$category){
	            foreach($statTypes as $n=>$v){
	                if(!isset($collection[$n])){
		                $collection[$n] = array('total'=>0,'points'=>0);
		            }
	                if($st['stats_name'] == $v){

	                    $collection[$n] = array('total'=>$st['total'],
	                                            'points'=>$st['points']);
	                }
	            }
	        }
	    }
	    return $collection;
	}
	private function getStats($category,$pos,$modifiers,$map,$stats){
	    
	    
	    $statTypes = $map[$category];
	    //pr($statTypes);
	    $collection = array();
	    foreach($stats as $s){
	        foreach($statTypes as $n=>$v){
	            if(!isset($collection[$n])){
	                $collection[$n] = array('total'=>0,'points'=>0);
	            }
	            if($s['stats_name'] == $v){
	                $collection[$n] = array('total'=>$s['total'],
	                                    'points'=>$s['total'] * $this->getModifierValue($modifiers,$v,$pos));
	            }
	        }
	    }
	    
	    return $collection;
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
	
	private function getMatches($game_team_id,$team_id,$arr,$expenditures,$tickets_sold){
		
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
					WHERE (a.home_id = '{$team_id}' 
							OR a.away_id = '{$team_id}')
					AND EXISTS (SELECT 1 FROM ffgame_stats.game_match_player_points d
								WHERE d.game_id = a.game_id 
								AND d.game_team_id = {$game_team_id} LIMIT 1)
					ORDER BY a.game_id";
			$rs = $this->Game->query($sql);
			

			foreach($rs as $n=>$r){
				$points = 0;
				$balance = 0;
				$income = 0;
				foreach($arr as $a){
					if($r['a']['matchday']==$a['matchday']){
						$points = $a['points'];
						break;
					}
				}
				foreach($tickets_sold as $b){
					if($r['a']['game_id']==$b['game_id']){
						$income = $b['total_income'];
						break;
					}
				}
				$match = $r['a'];
				
				if($r['a']['home_id'] == $team_id){
					$match['against'] = $r['c']['away_name'];
				}else{
					$match['against'] = $r['b']['home_name'];
				}
				$match['home_name'] = $r['b']['home_name'];
				$match['away_name'] = $r['c']['away_name'];
				$match['points'] = intval(@$points);
				$match['income'] = intval(@$income);
				$matches[] = $match;
			}

			//clean memory
			$rs = null;
			unset($rs);
		}
		return $matches;
	}
	public function finance(){
		$this->loadModel('Point');

		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		
		$user = $this->User->findByFb_id($fb_id);		
		
		$game_team = $this->Game->getTeam($fb_id);

		
		
		
		//getting staffs
		$officials = $this->Game->getAvailableOfficials($game_team['id']);
		$staffs = array();
		foreach($officials as $official){
			if(isset($official['hired'])){
				$staffs[] = $official;
			}
		}
		unset($officials);


		$finance = $this->getFinancialStatements($fb_id);
		$financial_statement['transaction'] = $finance;
		$financial_statement['weekly_balances'] = $this->weekly_balances;
		$financial_statement['total_items'] = $this->finance_total_items_raw;
		$financial_statement['tickets_sold'] = $this->tickets_sold;
		

		$response = $this->populateFinancialStatement(0,
													$finance,
													$staffs,
													$this->weekly_balances,
													$this->starting_budget,
													$this->finance_total_items_raw);
		$this->set('response',$response);
		$this->render('default');
	}
	private function populateFinancialStatement($week,$finance,$staffs,$weekly_balances,
												$starting_budget,$total_items){


		$response['status'] = 1;
		//financial statements
		$total_expenses = $this->getFinanceTotalExpenses($finance);


		$sponsor = 0;
		$sponsor += intval(@$finance['Joining_Bonus']);
		$sponsor += intval(@$finance['sponsorship']);


		//income from other events
		$other = $this->getFinanceOtherIncomes($finance);

		//expenses from other events
		$other_expenses = $this->getFinanceOtherExpenses($finance);
		$total_expenses -= $other_expenses;


		

		//get starting balance and running balance
		$balance_health = $this->getFinancialBalances($week,$weekly_balances,$starting_budget);
		$running_balance = $balance_health['running_balance'];
		$starting_balance = $balance_health['starting_balance'];
		//---- end of starting and running balance

		$staff_token = array();
		foreach($staffs as $staff){
		  $staff_token[] = str_replace(" ","_",strtolower($staff['name']));
		}
		

		//render arrays
		$income = $this->getFinanceIncomes($finance,$sponsor,$other,$total_items,$staff_token);
		$expense = $this->getFinanceExpenses($finance,$other_expenses,$total_items,$staff_token);
		$financial = array(
			'last_week_balance'=>array(
									'name'=>'Neraca Minggu Lalu',
									'description'=>'',
									'total'=>'ss$ '.number_format($starting_balance),
								),
			'incomes'=>$income,
			'expenses'=>$expense,
			'total_income'=>array(
				'name'=>'Total Perolehan',
				'description'=>'',
				'total'=>'ss$ '.number_format(abs(@$finance['total_earnings']))
			),
			'total_expenses'=>array(
				'name'=>'Total Pengeluaran',
				'description'=>'',
				'total'=>'ss$ '.number_format((@$total_expenses))
			),
			'running_balance'=>array(
				'name'=>'Neraca Berjalan',
				'description'=>'',
				'total'=>'ss$ '.number_format(@$running_balance)
			)
		);


		$response['data'] = $financial;

		return $response;

	}
	private function getFinanceExpenses($finance,$other_expenses,$total_items,$staff_token){
		$expenses = array();
		array_push($expenses,array(
					'name'=>'Biaya Operasional',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['operating_cost']))
			));

		array_push($expenses,array(
					'name'=>'Gaji Pemain',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['player_salaries']))
			));

		if(isset($finance['compensation_fee'])){
			array_push($expenses,array(
					'name'=>'Biaya Kompesansi',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['compensation_fee']))
			));
		}
		if(isset($finance['ticket_sold_penalty'])){
			array_push($expenses,array(
					'name'=>'Pinalti hasil penjualan tiket',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['ticket_sold_penalty']))
			));
		}
		if(isset($finance['security_overtime_fee'])){
			array_push($expenses,array(
					'name'=>'Biaya Overtime Sekuriti',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['security_overtime_fee']))
			));
		}
		if(isset($finance['buy_player'])){
			array_push($expenses,array(
					'name'=>'Pembelian Pemain',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['buy_player']))
			));
		}
		if($other_expenses > 0){
			array_push($expenses,array(
					'name'=>'Pengeluaran Lainnya',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$other_expenses))
			));
		}
		return $expenses;
	}
	private function getFinanceIncomes($finance,$sponsor,$other,$total_items,$staff_token){

		$income = array(
						array(
							'name'=>'Tiket Terjual',
							'description'=>'ss$'.round($finance['tickets_sold']/$total_items['tickets_sold'],2).
											' x '.number_format(@$total_items['tickets_sold']),
							'total'=>'ss$ '.number_format(@$finance['tickets_sold'])
						),
					);

	  	if($this->isStaffExist($staff_token,'commercial_director')){
		  	array_push($income,array(
					'name'=>'Bonus Commercial Director',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['commercial_director_bonus']))
			));
		}
		if($this->isStaffExist($staff_token,'marketing_manager')){
		  	array_push($income,array(
					'name'=>'Bonus Marketing Manager',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['marketing_manager_bonus']))
			));
		}
		if($this->isStaffExist($staff_token,'public_relation_officer')){
		  	array_push($income,array(
					'name'=>'Bonus Public Relation Officer',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$finance['public_relation_officer_bonus']))
			));
		}

       
		//sponsor
		array_push($income,array(
					'name'=>'Sponsor',
					'description'=>'',
					'total'=>'ss$ '.number_format(abs(@$sponsor))
			));

		//player sold
		if(isset($finance['player_sold'])){
			array_push($income,array(
					'name'=>'Penjualan Pemain',
					'description'=>'',
					'total'=>'ss$ '.number_format($finance['player_sold'])
			));
		}

		if(isset($finance['win_bonus'])){
			array_push($income,array(
					'name'=>'Bonus',
					'description'=>'Kemenangan',
					'total'=>'ss$ '.number_format($finance['win_bonus'])
			));
		}
		if($other > 0){
			array_push($income,array(
					'name'=>'Bonus',
					'description'=>'Lain-lain',
					'total'=>'ss$ '.number_format($other)
			));
		}
		return $income;
	}
	private function getFinanceOtherIncomes($finance){
		$other = 0;
		foreach($finance as $item_name => $item_value){
		  if($item_value > 0 && @eregi('other_',$item_name)){
		    $other += $item_value;
		  }
		  if($item_value > 0 && @eregi('event',$item_name)){
		    $other += $item_value;
		  }
		  if($item_value > 0 && @eregi('perk',$item_name)){
		    $other += $item_value;
		  }
		}
		return $other;
	}
	private function getFinancialBalances($week,$weekly_balances,$starting_budget){
		
		$first_week = $weekly_balances[0];
		$my_balance = $weekly_balances;
		$previous_balances = array();
		for($i=1;$i<$first_week['week'];$i++){
		  $previous_balances[] = array('week'=>$i,
		                              'balance'=>intval(@$starting_budget));
		}

		
		$weekly_balances = array_merge($previous_balances,$weekly_balances);

		if($week<=1){
		  $starting_balance = intval(@$starting_budget);
		}else{
		  $starting_balance = $weekly_balances[$week-2]['balance'];

		}
		if($week==0){
		  $running_balance = intval(@$weekly_balances[sizeof($weekly_balances)-1]['balance']);
		}else{
		  $running_balance = intval(@$weekly_balances[$week-1]['balance']);  
		}
		return array(
			'starting_balance'=>$starting_balance,
			'running_balance'=>$running_balance
		);
	}
	private function getFinanceOtherExpenses($finance){
		$other_expenses = 0;
		foreach($finance as $item_name => $item_value){
		  if($item_value < 0 && @eregi('other_',$item_name)){
		    $other_expenses += abs($item_value);
		  }
		  if($item_value < 0 && @eregi('transaction_fee',$item_name)){
		    $other_expenses += abs($item_value);
		  }
		}
		return $other_expenses;
	}
	private function getFinanceTotalExpenses($finance){
		$total_expenses = 0;
		$total_expenses+= intval(@$finance['operating_cost']);
		$total_expenses+= intval(@$finance['player_salaries']);
		$total_expenses+= intval(@$finance['commercial_director']);
		$total_expenses+= intval(@$finance['marketing_manager']);
		$total_expenses+= intval(@$finance['public_relation_officer']);
		$total_expenses+= intval(@$finance['head_of_security']);
		$total_expenses+= intval(@$finance['football_director']);
		$total_expenses+= intval(@$finance['chief_scout']);
		$total_expenses+= intval(@$finance['general_scout']);
		$total_expenses+= intval(@$finance['finance_director']);
		$total_expenses+= intval(@$finance['tax_consultant']);
		$total_expenses+= intval(@$finance['accountant']);
		$total_expenses+= intval(@$finance['buy_player']);

		$total_expenses+= intval(@$finance['compensation_fee']);
		$total_expenses+= intval(@$finance['ticket_sold_penalty']);
		$total_expenses+= intval(@$finance['security_overtime_fee']);
		return $total_expenses;
	}
	public function weekly_finance($week=1){
		$this->loadModel('Point');

		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		
		$user = $this->User->findByFb_id($fb_id);		
		
		$game_team = $this->Game->getTeam($fb_id);


		
		//get overall data first so that we can retrieve weekly_balances and starting budget
		//getting staffs
		$officials = $this->Game->getAvailableOfficials($game_team['id']);
		$staffs = array();
		foreach($officials as $official){
			if(isset($official['hired'])){
				$staffs[] = $official;
			}
		}
		unset($officials);


		$finance = $this->getFinancialStatements($fb_id);

		
		$weekly_finance = $this->Game->weekly_finance($fb_id,$week);
		$weekly_statement = $this->getWeeklyFinancialStatement($weekly_finance);


		
		$response = $this->populateFinancialStatement($week,
													$weekly_statement['transaction'],
													$staffs,
													$this->weekly_balances,
													$this->starting_budget,
													$weekly_statement['total_items']);
		$this->set('response',$response);
		$this->render('default');


	}
	private function isStaffExist($staff_token,$name){ 
	  foreach($staff_token as $token){
	    if($token==$name){
	      return true;
	    }
	  }
	}
	private function getWeeklyFinancialStatement($weekly_finance){
		$weekly_statement = array();
		$total_items = array();
		$weekly_statement['total_earnings'] = 0;
		$weekly_statement['other_income'] = 0;
		$weekly_statement['other_expenses'] = 0;
		while(sizeof($weekly_finance['transactions'])>0){
			$p = array_shift($weekly_finance['transactions']);
			$weekly_statement[$p['item_name']] = $p['amount'];

			$total_items[$p['item_name']] = $p['item_total'];
			if($p['amount'] > 0 && @eregi('other_',$p['item_name'])){
				$weekly_statement['other_income']+= intval($p['amount']);
				unset($weekly_statement[$p['item_name']]);
			}
			if($p['amount'] > 0 && @eregi('perk-',$p['item_name'])){
				$weekly_statement['other_income']+= intval($p['amount']);
				unset($weekly_statement[$p['item_name']]);
			}
			if($p['amount'] < 0 && @eregi('other_',$p['item_name'])){
				$weekly_statement['other_expenses']+= intval($p['amount']);
				unset($weekly_statement[$p['item_name']]);
			}
			if($p['amount'] < 0 && @eregi('perk-',$v['item_name'])){
				$weekly_statement['other_expenses']+= intval($p['amount']);
				unset($weekly_statement[$p['item_name']]);
			}
			if($p['amount'] < 0 && @eregi('transaction_fee_',$p['item_name'])){
				$weekly_statement['other_expenses']+= intval($p['amount']);
				unset($weekly_statement[$p['item_name']]);
			}
			if($p['amount'] > 0){
				$weekly_statement['total_earnings'] += $p['amount'];
			}
		}
		if(isset($weekly_statement['Joining_Bonus'])){
			$weekly_statement['sponsorship'] = $weekly_statement['Joining_Bonus'];
			unset($weekly_statement['Joining_Bonus']);
		}
		

		return array('transaction'=>$weekly_statement,'total_items'=>$total_items);
	}
	private function getFinancialStatements($fb_id){
		$finance = $this->Game->financial_statements($fb_id);
		
		$this->weekly_balances = @$finance['data']['weekly_balances'];
		$this->expenditures = @$finance['data']['expenditures'];
		$this->starting_budget = @intval($finance['data']['starting_budget']);
		$this->tickets_sold = @$finance['data']['tickets_sold'];

		if($finance['status']==1){

			$report = array('total_matches' => $finance['data']['total_matches'],
							'budget' => $finance['data']['budget']);
			$total_items = array();
			$report['total_earnings'] = 0;
			$report['other_income'] = 0;
			$report['other_expenses'] = 0;
			foreach($finance['data']['report'] as $n=>$v){
				$report[$v['item_name']] = $v['total'];
				$total_items[$v['item_name']] = $v['item_total'];

				if($v['total'] > 0 && @eregi('other_',$v['item_name'])){
					$report['other_income']+= intval($v['total']);
					unset($report[$v['item_name']]);
				}
				if($v['total'] > 0 && @eregi('perk-',$v['item_name'])){
					$report['other_income']+= intval($v['total']);
					unset($report[$v['item_name']]);
				}
				if($v['total'] < 0 && @eregi('other_',$v['item_name'])){
					$report['other_expenses']+= intval($v['total']);
					unset($report[$v['item_name']]);
				}
				if($v['total'] < 0 && @eregi('perk-',$v['item_name'])){
					$report['other_expenses']+= intval($v['total']);
					unset($report[$v['item_name']]);
				}
				if($v['total'] < 0 && @eregi('transaction_fee_',$v['item_name'])){
					$report['other_expenses']+= intval($v['total']);
					unset($report[$v['item_name']]);
				}
				if($v['total'] > 0){
					$report['total_earnings'] += $v['total'];
				}
			}
			if(isset($report['Joining_Bonus'])){
				$report['sponsorship'] = $report['Joining_Bonus'];
				unset($report['Joining_Bonus']);
			}
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
					'location'=>@$this->request->data['location'],
					'phone_number'=>$this->request->data['handphone']
				);
				//update team name
				$this->loadModel('Team');
				$this->Team->id = intval($user['Team']['id']);
				$this->Team->save(array(
						'team_name' => $this->request->data['club']
				));
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
			$user['User']['team_name'] = $user['Team']['team_name'];
			$this->set('response',array('status'=>1,'data'=>$user['User']));
		}
		$this->render('default');
	}
	public function save_avatar(){
		$this->loadModel('User');
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
		if(isset($_FILES['name'])&&strlen($_FILES['name'])>0){
			$_FILES['file']['name'] = str_replace(array(' ','\''),"_",$_FILES['file']['name']);
			if(move_uploaded_file($_FILES['file']['tmp_name'],
					Configure::read('avatar_img_dir').$_FILES['file']['name'])){
				//resize to 120x120 pixels
				$thumb = new Thumbnail();
				$thumb->resizeImage('resizeCrop', $_FILES['file']['name'], 
								Configure::read('avatar_img_dir'), 
								'120x120_'.$_FILES['file']['name'], 
								120, 
								120, 
								100);
				//save to db
				$data = array(
					'avatar_img'=>$_FILES['file']['name']
				);
				if(intval($user['User']['id']) > 0){
					$this->User->id = $user['User']['id'];
					$rs = $this->User->save($data);
					$this->set('response',array('status'=>1,'files'=>$_FILES['file']['name']));	
				}else{
					$this->set('response',array('status'=>400,'error'=>'User not found'));
				}
				
			}else{
				$this->set('response',array('status'=>0,'error'=>'cannot save the uploaded file.'));
			}
		}else if(isset($_POST['file'])){
			$buffer = base64_decode($_POST['file']);
			$new_filename = 'f'.time().rand(0,99999).".jpg";
			$fp = fopen(Configure::read('avatar_img_dir').$new_filename, "wb");
			$w = fwrite($fp, $buffer);
			fclose($fp);
			
			//resize to 120x120 pixels
			$thumb = new Thumbnail();
			$thumb->resizeImage('resizeCrop', $new_filename, 
							Configure::read('avatar_img_dir'), 
							'120x120_'.$new_filename, 
							120, 
							120, 
							100);
			
			if($w){
				//save to db
				$data = array(
					'avatar_img'=>$new_filename
				);
				if(intval($user['User']['id']) > 0){
					$this->User->id = $user['User']['id'];
					$rs = $this->User->save($data);
					$this->set('response',array('status'=>1,'files'=>$new_filename));	
				}else{
					$this->set('response',array('status'=>400,'error'=>'User not found'));
				}
			}else{
				$this->set('response',array('status'=>501,'error'=>'no file uploaded'));
			}
			
			//$this->set('response',array('status'=>2,'error'=>'masih testing','file'=>Configure::read('avatar_img_dir').$new_filename));
			
		}else{
			$this->set('response',array('status'=>500,'error'=>'no file uploaded'));
		}
		$this->render('default');
	}
	private function getCloseTime($nextMatch){
		
		$this->nextMatch = $nextMatch;
		
		$previous_match = $this->nextMatch['match']['previous_setup'];
				
		$upcoming_match = $this->nextMatch['match']['matchday_setup'];
		
		try{
			$last_matchday = @$this->nextMatch['match']['matchday'] - 1;
		
			$previous_match = @$this->nextMatch['match']['previous_setup'];
			
			$upcoming_match = @$this->nextMatch['match']['matchday_setup'];
		}catch(Exception $e){
			$last_matchday = 0;
			$previous_match = null;
			$upcoming_match = null;
		}



		if($previous_match!=null && $upcoming_match !=null){
			//check the previous match backend proccess status

			$matchstatus = $this->Game->getMatchStatus($previous_match['matchday']);
			
			if($matchstatus['is_finished']==0){

				//if the backend process is not finished,
				//we use the previous match's close time, but use the next match's opentime + 30 days
				$close_time = array("datetime"=>$previous_match['start_dt'],
								"ts"=>strtotime($previous_match['start_dt']));

				$open_time = strtotime($upcoming_match['end_dt']) + (60*60*24*30);
				
			}	
			else if(
				//get close time and open time compare to previous match
				(time() < strtotime($previous_match['start_dt']))
				||
				(time() <= strtotime($previous_match['end_dt']))

			  ){
				$close_time = array("datetime"=>$previous_match['start_dt'],
								"ts"=>strtotime($previous_match['start_dt']));

				$open_time = strtotime($previous_match['end_dt']);
				$matchstatus = $this->Game->getMatchStatus($previous_match['matchday']);
				if($matchstatus['is_finished']==0){
					$open_time += (60*60*24*30);
				}
				
			}else{
				if(time() < strtotime($upcoming_match['start_dt'])){
					//jika pertandingan belum di mulai.. maka open time itu diset berdasarkan
					//opentime minggu lalu
					$open_time = strtotime($previous_match['end_dt']);
				}else if(time() > strtotime($upcoming_match['start_dt'])
						 && time() <= strtotime($upcoming_match['end_dt'])){
					//jika tidak, menggunakan open time berikutnya
					$open_time = strtotime($upcoming_match['end_dt']);
				}else{
					$open_time = strtotime($upcoming_match['end_dt']);
					$matchstatus = $this->Game->getMatchStatus($upcoming_match['matchday']);
					if($matchstatus['is_finished']==0){
						$open_time += (60*60*24*30);
					}
				}

				
				$close_time = array("datetime"=>$upcoming_match['start_dt'],
								"ts"=>strtotime($upcoming_match['start_dt']));

				
			}
		}

		$this->closeTime = $close_time;

		

		//formation open time
		
		$this->openTime = $open_time;
				
	}

	public function test(){
		$this->set('response',array('status'=>1,'data'=>array()));
		$this->render('default');
	}
	private function getRingkasanClub(){

	}

	//transfer players stuffs//////
	public function team_list(){
		$teams = $this->Game->getMatchResultStats();

		foreach($teams['data'] as $n=>$v){
			$teams['data'][$n]['stats']['points_earned'] = ($v['stats']['wins'] * 3) + 
															($v['stats']['draws']);
		}
		$rs = $this->sortTeamByPoints($teams['data']);
		$this->set('response',array('status'=>1,'data'=>$rs));
		$this->render('default');
	}
	private function sortTeamByPoints($teams){
		
		$changes = false;
		$n = sizeof($teams);
		for($i=1; $i < sizeof($teams); $i++){
			$swap = false;
			$p = $teams[$i-1];
			$q = $teams[$i];
			$p['stats']['goals'] = intval(@$p['stats']['goals']);
			$p['stats']['conceded'] = intval(@$p['stats']['conceded']);

			$q['stats']['goals'] = intval(@$q['stats']['goals']);
			$q['stats']['conceded'] = intval(@$q['stats']['conceded']);

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

	public function view_team($team_id){
		$this->loadModel('User');
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$userData = $this->User->findByFb_id($fb_id);

		
		$club = $this->Game->getClub($team_id);
		
		$players = $this->Game->getMasterTeam($team_id);

		//list of players
		$my_players = $this->Game->get_team_players($fb_id);
		
		$player_list = array();
		while(sizeof($players)>0){
			$p = array_shift($players);
			$p['stats']['points'] = floatval($p['stats']['points']);
			if(!$this->isMyPlayer($p['uid'],$my_players)){
				if($p['transfer_value']>0){
					$player_list[] = $p;	
				}
				
			}
		}
		foreach($player_list as $n=>$player){
                       
            if($player['transfer_value']>0){
            
                if(intval(@$player['stats']['last_point'])!=0){
                    $player['transfer_value'] = round($player['transfer_value'] + 
                                                getTransferValueBonus(
                                                    floatval(@$player['stats']['performance']),
                                                    $player['transfer_value']));
                }
            }
            $player_list[$n] = $player;
        }
		$rs = array('club'=>$club,
					'players'=>$player_list);

		$this->set('response',array('status'=>1,'data'=>$rs));
		$this->render('default');
	}

	private function isMyPlayer($player_id,$my_players){
		foreach($my_players as $m){
			if($m['uid']==$player_id){
				return true;
			}
		}
	}
	public function view_player($player_id){
		require_once APP . 'Vendor' . DS. 'stats.locale.php';
		$this->loadModel('User');
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

		$response['stats']['points'] = ceil(floatval(@$point['Point']['points']) + floatval(@$point['Point']['extra_points']));
		$response['stats']['rank'] = intval(@$point['Point']['rank']);

		//budget
		$budget = $this->Game->getBudget($game_team['id']);
		$response['budget'] = $budget;

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


		//player detail : 
		$rs = $this->Game->get_player_info($player_id);
		
		
		
		
		//stats modifier
		$modifiers = $this->Game->query("SELECT * FROM ffgame.game_matchstats_modifier as Modifier");

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
			
			
		}
		$games = array(
		        'game_started'=>'game_started',
		        'sub_on'=>'total_sub_on'
		    );

		$passing_and_attacking = array(
		        'Freekick Goal'=>'att_freekick_goal',
		        'Goal inside the box'=>'att_ibox_goal',
		        'Goal Outside the Box'=>'att_obox_goal',
		        'Penalty Goal'=>'att_pen_goal',
		        'Freekick Shots'=>'att_freekick_post',
		        'On Target Scoring Attempt'=>'ontarget_scoring_att',
		        'Shot From Outside the Box'=>'att_obox_target',
		        'big_chance_created'=>'big_chance_created',
		        'big_chance_scored'=>'big_chance_scored',
		        'goal_assist'=>'goal_assist',
		        'total_assist_attempt'=>'total_att_assist',
		        'Second Goal Assist'=>'second_goal_assist',
		        'final_third_entries'=>'final_third_entries',
		        'fouled_final_third'=>'fouled_final_third',
		        'pen_area_entries'=>'pen_area_entries',
		        'won_contest'=>'won_contest',
		        'won_corners'=>'won_corners',
		        'penalty_won'=>'penalty_won',
		        'last_man_contest'=>'last_man_contest',
		        'accurate_corners_intobox'=>'accurate_corners_intobox',
		        'accurate_cross_nocorner'=>'accurate_cross_nocorner',
		        'accurate_freekick_cross'=>'accurate_freekick_cross',
		        'accurate_launches'=>'accurate_launches',
		        'long_pass_own_to_opp_success'=>'long_pass_own_to_opp_success',
		        'successful_final_third_passes'=>'successful_final_third_passes',
		        'accurate_flick_on'=>'accurate_flick_on'
		    );


		$defending = array(
		        'aerial_won'=>'aerial_won',
		        'ball_recovery'=>'ball_recovery',
		        'duel_won'=>'duel_won',
		        'effective_blocked_cross'=>'effective_blocked_cross',
		        'effective_clearance'=>'effective_clearance',
		        'effective_head_clearance'=>'effective_head_clearance',
		        'interceptions_in_box'=>'interceptions_in_box',
		        'interception_won' => 'interception_won',
		        'possession_won_def_3rd' => 'poss_won_def_3rd',
		        'possession_won_mid_3rd' => 'poss_won_mid_3rd',
		        'possession_won_att_3rd' => 'poss_won_att_3rd',
		        'won_tackle' => 'won_tackle',
		        'offside_provoked' => 'offside_provoked',
		        'last_man_tackle' => 'last_man_tackle',
		        'outfielder_block' => 'outfielder_block'
		    );

		$goalkeeper = array(
		                'dive_catch'=> 'dive_catch',
		                'dive_save'=> 'dive_save',
		                'stand_catch'=> 'stand_catch',
		                'stand_save'=> 'stand_save',
		                'cross_not_claimed'=> 'cross_not_claimed',
		                'good_high_claim'=> 'good_high_claim',
		                'punches'=> 'punches',
		                'good_one_on_one'=> 'good_one_on_one',
		                'accurate_keeper_sweeper'=> 'accurate_keeper_sweeper',
		                'gk_smother'=> 'gk_smother',
		                'saves'=> 'saves',
		                'goals_conceded'=>'goals_conceded'
		                    );


		$mistakes_and_errors = array(
		            'penalty_conceded'=>'penalty_conceded',
		            'red_card'=>'red_card',
		            'yellow_card'=>'yellow_card',
		            'challenge_lost'=>'challenge_lost',
		            'dispossessed'=>'dispossessed',
		            'fouls'=>'fouls',
		            'overrun'=>'overrun',
		            'total_offside'=>'total_offside',
		            'unsuccessful_touch'=>'unsuccessful_touch',
		            'error_lead_to_shot'=>'error_lead_to_shot',
		            'error_lead_to_goal'=>'error_lead_to_goal'
		            );
		$map = array('games'=>$games,
		              'passing_and_attacking'=>$passing_and_attacking,
		              'defending'=>$defending,
		              'goalkeeper'=>$goalkeeper,
		              'mistakes_and_errors'=>$mistakes_and_errors
		             );
		
		$data = $rs['data'];

		
		switch($data['player']['position']){
		    case 'Forward':
		        $pos = "f";
		    break;
		    case 'Midfielder':
		        $pos = "m";
		    break;
		    case 'Defender':
		        $pos = "d";
		    break;
		    default:
		        $pos = 'g';
		    break;
		}
		$total_points = 0;
		$main_stats_vals = array('games'=>0,
		                            'passing_and_attacking'=>0,
		                            'defending'=>0,
		                            'goalkeeper'=>0,
		                            'mistakes_and_errors'=>0,
		                         );



		if(isset($data['overall_stats'])){
		    foreach($data['overall_stats'] as $stats){
		        foreach($map as $mainstats=>$substats){
		            foreach($substats as $n=>$v){
		                
		                if($v==$stats['stats_name']){
		                    if(!isset($main_stats_vals[$mainstats])){
		                        $main_stats_vals[$mainstats] = 0;
		                        $main_stats_ori[$mainstats] = 0;
		                    }
		                    $main_stats_vals[$mainstats] += ($stats['total'] *
		                                                    $this->getModifierValue($modifiers,
		                                                                            $v,
		                                                                            $pos));

		                   
		                }
		            }
		        }
		    }
		    foreach($main_stats_vals as $n){
		        $total_points += $n;
		    }

			

			
		    $profileStats = $this->getStats('games',$pos,$modifiers,$map,$data['overall_stats']);
		    $games = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$games[$statsName] = $statsVal;
		    }
			$profileStats = $this->getStats('passing_and_attacking',$pos,$modifiers,$map,$data['overall_stats']);
		    $passing_and_attacking = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$passing_and_attacking[$statsName] = $statsVal;
		    }
		    $profileStats = $this->getStats('defending',$pos,$modifiers,$map,$data['overall_stats']);
		    $defending = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$defending[$statsName] = $statsVal;
		    }
           
		    $profileStats = $this->getStats('goalkeeper',$pos,$modifiers,$map,$data['overall_stats']);
		    $goalkeeper = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$goalkeeper[$statsName] = $statsVal;
		    }

		    $profileStats = $this->getStats('mistakes_and_errors',$pos,$modifiers,$map,$data['overall_stats']);
		    $mistakes_and_errors = array();
		    foreach($profileStats as $statsName=>$statsVal){
		    	$statsName = stats_translated($statsName,'id');
		    	$mistakes_and_errors[$statsName] = $statsVal;
		    }

			$stats = array(
				'games'=>$games,
				'passing_and_attacking'=>$passing_and_attacking,
				'defending'=>$defending,
				'goalkeeping'=>$goalkeeper,
				'mistakes_and_errors'=>$mistakes_and_errors,

			);
			
		}
		$performance = 0;
        if(sizeof($data['stats'])>0){
            if(intval(@$data['stats'][sizeof($data['stats'])-1]['points'])!=0){
                $performance = getTransferValueBonus(
                                                $data['stats'][sizeof($data['stats'])-1]['performance'],
                                               $data['player']['transfer_value']);
            }
        }
      
        $data['player']['transfer_value'] = round($data['player']['transfer_value'] + $performance);
		$response['player'] = array('info'=>$data['player'],
									 'summary'=>$main_stats_vals,
										'stats'=>$stats);
		
		
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}
	/**
	* sale a player
	*/
	public function sale(){
		$this->loadModel('Team');
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

		$player_id = Sanitize::clean($this->request->data['player_id']);

		$window = $this->Game->transfer_window();

		//check if the transfer window is opened, or the player is just registered within 24 hours
		$is_new_user = false;
		$can_transfer = false;
		
		if(time()<strtotime($this->user['User']['register_date'])+(24*60*60)){
			$is_new_user = true;
		}
		if(!$is_new_user){
			if(strtotime(@$window['tw_open']) <= time() && strtotime(@$window['tw_close'])>=time()){
				$can_transfer = true;
				
			}
		}else{
			$can_transfer = true;
		}
		
		if(strlen($player_id)<2){
			
			$rs = array('status'=>'0','error'=>'no data available');

		}else{

			if($can_transfer){
				$window_id = $window['id'];
				$rs = $this->Game->sale_player($window_id,$game_team['id'],$player_id);

				//reset financial statement
				$this->Session->write('FinancialStatement',null);
				
				
				if(@$rs['status']==1){
					//do nothing
				}else if(@$rs['status']==2){
					$rs = array('status'=>2,'message'=>'No Money');
					
				}else if(@$rs['status']==-1){
					$rs = array('status'=>-1,'message'=>'you cannot sale a player who already bought from the same transfer window');
					
				}else if(isset($rs['error'])){
					$rs = array('status'=>'0','error'=>'Transaction Failed');
				}
			}else{
				$rs = array('status'=>3,'message'=>'Transfer window is closed','open'=>strtotime(@$window['tw_open']),
							'close'=> strtotime(@$window['tw_close']), 'now'=>time());
			}
		}
		
		$this->set('response',$rs);
		$this->render('default');
	}
	public function livestats($game_id){
		$game_id = Sanitize::paranoid($game_id);
		$rs = $this->Game->query("SELECT home_id,away_id,b.name AS home_name,c.name AS away_name 
							FROM ffgame.game_fixtures a
							INNER JOIN ffgame.master_team b
							ON a.home_id = b.uid
							INNER JOIN ffgame.master_team c
							ON a.away_id = c.uid
							WHERE a.game_id='{$game_id}'
							LIMIT 1;");

		$response = $this->Game->livestats($game_id);
		$data = json_decode($response,true);
		$data['fixture']  = array(
			'home'=>$rs[0]['b']['home_name'],
			'away'=>$rs[0]['c']['away_name'],
			'home_id'=>$rs[0]['a']['home_id'],
			'away_id'=>$rs[0]['a']['away_id']
		);
		
		$this->set('response',$data);
		$this->render('default');
	}
	
	public function livegoals($game_id){
		$response = $this->Game->livegoals($game_id);
		$this->set('response',$response);
		$this->set('raw',true);
		$this->render('default');
	}
	public function livematches(){
		//first we have to know our current
		$matchday = 10;
		$response = $this->Game->livematches($matchday);
		$this->set('response',$response);
		$this->set('raw',true);
		$this->render('default');
	}
	/**
	* buy a player
	*/
	public function buy(){
		$this->loadModel('Team');
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

		$player_id = Sanitize::clean($this->request->data['player_id']);

		$window = $this->Game->transfer_window();
		$window_id = intval(@$window['id']);
		
		//check if the transfer window is opened, or the player is just registered within 24 hours
		$is_new_user = false;
		$can_transfer = false;

		if(time()<strtotime($user['User']['register_date'])+(24*60*60)){
			$is_new_user = true;
		}

		if(!$is_new_user){
			if(strtotime(@$window['tw_open']) <= time() && strtotime(@$window['tw_close'])>=time()){
				$can_transfer = true;
				
			}
		}else{
			$can_transfer = true;
		}

		if(strlen($player_id)<2){
			
			$rs = array('status'=>'0','error'=>'no data available');

		}else{
			if($can_transfer){
				$rs = $this->Game->buy_player($window_id,$game_team['id'],$player_id);
			
				//reset financial statement
				$this->Session->write('FinancialStatement',null);
				

				if(@$rs['status']==1){
					$msg = "@p1_".$user['User']['id']." telah membeli {$rs['data']['name']} seharga SS$".number_format($rs['data']['transfer_value']);
					
				}else if(@$rs['status']==2){
					$rs = array('status'=>2,'message'=>'No Money');
					
				}else if(@$rs['status']==-1){
					$rs = array('status'=>-1,'message'=>'you cannot buy a player who already sold from the same transfer window');
					
				}else if(isset($rs['error'])){
					$rs = array('status'=>'0','error'=>'Transaction Failed');
				}
			}else{
				$rs = array('status'=>3,'message'=>'Transfer window is closed');
			}
		}

		

		$this->set('response',$rs);
		$this->render('default');
	}
	public function transfer_status(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$api_session = $this->readAccessToken();
		$fb_id = $api_session['fb_id'];
		$user = $this->User->findByFb_id($fb_id);
	

		$window = $this->Game->transfer_window();
		$window_id = intval(@$window['id']);
		
		//check if the transfer window is opened, or the player is just registered within 24 hours
		$is_new_user = false;
		$can_transfer = false;
		
		if(time()<strtotime($user['User']['register_date'])+(24*60*60)){
			$is_new_user = true;
		}

		if(!$is_new_user){
			if(strtotime(@$window['tw_open']) <= time() && strtotime(@$window['tw_close'])>=time()){
				$can_transfer = true;
				
			}
		}else{
			$can_transfer = true;
		}

		
		if($can_transfer){
			$rs = array('status'=>1,'message'=>'Transfer window is open');
		}else{
			$rs = array('status'=>0,'message'=>'Transfer window is closed');
		}

	
		$this->set('response',$rs);
		$this->render('default');
	}


	//online catalog API
	public function catalog(){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');

		if(isset($this->request->query['ctoken'])){
			$catalog_token = unserialize(decrypt_param($this->request->query['ctoken']));	
		}else{
			$catalog_token = '';
		}
		
		$user_fb_id = Sanitize::clean(@$catalog_token['fb_id']);

		$since_id = intval(@$this->request->query['since_id']);

		$start = intval(@$this->request->query['start']);
		$total = intval(@$this->request->query['total']);

		if($total > 10){
			$total = 10;
		}

		$response = array();

		if(isset($this->request->query['cid'])){
			$category_id = intval($this->request->query['cid']);
		}else{
			$category_id = 0;
		}
		
		$merchandise = $this->MerchandiseItem->find('count',array('conditions'=>array('merchandise_type'=>0,'n_status'=>1)));

		if($merchandise > 0){
			$response['has_merchandise'] = true;
		}else{
			$response['has_merchandise'] = false;
		}
		

		//bind the model's association first.
		//i'm too lazy to create a new Model Class :P
		$this->MerchandiseItem->bindModel(array(
			'belongsTo'=>array('MerchandiseCategory')
		));

		//we need to populate the category
		$categories = $this->getCatalogMainCategories();
		$response['main_categories'] = $categories;

		$total_rows = 0;
		//if category is set, we filter the query by category_id
		if($category_id != 0 && 
			intval($category_id) != intval(Configure::read('DIGITAL_ITEM_CATEGORY'))){
			$category_ids = array($category_id);
			//check for child ids, and add it into category_ids
			$category_ids = $this->getChildCategoryIds($category_id,$category_ids);
			$options = array('conditions'=>array(
									'merchandise_category_id'=>$category_ids,
									'MerchandiseItem.parent_id'=>0,
									'merchandise_type'=>0,'n_status'=>1),
									'offset'=>$start,
									'limit'=>$total,
									'order'=>array('MerchandiseItem.id'=>'DESC')
									);



			//maybe the category has children in it.
			//so we try to populate it
			$child_categories = $this->getChildCategories($category_id);
			$response['child_categories'] = $child_categories;

			//we need to know the category details
			$category = $this->MerchandiseCategory->findById($category_id);
			$response['current_category'] = $category['MerchandiseCategory'];
			

		}else{
			//if doesnt, we query everything.
			$options = array(
						'conditions'=>array('merchandise_type'=>0,'MerchandiseItem.parent_id'=>0,
											'price_money > 0','n_status'=>1),
						'offset'=>$start,
						'limit'=>$total,
						'order'=>array('MerchandiseItem.id'=>'DESC')
						);
		}


		

		//retrieve the results.
		$rs = $this->MerchandiseItem->find('all',$options);

		//retrieve the total rows
		unset($options['limit']);
		unset($options['offset']);
		$total_rows = $this->MerchandiseItem->find('count',$options);
		
		//check the stock for each items
		for($i=0;$i<sizeof($rs);$i++){
			//get the available stock
			
			
			$rs[$i]['MerchandiseItem']['available'] = $rs[$i]['MerchandiseItem']['stock'];

			//prepare the picture url
			$pic = Configure::read('avatar_web_url').
										"merchandise/thumbs/0_".
										$rs[$i]['MerchandiseItem']['pic'];
			$rs[$i]['MerchandiseItem']['picture'] = $pic;

			//check if the item has child_items
			$opts = array('conditions'=>array('parent_id'=>$rs[$i]['MerchandiseItem']['id']));
			$child_items = $this->MerchandiseItem->find('count',$opts);
			
			if($child_items > 0){
				$rs[$i]['has_child'] = 1;
			}else{
				$rs[$i]['has_child'] = 0;
			}

		}
		//assign it.
		
		$response['items'] = $rs;

		//setup new offset pointers
		if(sizeof($rs) > 0){
			$next_offset = $start + $total;
		}else{
			$next_offset = $start;
		}
		
		$previous_offset = $start - $total;
		if($previous_offset < 0){
			$previous_offset = 0;
		}
		//-->


		//and here's the JSON output
		$this->layout="ajax";
		$this->set('response',array('status'=>1,
									'data'=>$response,
									'offset'=>$start,
									'limit'=>$total,
									'next_offset'=>$next_offset,
									'previous_offset'=>$previous_offset,
									'total_rows'=>$total_rows));


		$this->render('default');
	}
	///api for displaying the catalog's item
	public function catalog_item($item_id){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');


		//we need to populate the category
		$categories = $this->getCatalogMainCategories();
		$response['main_categories'] = $categories;

		
		//parno mode.
		$item_id = Sanitize::clean($item_id);

		//get the item detail
		$item = $this->MerchandiseItem->findById($item_id);
		
		
			
		$item['MerchandiseItem']['available'] = $item['MerchandiseItem']['stock'];

		//prepare the picture url
		$pic = Configure::read('avatar_web_url').
									"merchandise/thumbs/0_".
									$item['MerchandiseItem']['pic'];
		$item['MerchandiseItem']['picture'] = $pic;

		$response['item'] = $item['MerchandiseItem'];
		

		$category = $this->MerchandiseCategory->findById($item['MerchandiseItem']['merchandise_category_id']);
		$response['current_category'] = $category['MerchandiseCategory'];

		$child_opts = array('conditions'=>array('parent_id'=>$item['MerchandiseItem']['id'],
												'n_status'=>1),
							'limit'=>100,
							'order'=>'MerchandiseItem.id');

		$response['children'] = $this->MerchandiseItem->find('all',$child_opts);
		$response['parent'] = $this->MerchandiseItem->findById($item['MerchandiseItem']['parent_id']);
		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$response));
		$this->render('default');
	}
	/*
	* ecash url untuk pembayaran ongkir
	*/
	public function payment_url($order_id){
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');
		$ongkir = $this->Ongkir->find('all',array('limit'=>10000));
		$rs = $this->MerchandiseOrder->findById($order_id);
		
		
		foreach($ongkir as $ok){
			if($ok['Ongkir']['id'] == $rs['MerchandiseOrder']['ongkir_id']){
				$city = $ok['Ongkir'];
			}
		}
	
		$kg = 0;
		for($i=0;$i<sizeof($items);$i++){
			$kg = intval($items[$i]['qty']) * ceil(floatval(@$items[$i]['data']['MerchandiseItem']['weight']));
		}

		$total_ongkir = $kg * $city['cost'];
		//add suffix -1 to define that its the payment for shipping for these po number.
		$transaction_id =  $rs['MerchandiseOrder']['po_number'].'-1';

		//ecash url
		$rs = $this->Game->getEcashUrl(array(
			'transaction_id'=>$transaction_id,
			'amount'=>$total_ongkir,
			'clientIpAddress'=>$this->request->clientIp(),
			'description'=>'Shipping Fee #'.$transaction_id,
			'source'=>'SSPAY'
		));

		$this->set('transaction_id',$transaction_id);
		$this->set('ecash_url',$rs['data']);

		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$rs['data'],'transaction_id'=>$transaction_id));
		$this->render('default');

	}

	/*
	* API untuk mendapatkan url pembayaran ecash.
	*/
	public function ecash_url($game_team_id){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');

		CakeLog::write('debug',json_encode($this->request->data));

		$shopping_cart = unserialize(decrypt_param($this->request->data['param']));
		$transaction_id = intval(@$game_team_id).'-'.date("YmdHis").'-'.rand(0,99);
		$description = 'Purchase Order #'.$transaction_id;
		$fb_id = $this->request->data['fb_id'];

		//get total money to be spent.
		$total_price = 0;
		$all_digital = true;
		$kg = 0;
		$category = array();
		for($i=0;$i<sizeof($shopping_cart);$i++){

			$shopping_cart[$i]['data'] = $this->MerchandiseItem->findById($shopping_cart[$i]['item_id']);
			$item = $shopping_cart[$i]['data']['MerchandiseItem'];
			$kg += ceil(floatval($item['weight'])) * intval($shopping_cart[$i]['qty']);
			$total_price += (intval($shopping_cart[$i]['qty']) * intval($item['price_money']));
			CakeLog::write('debug', "aaaaaaaaaa".json_encode($item));
			$category[$i] = $item['merchandise_category_id'];
			//is there any non-digital item ?
			if($item['merchandise_type']==0){
				$all_digital = false;
			}
		}


		//book the item stocks
		$this->book_items($shopping_cart,$transaction_id,$game_team_id,$fb_id);
		
		$admin_fee = Configure::read('PO_ADMIN_FEE');
		$enable_ongkir = true;
		if(count($shopping_cart) > 1)
		{
			$admin_fee = Configure::read('PO_ADMIN_FEE');
		}
		else
		{
			//check enable or disable admin fee
			$rs_adminfee = $shopping_cart[0]['data']['MerchandiseItem'];
			if($rs_adminfee['enable_admin_fee'] != 1)
			{
				$admin_fee = 0;
			}
			else
			{
				//check value of admin_fee
				if($rs_adminfee['admin_fee'] == 0)
				{
					$admin_fee = Configure::read('PO_ADMIN_FEE');
				}
				else
				{
					$admin_fee = $rs_adminfee['admin_fee'];
				}
			}

			//check ongkir
			if($rs_adminfee['enable_ongkir'] == 0)
			{
				$enable_ongkir = false;
			}
		}

		$contain_tiket = $this->check_category_ticket($category);

		if($contain_tiket){
			$admin_fee = 5000;
			$enable_ongkir = false;
		}

		if($all_digital){
			$admin_fee = 0;
		}
		$total_price += $admin_fee;

		//include ongkir
		$total_ongkir = 0;
		if($enable_ongkir)
		{
			$ongkirList = $this->getOngkirList();
			foreach($ongkirList as $ongkir){
				if($ongkir['Ongkir']['id'] == intval($this->request->data['city_id'])){
					$total_ongkir = intval($ongkir['Ongkir']['cost']);
					break;
				}
			}
		}
		
		$total_price += ($kg*$total_ongkir);

		$transaction_data = array('profile'=>$this->request->data,
								 'shopping_cart'=>$shopping_cart,
								 'base_ongkir_value'=>$total_ongkir);
		

		$rs = $this->Game->getEcashUrl(array(
			'transaction_id'=>$transaction_id,
			'description'=>$description,
			'amount'=>$total_price,
			'clientIpAddress'=>$this->request->clientIp(),
			'source'=>'fm'
		));
		if($rs['data']!='#'){
			$this->Game->storeToTmp(intval(@$game_team_id),$transaction_id,encrypt_param(serialize($transaction_data)));
		}
		CakeLog::write('debug',intval(@$game_team_id).'-'.$transaction_id.'-'.json_encode($transaction_data));

		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$rs['data'],'transaction_id'=>$transaction_id));
		$this->render('default');
	}


	/*
	*return true if order contain ticket category
	*/
	private function check_category_ticket($category_id = array())
	{
		$ticket_category_id = Configure::read('ticket_category_id');
		foreach ($category_id as $value) {
			if($value == $ticket_category_id)
			{
				return true;
			}
		}

		return false;
	}

	
	/*
	* book items stock.
	* upon checkout, the stock will be locked for 5 minutes to prevent other people to order
	*/
	private function book_items($items,$order_id,$game_team_id,$fb_id){	
		$this->loadModel('MerchandiseItemPerk');
		
		for($i=0; $i < sizeof($items); $i++){
			
			$qty = $items[$i]['qty'];
			$keyname = 'claim_stock_'.$items[$i]['item_id'].'_'.$game_team_id.'_'.$fb_id;
			$ttl = 15*60; //user have 15 minutes to complete the payment.
			$this->Game->storeToTmp($game_team_id,
									$keyname,
									$qty,
									$ttl);

			$this->Game->storeToTmp($game_team_id,
									'purchase_order_'.$order_id,
									'1',
									$ttl);

			CakeLog::write('api','lock item - '.$order_id.' - '.$items[$i]['item_id'].' - qty : '.$qty.' key:'.$keyname);
		}
	}

	/**get ongkir list **/
	private function getOngkirList(){
		$this->loadModel('Ongkir');
		$ongkir = $this->Ongkir->find('all',array('limit'=>10000));
		return $ongkir;
	}
	/*
	* validate ecash id
	*/
	public function ecash_validate(){
		$id = $this->request->query['id'];

		$rs = $this->Game->EcashValidate($id);
		CakeLog::write('debug','ecash_validate - '.$id.' - '.json_encode($rs));

		list($id,$trace_number,$nohp,$transaction_id,$status) = explode(',',$rs['data']);

		$result = array(
			'id'=>trim($id),
			'trace_number'=>trim($trace_number),
			'nohp'=>trim($nohp),
			'transaction_id'=>trim($transaction_id),
			'status'=>trim($status)
		);
		
		$is_valid = true;
		
		if(Configure::read('debug')==0){
			/*
			todo - besok dinyalain

			$ecash_validate = $this->Session->read('ecash_return');
			if($result['id']==$ecash_validate['id'] &&
				$result['trace_number']==$ecash_validate['trace_number'] &&
				$result['nohp']==$ecash_validate['nohp'] &&
				$result['transaction_id']==$ecash_validate['transaction_id'] &&
				$result['status']==$ecash_validate['status']
				){
				$is_valid = true;
			}else{
				
				$is_valid = false;
			}*/
		}
		CakeLog::write('debug','ecash_validate - '.$id.' - '.json_encode($result));
		CakeLog::write('debug','ecash_validate - is valid : '.$id.' - '.json_encode($is_valid));
		CakeLog::write('debug',strtoupper(trim($result['status'])).' <-> SUCCESS');
		$this->layout="ajax";
		if(strtoupper(trim($result['status']))=='SUCCESS' && $is_valid){
			CakeLog::write('debug','ecash_validate - '.$id.' - '.'success');
			$status = "SUCCESS";
			$this->set('response',array('status'=>1,'data'=>$result));
		}else{
			CakeLog::write('debug','ecash_validate - '.$id.' - '.'failed');
			$status = "FAILED";
			$this->set('response',array('status'=>0,'data'=>$result));
		}
		
		
		$this->render('default');
	}
	public function catalog_save_order($game_team_id){
		

		$rs = $this->pay_with_ecash_completed($game_team_id,$this->request->data);

		CakeLog::write('debug','pay_with_ecash_completed - finished '.json_encode($rs));
		$this->layout="ajax";
		if($rs){
			$last_order = $this->Game->getFromTmp(intval(@$game_team_id),
										$this->request->data['transaction_id'].'_order_id');
			$order_id = $last_order['data'];
			$this->set('response',array('status'=>1,'order_id'=>$order_id));
			CakeLog::write('debug','pay_with_ecash_completed - status : 1, order_id:'.$order_id);

		}else{
			$this->set('response',array('status'=>0,'error'=>@$rs['error']));
			CakeLog::write('debug','pay_with_ecash_completed - status : 0');
		}
		
		$this->render('default');
	}
	private function pay_with_ecash_completed($game_team_id,$ecash_data){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');
		

		$this->userData['team']['id'] = intval($game_team_id);
		

		$transaction_id = $ecash_data['transaction_id'];

		//get data from redis store
		$rs = $this->Game->getFromTmp(intval(@$game_team_id),$transaction_id);
		$transaction_tmp = unserialize(decrypt_param($rs['data']));


		CakeLog::write('debug','pay_with_ecash_completed'.'-'.json_encode($transaction_tmp));
		$shopping_cart = unserialize(decrypt_param($transaction_tmp['profile']['param']));
		
		CakeLog::write('debug','pay_with_ecash_completed - shopping_cart : '.json_encode($shopping_cart));
		
		$total_price = 0;
		
		$all_digital = true;
		
		$is_ticket = false;

		$kg = 0;
		for($i=0;$i<sizeof($shopping_cart);$i++){
			if($shopping_cart[$i]['item_id'] > 0){
				$shopping_cart[$i]['data'] = $this->MerchandiseItem->findById($shopping_cart[$i]['item_id']);
				$item = $shopping_cart[$i]['data']['MerchandiseItem'];
				//check parent, if parent exists, then we concat the parent's name into item's name.
				$parent_item = $this->MerchandiseItem->findById(intval($item['parent_id']));
				if(isset($parent_item['MerchandiseItem'])){
					$shopping_cart[$i]['data']['MerchandiseItem']['name'] = $parent_item['MerchandiseItem']['name'].' '.
																		$item['name'];	
				}
				if($item['merchandise_category_id'] == Configure::read('ticket_category_id')){
					$is_ticket = true;
				}
				$kg += intval($shopping_cart[$i]['qty']) * ceil(floatval($item['weight']));
				$total_price += (intval($shopping_cart[$i]['qty']) * intval($item['price_money']));
				//is there any non-digital item ?
				if($item['merchandise_type']==0){
					$all_digital = false;
				}
			}
		}

		$admin_fee = Configure::read('PO_ADMIN_FEE');
		$enable_ongkir = true;
		if(count($shopping_cart) > 1)
		{
			$admin_fee = Configure::read('PO_ADMIN_FEE');
		}
		else
		{
			//check enable or disable admin fee
			$rs_adminfee = $shopping_cart[0]['data']['MerchandiseItem'];
			if($rs_adminfee['enable_admin_fee'] != 1)
			{
				$admin_fee = 0;
			}
			else
			{
				//check value of admin_fee
				if($rs_adminfee['admin_fee'] == 0)
				{
					$admin_fee = Configure::read('PO_ADMIN_FEE');
				}
				else
				{
					$admin_fee = $rs_adminfee['admin_fee'];
				}
			}

			//check ongkir
			if($rs_adminfee['enable_ongkir'] == 0)
			{
				$enable_ongkir = false;
			}
		}
		
		if($all_digital){
			$admin_fee = 0;
		}
		$total_price += $admin_fee;

		


		//$data = unserialize(decrypt_param($ecash_data['profile']));
		$data = $transaction_tmp['profile'];
		CakeLog::write('debug','pay_with_ecash_completed - profile : '.json_encode($data));
		
		$total_ongkir = 0;

		if($enable_ongkir)
		{
			//calculate ongkir
			$ongkirList = $this->getOngkirList();
			foreach($ongkirList as $ongkir){
				if($ongkir['Ongkir']['id'] == intval($data['city_id'])){
					$total_ongkir = intval($ongkir['Ongkir']['cost']);
					break;
				}
			}
		}

		$total_ongkir = $total_ongkir * $kg;

		$total_price += $total_ongkir;



		$data['merchandise_item_id'] = 0;
		$data['user_id'] = 0;
		$data['order_type'] = 1;
		$data['game_team_id'] = intval($game_team_id);
		if($all_digital){
			$data['n_status'] = 3;	
		}else{
			$data['n_status'] = 1;
		}

		$data['order_date'] = date("Y-m-d H:i:s");
		$data['data'] = serialize($shopping_cart);
		$data['po_number'] = $ecash_data['transaction_id'];
		$data['total_sale'] = intval($total_price);
		$data['payment_method'] = 'ecash';
		$data['trace_code'] = $ecash_data['trace_number'];
		$data['ongkir_id'] = intval(@$data['city_id']);
		//we need ongkir value
		$ok = $this->Ongkir->findById(intval(@$data['city_id']));
		$data['ongkir_value'] = $total_ongkir;
		

		CakeLog::write('debug','TO BE SAVED : '.json_encode($data));

		$this->MerchandiseOrder->create();
		try{
			$rs = $this->MerchandiseOrder->save($data);	
			if($this->MerchandiseOrder->getInsertID() > 0){
				$order_id = $this->MerchandiseOrder->getInsertID();
				$this->Game->storeToTmp(intval(@$game_team_id),
										$transaction_id.'_order_id',
										$order_id,
										10*60);
				if($is_ticket){
					//send ticket email
					$this->sendEmailTicket($data['email'],$data['po_number'],$order_id);
					//generate ticket voucher
					$this->generateVouchers($order_id,$data,$shopping_cart);

				}
				$this->process_items($shopping_cart,$data['po_number']);	
			}
			
		}catch(Exception $e){
			//$rs = array('MerchandiseOrder'=>$data);
			$rs['error'] = $e->getMessage();
		}
		
		
		CakeLog::write('debug','INPUT : '.json_encode(@$rs));

		
		
		//di production di nyalain ya.
		$this->Game->storeToTmp(intval(@$game_team_id),$transaction_id,'');


		if(isset($rs['MerchandiseOrder'])){
			$this->Game->storeToTmp(intval(@$game_team_id),'final_'.$transaction_id,'SUCCESS');
			return true;
		}

		$final_transaction = $this->Game->getFromTmp(intval(@$game_team_id),'final_'.$transaction_id);
		

		if($final_transaction['data']=='SUCCESS'){
			CakeLog::write('debug','final_'.$transaction_id.'[SUCCESS]:'.json_encode($final_transaction));
			return true;
		}else{
			CakeLog::write('debug','final_'.$transaction_id.'[FAILED]:'.json_encode($final_transaction));
			$this->Game->storeToTmp(intval(@$game_team_id),'final_'.$transaction_id,'FAILED');	
			return false;
		}
	}
	/*
	* generate ticket voucher, and saves it in fantasy.merchandise_vouchers
	*/
	private function generateVouchers($order_id,$order_data,$shopping_cart){
		
		CakeLog::write('generateVoucher',' shopping_cart - >'.json_encode($shopping_cart));
		CakeLog::write('generateVoucher',' order_data - >'.json_encode($order_data));


		$no = 1;
		for($i=0;$i<sizeof($shopping_cart);$i++){
			$item = $shopping_cart[$i]['data']['MerchandiseItem'];
			$qty = $shopping_cart[$i]['qty'];
			if(Configure::read('ticket_category_id') == $item['merchandise_category_id']){
				for($j=0;$j < $qty;$j++){
					$voucher_code = $order_data['po_number'].$no;
					CakeLog::write('generateVoucher',date("Y-m-d H:i:s")." - ".$order_data['po_number'].' - '.$voucher_code);
					
					$sql = "
					INSERT IGNORE INTO fantasy.merchandise_vouchers
					(merchandise_order_id,merchandise_item_id,voucher_code,created_dt,n_status)
					VALUES
					({$order_id},
					 {$item['id']},
					 '{$voucher_code}',
					 NOW(),
					 0)";
					CakeLog::write('generateVoucher',' - >'.$sql);
					$rs = $this->Game->query($sql);
					
					$no++;

					
				}
			}	
		}
	}
	//test function for generate a voucher
	private function test_generate_voucher(){
		$rs = $this->Game->query("SELECT * FROM fantasy.merchandise_orders a WHERE id=388 LIMIT 1");
		$data = $rs[0]['a'];
		$shopping_cart = unserialize($data['data']);
		
		$this->generateVouchers($data['id'],$data,$shopping_cart);
		$this->layout="ajax";
		$this->set('response',array('status'=>1));
		$this->render('default');
	}

	private function sendEmailTicket($email,$po_number,$order_id){
		$url = "http://www.supersoccer.co.id/onlinecatalog/view_order/".intval($order_id);
		$view = new View($this, false);
		$body = $view->element('email_ticket',array('subject'=>'Transaksi Berhasil !',
													'voucher_url'=>$url,
													'po_number'=>$po_number));
		
		$body = mysql_escape_string($body);
		$rs = $this->Game->query("INSERT IGNORE INTO ffgame.email_queue
							(subject,email,plain_txt,html_text,queue_dt,n_status)
							VALUES
							('transaksi berhasil !','{$email}','{$body}','{$body}',NOW(),0) ;");

	}

	public function view_voucher($id){
		$id = intval($id);
		$this->Game->query("UPDATE fantasy.merchandise_vouchers 
							SET n_status = 1 WHERE id = {$id}");
		$this->layout="ajax";
		$this->set('response',array('status'=>1));
		$this->render('default');
	}
	/*api call for purchasing item using coins
	$game_team_id -> user's game_team_id
	we can require the game_team_id after calling get_fm_profile api.
	
	$param -> encrypted serialized array of :
	$items[0]['item_id']
	$items[0]['qty']
	$items[1]['item_id']
	$items[1]['qty']
	*/
	public function catalog_purchase($game_team_id){
		
		$param = unserialize(decrypt_param($this->request->data['param']));

		$result = $this->pay_with_coins($game_team_id,$param);
		CakeLog::write('debug',$game_team_id.'-team_id');
		CakeLog::write('debug',$game_team_id,'data ->'.json_encode($this->request->data));
		CakeLog::write('debug','catalog - '.json_encode($result));
		$is_transaction_ok = $result['is_transaction_ok'];
		$no_fund = @$result['no_fund'];
		$order_id = @$result['order_id'];
		
		if($is_transaction_ok == true){
			//check accross the items, we apply the perk for all digital items
			$this->process_items($result['items'],$order_id);
		}

		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$result));
		$this->render('default');
	}
	/*
	* process digital items
	* when the digital items redeemed, we reduce its stock.
	*/
	private function process_items($items,$order_id){	
		CakeLog::write('debug',json_encode($items));
		$this->loadModel('MerchandiseItemPerk');
		for($i=0; $i<sizeof($items); $i++){
			$item = $items[$i]['data']['MerchandiseItem'];
			$qty = $items[$i]['qty'];
			CakeLog::write('apply_digital_perk',json_encode($this->userData));
			if($item['merchandise_type']==1){
				$this->apply_digital_perk($this->userData['team']['id'],
											$item['perk_id'],$order_id);

			}else if($item['perk_id'] == 0){
				$perks = $this->MerchandiseItemPerk->find('all',
													array('conditions'=>array('merchandise_item_id'=>$item['id']),
														 'limit'=>20)
													);
				CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'-'.$item['id'].'-'.json_encode($perks));
				for($j=0;$j<sizeof($perks);$j++){
					CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'#'.$j.' -'.$item['id'].'-'.$perks[$j]['MerchandiseItemPerk']['perk_id'].'- applying');
					$this->apply_digital_perk($this->userData['team']['id'],
											$perks[$j]['MerchandiseItemPerk']['perk_id'],$order_id);
				}
			
			}
			
			$this->reduceStock($item['id'],$qty);
			CakeLog::write('stock','process_items - '.$order_id.' - '.$item['id'].' - '.$qty.' REDUCED');
		}
		
		
	}
	


	private function ReduceStock($item_id,$qty=1){
		$item_id = intval($item_id);
		$sql = "UPDATE merchandise_items SET stock = stock - {$qty} WHERE id = {$item_id}  AND n_status = 1";
		$this->MerchandiseItem->query($sql);

		$sql = "UPDATE merchandise_items SET stock = 0 WHERE id = {$item_id} AND stock < 0";
		$this->MerchandiseItem->query($sql);
		CakeLog::write('api_stock','stock '.$item_id." {$qty} reduced");
		
	}

	private function pay_with_coins($game_team_id,$shopping_cart){

		CakeLog::write('debug',$game_team_id.' - pay with coins');
		$game_team_id = intval($game_team_id);


		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');

		$result = array('is_transaction_ok'=>false);

		//get total coins to be spent.
		$total_coins = 0;
		$all_digital = true;
		$kg = 0;
		$all_stock_ok = true;

		for($i=0;$i<sizeof($shopping_cart);$i++){
			if(intval($shopping_cart[$i]['qty']) <= 0){
				$shopping_cart[$i]['qty'] = 1;
			}
			$shopping_cart[$i]['data'] = $this->MerchandiseItem->findById($shopping_cart[$i]['item_id']);
			$item = $shopping_cart[$i]['data']['MerchandiseItem'];
			$kg += ceil(floatval($item['weight'])) * intval($shopping_cart[$i]['qty']);
			$total_coins += (intval($shopping_cart[$i]['qty']) * intval($item['price_credit']));
			//is there any non-digital item ?
			if($item['merchandise_type']==0){
				$all_digital = false;
			}
			if($item['stock'] <= 0){
				$all_stock_ok = false;
			}
		}
		$cash = $this->Game->getCash($game_team_id);
		CakeLog::write('debug',$game_team_id.' cash : '.$cash);
		CakeLog::write('debug',$game_team_id.' total coins : '.$total_coins);

		//1. check if the coins are sufficient
		if(intval($cash) >= $total_coins){
			$no_fund = false;
		}else{
			$no_fund = true;
		}
		
		//2. if fund is available, we create transaction id and order detail.
		if(!$no_fund && $all_stock_ok){

			$data = $this->request->data;
			$data['merchandise_item_id'] = 0;
			$data['game_team_id'] = $game_team_id;
			$data['user_id'] = 0;
			$data['order_type'] = 1;

			if($all_digital){
				$data['n_status'] = 3;	
			}else{
				$data['n_status'] = 0;
			}
			$data['order_date'] = date("Y-m-d H:i:s");
			$data['data'] = serialize($shopping_cart);
			$data['po_number'] = $game_team_id.'-'.date("ymdhis");
			$data['total_sale'] = intval($total_coins);
			$data['payment_method'] = 'coins';
			$data['ongkir_id'] = $this->request->data['city_id'];
			//we need ongkir value
			$ok = $this->Ongkir->findById($data['ongkir_id']);

			$data['ongkir_value'] = $kg * intval($ok['Ongkir']['cost']);

			$this->MerchandiseOrder->create();
			$rs = $this->MerchandiseOrder->save($data);	
			if($rs){
				$result['order_id'] = $this->MerchandiseOrder->id;
				//time to deduct the money
				$this->Game->query("
				INSERT IGNORE INTO ffgame.game_transactions
				(game_team_id,transaction_name,transaction_dt,amount,
				 details)
				VALUES
				({$game_team_id},'purchase_{$data['po_number']}',
					NOW(),
					-{$total_coins},
					'{$data['po_number']} - {$result['order_id']}');");
				
				//update cash summary
				$this->Game->query("INSERT INTO ffgame.game_team_cash
				(game_team_id,cash)
				SELECT game_team_id,SUM(amount) AS cash 
				FROM ffgame.game_transactions
				WHERE game_team_id = {$game_team_id}
				GROUP BY game_team_id
				ON DUPLICATE KEY UPDATE
				cash = VALUES(cash);");

				//flag transaction as ok
				$is_transaction_ok = true;
				$result['is_transaction_ok'] = $is_transaction_ok;
				$result['items'] = $shopping_cart;
			}
		}

		$result['no_fund'] = $no_fund;
		return $result;
	}

	
	private function apply_digital_perk($game_team_id,$perk_id,$unique=false){
		$this->loadModel('MasterPerk');

		$perk = $this->MasterPerk->findById($perk_id);
		$perk['MasterPerk']['data'] = unserialize($perk['MasterPerk']['data']);
		switch($perk['MasterPerk']['data']['type']){
			case "jersey":
				CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'-'.$game_team_id.'-'.$perk_id.'- apply jersey');
				return $this->apply_jersey_perk($game_team_id,$perk['MasterPerk']);
			break;
			case "free_player":
				$player_id = $perk['MasterPerk']['data']['player_id'];
				$amount = intval($perk['MasterPerk']['data']['money_reward']);
				CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'-'.$game_team_id.'-'.$perk_id.'- apply free player');
				return $this->apply_free_player_perk($game_team_id,$player_id,$unique,$amount);
			break;
			default:
				//check if it's a money perk
				if($perk['MasterPerk']['perk_name']=='IMMEDIATE_MONEY'){
					CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'-'.$game_team_id.'-'.$perk_id.'- apply money');
					$this->apply_money_perk($game_team_id,$unique,$perk['MasterPerk']['amount']);
				}else{
					CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").'-'.$game_team_id.'-'.$perk_id.'- apply etc');
					//for everything else, let the game API handle the task
					$rs = $this->Game->apply_digital_perk($game_team_id,$perk_id);

					CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").' result - '.json_encode($rs));
					if($rs['data']['can_add'] && $rs['data']['success']){
						return true;
					}else if(!$rs['data']['can_add']){
						//tells us that the perk cannot be redeemed because these perk is already redeemed before
						$this->Session->write('apply_digital_perk_error','1');
					}else{
						//tells us that the perk cannot be redeemed because we cannot save the perk.
						$this->Session->write('apply_digital_perk_error','2');
					}
				}
				
			break;
			
		}
		
	}
	/*
	* $unique_id -> can be item_id, or any unique identifier we can use as transaction name.
	*/
	private function apply_money_perk($game_team_id,$unique_id,$amount){
		$transaction_name = 'apply_money_perk_'.$unique_id;
		CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").' - '.$game_team_id.' - apply_money_perk_'.$unique_id.' -> '.$amount);
		$rs = $this->Game->add_expenditure($game_team_id,$transaction_name,intval($amount));
		if($rs['status']==1){
			return true;
		}
	}
	private function apply_free_player_perk($game_team_id,$player_id,$unique_id,$amount){
		//check if the user has the player
		$my_player = $this->Game->query("SELECT * FROM ffgame.game_team_players a
							WHERE game_team_id={$game_team_id} 
							AND player_id='{$player_id}'",false);
		if(@$my_player[0]['a']['player_id'] == $player_id){
			CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").' - '.$game_team_id.' - '.$unique_id.' - player exists, reward money instead : '.$amount);
			//reward money instead
			return $this->apply_money_perk($game_team_id,$unique_id,$amount);
		}else{
			CakeLog::write('apply_digital_perk',date("Y-m-d H:i:s").' - '.$game_team_id.' - '.$unique_id.' - free player : '.$player_id);
			return $this->Game->query("INSERT IGNORE INTO ffgame.game_team_players
								(game_team_id,player_id)
								VALUES({$game_team_id},'{$player_id}')",false);
		}

	}
	private function apply_jersey_perk($game_team_id,$perk_data){
		$this->loadModel('DigitalPerk');
		$this->DigitalPerk->cache = false;


		//only 1 jersey can be used


		//so we disabled all existing jersey
		$this->loadModel('DigitalPerk');
		$this->DigitalPerk->bindModel(
			array('belongsTo'=>array(
				'MasterPerk'=>array(
					'type'=>'inner',
					'foreignKey'=>false,
					'conditions'=>array(
						"MasterPerk.id = DigitalPerk.master_perk_id",
						"MasterPerk.perk_name = 'ACCESSORIES'"
					)
				)
			))
		);
		$current_perks = $this->DigitalPerk->find('all',array(
			'conditions'=>array('game_team_id'=>$game_team_id),
			'limit'=>40
		));
		$has_bought = false;
		$bought_id = 0;
		//we only take the jersey perks
		$jerseys = array();
		while(sizeof($current_perks)>0){
			$p = array_pop($current_perks);
			$p['MasterPerk']['data'] = unserialize($p['MasterPerk']['data']);
			if($p['MasterPerk']['data']['type']=='jersey'){
				$jerseys[] = $p['DigitalPerk']['id'];
			}
			if($p['DigitalPerk']['master_perk_id'] == $perk_data['id']){
				$has_bought = true;
				$bought_id = $p['DigitalPerk']['id'];
			}
		}
		//check if these jersy has been bought before.
		
		//disable the current jerseys
		for($i=0;$i<sizeof($jerseys);$i++){

			$this->DigitalPerk->id = intval($jerseys[$i]);
			$this->DigitalPerk->save(array(
				'n_status'=>0
			));
		}


		//add new jersey
		if(!$has_bought){
			$this->DigitalPerk->create();
			$rs = $this->DigitalPerk->save(
				array('game_team_id'=>$game_team_id,
					  'master_perk_id'=>$perk_data['id'],
					  'n_status'=>1,
					  'redeem_dt'=>date("Y-m-d H:i:s"),
					  'available'=>99999)
			);
			if(isset($rs['DigitalPerk'])){
				return true;
			}
		}else{
			//update the status only
			$this->DigitalPerk->id = intval($bought_id);
			$rs = $this->DigitalPerk->save(array(
				'n_status'=>1
			));
			if($rs){
				return true;
			}
		}
		
	}
	/**
	*	get catalog's main categories
	*/
	private function getCatalogMainCategories(){
		//retrieve main categories
		$categories = $this->MerchandiseCategory->find('all',
						array('conditions'=>array('parent_id'=>0),
							  'limit'=>100)
					);
		for($i=0;$i<sizeof($categories);$i++){
			$categories[$i]['Child'] = $this->getChildCategories($categories[$i]['MerchandiseCategory']['id']);
		}
		return $categories;
	}
	/*
	* 
	*/
	private function getChildCategories($category_id){
		//retrieve main categories
		$categories = $this->MerchandiseCategory->find('all',
														array('conditions'=>
															array('parent_id'=>$category_id),
															      'limit'=>100)
													);
		return $categories;
	}
	/**
	*	get the list of child categories, 1 level under only.
	*/
	private function getChildCategoryIds($category_id,$category_ids){
		$categories = $this->MerchandiseCategory->find('all',
														array('conditions'=>array('parent_id'=>$category_id),
															  'limit'=>100)
													);
		for($i=0;$i<sizeof($categories);$i++){
			$category_ids[] = $categories[$i]['MerchandiseCategory']['id'];
		}

		return $category_ids;
	}
	/*
	* API for showing the order history
	*/
	public function order_history($fb_id){
		$since_id = intval(@$this->request->query['since_id']);
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');

		$rs = $this->MerchandiseOrder->find('all',array(
					'conditions'=>array('fb_id'=>$fb_id,'id > '.$since_id),
					'limit'=>20,
					'order'=>array('MerchandiseOrder.id'=>'DESC')
				));
		$result = array();
		$since_id = 0;

		while(sizeof($rs)>0){
			$p = array_shift($rs);
			$since_id = intval($p['MerchandiseOrder']['id']);
			$p['MerchandiseOrder']['data'] = unserialize($p['MerchandiseOrder']['data']);
			$result[] = $p['MerchandiseOrder'];
		}

		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$result,'since_id'=>$since_id));
		$this->render('default');
	}
	public function view_order($order_id){
		$order_id = intval($order_id);
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');
		//attach order detail
		$rs = $this->MerchandiseOrder->findById($order_id);

		$rs['MerchandiseOrder']['data'] = unserialize($rs['MerchandiseOrder']['data']);
			

		$ongkir = $this->Ongkir->find('all');
		foreach($ongkir as $o){
			if($o['Ongkir']['id'] == $rs['MerchandiseOrder']['ongkir_id']){
				$deliverTo = $o['Ongkir'];
			}
		}

		if($rs['MerchandiseOrder']['ongkir_value'] == 0)
		{
			$deliverTo['cost'] = 0;
		}

		//voucher if any

		$sql = "SELECT a.id AS voucher_id,a.voucher_code,c.name,
				b.game_team_id,
				b.po_number,
				a.merchandise_item_id,
				b.email,
				b.first_name,
				b.last_name,
				b.fb_id,
				b.id as order_id,
				b.ktp,
				b.phone,
				b.po_number,
				c.data,
				c.parent_id as item_parent_id
				FROM fantasy.merchandise_vouchers a
				INNER JOIN fantasy.merchandise_orders b
				ON a.merchandise_order_id = b.id 
				INNER JOIN fantasy.merchandise_items c
				ON a.merchandise_item_id = c.id
				WHERE a.merchandise_order_id = {$order_id} LIMIT 100";
		$voucher = $this->Game->query($sql);
		$vouchers = array();
		while(sizeof($voucher) > 0){
			$v = array_shift($voucher);
			$voucher_item = $v['b'];
			$voucher_item['item_name'] = $v['c']['name'];
			$voucher_item['voucher_id'] = $v['a']['voucher_id'];
			$voucher_item['voucher_code'] = $v['a']['voucher_code'];
			$voucher_item['item_data'] = json_decode($v['c']['data']);
			$voucher_item['item_id'] = $v['a']['merchandise_item_id'];
			$voucher_item['item_parent_id'] = intval(@$v['c']['item_parent_id']);
			//check parent, if parent exists, then we concat the parent's name into item's name.
			$parent_item = $this->MerchandiseItem->findById(intval($voucher_item['item_parent_id']));
			if(isset($parent_item['MerchandiseItem'])){
				$voucher_item['item_name'] = $parent_item['MerchandiseItem']['name'].' '.
																	$voucher_item['item_name'];	
			}
			$vouchers[] = $voucher_item;
		}
		$this->layout="ajax";
		$this->set('response',array('status'=>1,
									'data'=>$rs,
									'deliveryTo'=>$deliverTo,
									'vouchers'=>$vouchers));
		$this->render('default');

	}
	/*
	* returns data required for delivery fee payment page with ecash
	*/
	public function ecash_ongkir_payment($order_id){
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');

		//fb_id
		$fb_profile = @unserialize(@decrypt_param(@$this->request->query['req']));
		
		//attach order detail
		$rs = $this->MerchandiseOrder->findById($order_id);
		$this->layout="ajax";


		if(isset($fb_profile) && $rs['MerchandiseOrder']['fb_id'] == $fb_profile['id']){
			$rs['MerchandiseOrder']['data'] = unserialize($rs['MerchandiseOrder']['data']);
				

			$ongkir = $this->Ongkir->find('all');
			foreach($ongkir as $o){
				if($o['Ongkir']['id'] == $rs['MerchandiseOrder']['ongkir_id']){
					$deliverTo = $o['Ongkir'];
				}
			}
			//add suffix -1 to define that its the payment for shipping for these po number.
			$transaction_id =  $rs['MerchandiseOrder']['po_number'].'-1';
			
			
			//ecash url
			$ecash_url = $this->Game->getEcashUrl(array(
				'transaction_id'=>$transaction_id,
				'amount'=>$rs['MerchandiseOrder']['ongkir_value'],
				'clientIpAddress'=>$this->request->clientIp(),
				'description'=>'Shipping Fee #'.$transaction_id,
				'source'=>'SSPAY'
			));
			
			$this->set('response',array('status'=>1,
						'data'=>$rs['MerchandiseOrder'],
						'ecash_url'=>$ecash_url['data'],
						'transaction_id'=>$transaction_id,'deliveryTo'=>$deliverTo));
		}else{
			$this->set('response',array('status'=>0));
		}
		
		$this->render('default');
	}
	public function ecash_ongkir_payment_complete(){
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');

		
		$id = $this->request->query['returnId'];
		CakeLog::write('debug','ecash_ongkir_payment_complete'.$id);
		//this is the secret data we sent, 
		//an object that consist the transaction_id and it's related order_id
		$ecash_data = unserialize(decrypt_param($this->request->query['ecash_data']));

		//these the data sent-back by ecash after user complete the payment by entering OTP.
		//$sendData = unserialize(decrypt_param($this->request->query['sendData']));

		//now validate the ecash returnId
		$rs  = $this->Game->EcashValidate($id);
		
		//CakeLog::write('debug','ecash_ongkir_payment_complete'.@$id.' - '.@$rs);
		list($id,$trace_number,$nohp,$transaction_id,$status) = explode(',',$rs['data']);

		//di comment dulu, gak working kayak gini, sessionnya beda soalnya
		//if(Configure::read('debug')!=0){
		$sendData = array('id'=>trim($id),
								'trace_number'=>trim($trace_number),
								'nohp'=>trim($nohp),
								'transaction_id'=>trim($transaction_id),
								'status'=>trim($status));
		//}
		
		if($transaction_id==$ecash_data['transaction_id']
					&& strtoupper(trim($status)) =='SUCCESS'){
			CakeLog::write('debug','ecash_ongkir_payment_complete'.$id.' - SUCCESS');
			//transaction complete, we update the order status
			$data['n_status'] = 1;
			$this->MerchandiseOrder->id = intval($ecash_data['order_id']);
			$updateResult = $this->MerchandiseOrder->save($data);
			if(isset($updateResult)){
				CakeLog::write('debug','ecash_ongkir_payment_complete'.$id.' - DBSUCCESS');
				$response_status = 1;
			}else{
				CakeLog::write('debug','ecash_ongkir_payment_complete'.$id.' - DBERROR');
				$response_status = 0;
			}
		}else{
			CakeLog::write('debug','ecash_ongkir_payment_complete'.$id.' - FAILED');
			//transaction incomplete , return error
			$response_status = 0;
		}
		$this->layout="ajax";

		CakeLog::write('debug','ecash_ongkir_payment_complete'.$id.' - '.$response_status.' - '.
						json_encode(array('status'=>$response_status,'data'=>$sendData)));

		$this->set('response',array('status'=>$response_status,'data'=>$sendData));
		$this->render('default');
	}
	public function get_ongkir(){
		$this->loadModel('Ongkir');
		$rs = $this->Ongkir->find('all',array('limit'=>10000,'order'=>array('Ongkir.kecamatan')));
		$ongkir = array();
		while(sizeof($rs)>0){
			$p = array_shift($rs);
			$ongkir[] = $p['Ongkir'];
		}	
		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$ongkir));
		$this->render('default');
	}
	public function get_fm_profile(){
		$data = unserialize(decrypt_param($this->request->query['req']));
		$fb_id = $data['fb_id'];
		$team = array();
		
		$team = $this->Game->getTeam($fb_id);

		if(isset($team['id'])){
			$cash = $this->Game->getCash($team['id']);
		}else{
			$cash = 0;
		}
		
		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>array('team'=>$team,'coins'=>$cash)));
		$this->render('default');
	}
	/*
	public function test_fm_profile(){
		$data['fb_id']='622088280';
		print encrypt_param(serialize($data));
		die();

	}*/

	public function test_shopping_cart(){
		$shopping_cart[] = array('item_id'=>58,'qty'=>1);
		$shopping_cart[] = array('item_id'=>59,'qty'=>1);
		print encrypt_param(serialize($shopping_cart));
		die();

	}
	/*
	*	method for checking the items availability
	*  if the item is available, we flag the item_id with 1, else we flag it with 0
	*/
	public function catalog_checkItems(){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseOrder');
		
		$game_team_id = intval(Sanitize::clean($this->request->query('game_team_id')));
		$fb_id = Sanitize::clean($this->request->query('fb_id'));

		$itemIds = Sanitize::clean($this->request->query('itemId'));
		$arr = explode(',',$itemIds);
		$items = array();

		for($i=0;$i<sizeof($arr);$i++){
			$item = $this->MerchandiseItem->findById(intval($arr[$i]));
			$total_claimed_qty = $this->getClaimedStock($game_team_id,$fb_id,$arr[$i]);

			if((($item['MerchandiseItem']['stock'] - $total_claimed_qty)) > 0){
				$items[intval($arr[$i])] = intval($item['MerchandiseItem']['stock']) - $total_claimed_qty;
			}else{
				
				$items[intval($arr[$i])] = 0;
			}
		}

		$this->layout="ajax";
		$this->set('response',array('status'=>1,'data'=>$items));
		$this->render('default');
	}
	private function getClaimedStock($game_team_id,$fb_id,$item_id){
		$pattern = 'claim_stock_'.$item_id.'_*';
		$claimed = $this->Game->getTmpKeys($game_team_id,
								$pattern);
		
		$total_claimed_qty = 0;
		if($claimed['status']==1){
			for($k=0;$k<sizeof($claimed['data']);$k++){
				//pastikan yg kita cek itu bukan punya si user
				$arr = explode("_",$claimed['data'][$k]);
				$owner_id = $arr[3];
				$owner_fb_id = $arr[4];
				
				if($owner_id !=0){
					if($owner_id != $game_team_id){
						$claimed_qty = $this->Game->getFromTmp($game_team_id,$claimed['data'][$k]);
						$total_claimed_qty += intval($claimed_qty['data']);

					}	
				}else if(strlen($owner_fb_id)>0){
					if($owner_fb_id != $fb_id){
						$claimed_qty = $this->Game->getFromTmp($game_team_id,$claimed['data'][$k]);
						$total_claimed_qty += intval($claimed_qty['data']);	
					}
				}else{
					//do nothing

				}
				
			}
		}
		
		return $total_claimed_qty;
	}
	
	//dummy for selling player
	public function test_buy(){
		$game_team = $this->Game->getTeam($fb_id);

		$player_id = Sanitize::clean($this->request->data['player_id']);
		$call_status = $this->request->data['status'];
		
		if(strlen($player_id)<2){
			
			$rs = array('status'=>'0','error'=>'no data available');

		}else{
			switch($call_status){
				case 1:
					$rs = array('status'=>1,'data'=>array(
									'name'=>'Michael Carrick',
									'transfer_value'=>10111573,
								),
								'message'=>"the player has been successfully bought.");

				break;
				case 2:
					$rs = array('status'=>2,'message'=>'no money');

				break;
				case -1:
					$rs = array('status'=>-1,'message'=>'you cannot buy a player who already bought from the same transfer window');

				break;
				case 3:
					$rs = array('status'=>3,'message'=>'Transfer window is closed');

				break;
				case 0:
					$rs = array('status'=>0,'message'=>'Oops, cannot buy the player.');

				break;
				default:
					$rs = array('status'=>'0','error'=>'no data available');
				break;
			}
			
		}

		

		$this->set('response',$rs);
		$this->render('default');
	}

	//dummy for buying player
	public function test_sale(){
		$game_team = $this->Game->getTeam($fb_id);

		$player_id = Sanitize::clean($this->request->data['player_id']);
		$call_status = $this->request->data['status'];
		
		if(strlen($player_id)<2){
			
			$rs = array('status'=>'0','error'=>'no data available');

		}else{
			switch($call_status){
				case 1:
					$rs = array('status'=>1,'data'=>array(
									'name'=>'Michael Carrick',
									'transfer_value'=>10111573,
								),
								'message'=>"the player has been successfully sold.");

				break;
				
				case -1:
					$rs = array('status'=>-1,'message'=>'you cannot sale a player who already bought from the same transfer window');

				break;
				case 3:
					$rs = array('status'=>3,'message'=>'Transfer window is closed');

				break;
				case 0:
					$rs = array('status'=>0,'message'=>'Oops, cannot sale the player.');

				break;
				default:
					$rs = array('status'=>0,'error'=>'no data available');
				break;
			}
			
		}


		$this->set('response',$rs);
		$this->render('default');
	}

	public function bet_match(){
		$this->layout="ajax";
		
		

		$rs = $this->Game->getMatches();
		$matches = $rs['matches'];
		unset($rs);
		$bet_matches = array();
		$n=0;
		//we display the previous match and upcoming match
		//so we got 20 matches displayed.
		$matchday = 0;
		for($i=0;$i<sizeof($matches);$i++){
			if($matches[$i]['period']!='FullTime'){
				$matchday = $matches[$i]['matchday'];
				break;
			}
		}
		//now retrieve the matches
		for($i=0;$i<sizeof($matches);$i++){
			if($matches[$i]['matchday'] == $matchday || 
					$matches[$i]['matchday'] == ($matchday - 1)){
				$bet_matches[] = array(
				'game_id'=>$matches[$i]['game_id'],
				'home_id'=>$matches[$i]['home_id'],
				'away_id'=>$matches[$i]['away_id'],
				'period'=>$matches[$i]['period'],
				'home_name'=>$matches[$i]['home_name'],
				'away_name'=>$matches[$i]['away_name'],
				'home_logo'=>'http://widgets-images.s3.amazonaws.com/football/team/badges_65/'.
									str_replace('t','',$matches[$i]['home_id']).'.png',
				'away_logo'=>'http://widgets-images.s3.amazonaws.com/football/team/badges_65/'.
									str_replace('t','',$matches[$i]['away_id']).'.png'
				);
			}
			
		}
		$this->set('response',array('status'=>1,'data'=>$bet_matches));
		$this->render('default');
	}
	//below is the list of `tebak-skor` minigame APIs
	public function submit_bet($game_id){

		$game_id = Sanitize::clean($game_id);
		CakeLog::write('debug',date("Y-m-d H:i:s - ").'submit_bet - '.$game_id);

		CakeLog::write('debug',date("Y-m-d H:i:s - ").'submit_bet - '.json_encode($this->request->query['req']));
		
		
		if(isset($this->request->query['req'])){
			$req = unserialize(decrypt_param(@$this->request->query['req']));
			$fb_id = $req['fb_id'];
			$bet_data = $req['data'];
			
			CakeLog::write("debug",json_encode($req));
			/*
			$fb_id = "100001023465395";
			$data = array(
				'SCORE_GUESS'=>array('home'=>1,'away'=>0,'coin'=>50),
				'CORNERS_GUESS'=>array('home'=>1,'away'=>0,'coin'=>10),
				'SHOT_ON_TARGET_GUESS'=>array('home'=>1,'away'=>0,'coin'=>0),
				'CROSSING_GUESS'=>array('home'=>1,'away'=>0,'coin'=>10),
				'INTERCEPTION_GUESS'=>array('home'=>1,'away'=>0,'coin'=>10),
				'YELLOWCARD_GUESS'=>array('home'=>1,'away'=>0,'coin'=>0)
			);
			$req = encrypt_param(serialize(array('fb_id'=>$fb_id,'data'=>$data)));
			$bet_data = $data;
			*/
			$game_team = $this->Game->getTeam($fb_id);
			$game_team_id = $game_team['id'];

			CakeLog::write("debug","1");

			$matches = $this->Game->getMatches();
			$the_match = array();
			CakeLog::write("debug","2");			
			foreach($matches['matches'] as $match){
				if($match['game_id'] == $game_id){
					$the_match = $match;
					break;
				}
				
			}
			CakeLog::write("debug","3");
			unset($matches);
			$coin_ok = false;

			//make sure that the coin is sufficient
			$cash = $this->Game->getCash($game_team_id);
			CakeLog::write("debug",json_encode($cash));

			$total_bets = 0;

			CakeLog::write('debug',json_encode($bet_data));
			foreach($bet_data as $name=>$val){
				$total_bets += intval($val['coin']);
			}

			CakeLog::write("debug",json_encode($cash));

			CakeLog::write('debug',date("Y-m-d H:i:s").
									' - submit_bet - '.
									$game_id.' - fb_id:'.$fb_id.' - game_team_id : '.
									$game_team_id.' - cash : '.$cash.' - bet : '.$total_bets. 
									' - '.json_encode($bet_data));
			if($total_bets <= 100 
				&& $total_bets < intval($cash)){
				$coin_ok = true;
			}else{
				CakeLog::write('debug',date("Y-m-d H:i:s").
									' - submit_bet - '.'coin not ok -> total_bets : '.$total_bets);
			}

			if($the_match['period']=='PreMatch' && $coin_ok){
				
				
				foreach($bet_data as $name=>$val){
					//all negative coins will be invalid
					if($val['coin']<0){
						$val['coint'] = 0;
					}

					$sql = "INSERT INTO ffgame.game_bets
							(game_id,game_team_id,bet_name,home,away,coins,submit_dt)
							VALUES
							('{$game_id}',
								'{$game_team_id}',
								'{$name}',
								'{$val['home']}',
								'{$val['away']}',
								'{$val['coin']}',
								NOW())
							ON DUPLICATE KEY UPDATE
							home = VALUES(home),
							away = VALUES(away),
							coins = VALUES(coins)";
					$this->Game->query($sql,false);
					CakeLog::write('debug',date("Y-m-d H:i:s - ").
											'submit_bet - '.$game_id.
											' - fb_id:'.$fb_id.' - game_team_id : '.
									$game_team_id.' - cash : '.$cash.' - bet : '.$total_bets. 
									' - '.$sql);

				}
				$transaction_name = 'PLACE_BET_'.$game_id;
				$bet_cost = abs(intval($total_bets)) * -1;
				$sql = "INSERT INTO ffgame.game_transactions
						(game_team_id,transaction_dt,transaction_name,amount,details)
						VALUES
						('{$game_team_id}',NOW(),'{$transaction_name}',{$bet_cost},'deduction')
						ON DUPLICATE KEY UPDATE
						amount = VALUES(amount);";
				$this->Game->query($sql,false);
				CakeLog::write('error',$sql);
				$sql = "INSERT INTO ffgame.game_team_cash
						(game_team_id,cash)
						SELECT game_team_id,SUM(amount) AS cash 
						FROM ffgame.game_transactions
						WHERE game_team_id = {$game_team_id}
						GROUP BY game_team_id
						ON DUPLICATE KEY UPDATE
						cash = VALUES(cash);";
				$this->Game->query($sql,false);


				$this->set('response',array('status'=>1,'game_id'=>$game_id,'fb_id'=>$fb_id));
			}else{
				$this->set('response',array('status'=>0,'game_id'=>$game_id,'fb_id'=>$fb_id));
			}
			
		}else{
			CakeLog::write('debug',date("Y-m-d H:i:s - ").'submit_bet - '.$game_id.' - no request');
			$this->set('response',array('status'=>0,'game_id'=>$game_id,'fb_id'=>0,'error'=>'no request specified'));
		}
		
		$this->layout="ajax";
	
		$this->render('default');
	}

	public function bet_info($game_id){
		$this->layout="ajax";


		
		$fb_id = $this->request->query['fb_id'];

		//get the game_team_id
		$game_team = $this->Game->getTeam($fb_id);
		$game_team_id = $game_team['id'];


		$matches = $this->Game->getMatches();
		$the_match = array();
		
		foreach($matches['matches'] as $match){
			if($match['game_id'] == $game_id){
				$the_match = $match;
				break;
			}
			
		}
		unset($matches);
		

		$the_match['home_logo'] = 'http://widgets-images.s3.amazonaws.com/football/team/badges_65/'.
									str_replace('t','',$the_match['home_id']).'.png';
		$the_match['away_logo'] = 'http://widgets-images.s3.amazonaws.com/football/team/badges_65/'.
									str_replace('t','',$the_match['away_id']).'.png';

		if($the_match['period'] == 'PreMatch'){
			$can_place_bet = true;
		}else{
			$can_place_bet = false;
		}
		//check if the user can place the bet
		$sql = "SELECT * FROM ffgame.game_bets a
				WHERE game_id='{$game_id}' AND game_team_id='{$game_team_id}' LIMIT 10;";

		
		CakeLog::write('debug',date("Y-m-d H:i:s - ").'bet_info - '.$game_id.' - '.$fb_id.' - '.$game_team_id.' - '.$sql);
		$check = $this->Game->query($sql,false);
		CakeLog::write('debug',date("Y-m-d H:i:s - ").'bet_info - '.$game_id.' - '.$fb_id.' - '.$game_team_id.' - '.json_encode($the_match). ' - '.json_encode(@$check[0]['a']));
		
		if(isset($check[0]['a']) 
				&& $check[0]['a']['game_team_id'] == $game_team_id){
			
			$n = 0;
			CakeLog::write('debug',date("Y-m-d H:i:s - ").'bet_info - '.$game_id.' - '.$fb_id.' - '.$game_team_id.' - has place bet');	
		}else if($the_match['period'] != 'PreMatch'){
			$can_place_bet = false;
			$n = 0;
			CakeLog::write('debug',date("Y-m-d H:i:s - ").'bet_info - '.$game_id.' - '.$fb_id.' - '.$game_team_id.' - cannot place bet :(');	
		}else{
			
			$n=1;
			CakeLog::write('debug',date("Y-m-d H:i:s - ").'bet_info - '.$game_id.' - '.$fb_id.' - '.$game_team_id.' - can place bet');	
		}

		$my_bet = array();
		
		$my_bet[] = $this->getBetValue('SCORE_GUESS',$check);
		$my_bet[] = $this->getBetValue('CORNERS_GUESS',$check);
		$my_bet[] = $this->getBetValue('SHOT_ON_TARGET_GUESS',$check);
		$my_bet[] = $this->getBetValue('CROSSING_GUESS',$check);
		$my_bet[] = $this->getBetValue('INTERCEPTION_GUESS',$check);
		$my_bet[] = $this->getBetValue('YELLOWCARD_GUESS',$check);

		if($n==1){
			$items = array(
				array('bet_name'=>'SCORE_GUESS'),
				array('bet_name'=>'CORNERS_GUESS'),
				array('bet_name'=>'SHOT_ON_TARGET_GUESS'),
				array('bet_name'=>'CROSSING_GUESS'),
				array('bet_name'=>'INTERCEPTION_GUESS'),
				array('bet_name'=>'YELLOWCARD_GUESS')
			);

			$this->set('response',array('status'=>1,
								'game_id'=>$game_id,
								'data'=>$items,
								'match'=>$the_match,
								'fb_id'=>$fb_id,
								'can_place_bet'=>$can_place_bet)
			);

		}else{

			$rs = $this->Game->getBetInfo($game_id);
			

			$items = array(
				array('bet_name'=>'SCORE_GUESS',
												'home'=>intval($rs['data']['SCORE_GUESS']['home']),
												'away'=>intval($rs['data']['SCORE_GUESS']['away'])),
				array('bet_name'=>'CORNERS_GUESS',
												'home'=>intval($rs['data']['CORNERS_GUESS']['home']),
												'away'=>intval($rs['data']['CORNERS_GUESS']['away'])),
				array('bet_name'=>'SHOT_ON_TARGET_GUESS',
												'home'=>intval($rs['data']['SHOT_ON_TARGET_GUESS']['home']),
												'away'=>intval($rs['data']['SHOT_ON_TARGET_GUESS']['away'])),
				array('bet_name'=>'CROSSING_GUESS',
												'home'=>intval($rs['data']['CROSSING_GUESS']['home']),
												'away'=>intval($rs['data']['CROSSING_GUESS']['away'])),
				array('bet_name'=>'INTERCEPTION_GUESS',
												'home'=>intval($rs['data']['INTERCEPTION_GUESS']['home']),
												'away'=>intval($rs['data']['INTERCEPTION_GUESS']['away'])),
				array('bet_name'=>'YELLOWCARD_GUESS',
												'home'=>intval($rs['data']['YELLOWCARD_GUESS']['home']),
												'away'=>intval($rs['data']['YELLOWCARD_GUESS']['away'])),


			);
			$winners = array();
			if(isset($rs['data']['winners'])){
				$winners = $rs['data']['winners'];
				
				if(sizeof($winners)>0){
					foreach($winners as $n=>$v){
						$game_user = $this->Game->query("
									SELECT fb_id FROM ffgame.game_users a
									INNER JOIN ffgame.game_teams b
									ON a.id = b.user_id WHERE b.id = {$v['game_team_id']}
									LIMIT 1;");
						$winners[$n]['game_team_id'] = null;
						$winners[$n]['fb_id'] = $game_user[0]['a']['fb_id'];
					}
				}
			}

			//dummy
			/*
			$winners = array(array('fb_id'=>'100000807572975','score'=>100),
							array('fb_id'=>'100000213094071','score'=>90),
							array('fb_id'=>'100001023465395','score'=>80));*/
			//->
			$this->set('response',array('status'=>1,
								'game_id'=>$game_id,
								'data'=>$items,
								'match'=>$the_match,
								'winners'=>$winners,
								'fb_id'=>$fb_id,
								'my_bet'=>$my_bet,
								'can_place_bet'=>$can_place_bet)
			);
		}
		
		$this->render('default');
	}
	private function getBetValue($bet_name,$bets){
		for($i=0;$i<sizeof($bets);$i++){
			if($bets[$i]['a']['bet_name']==$bet_name){
				return $bets[$i]['a'];
			}
		}
	}
	//--> and of `tebak-skor` minigame APIs

	//all the stuffs related to ticket agent / retailers
	private function agent_dummy(){
		$email = 'dev@supersoccer.co.id';
		$password = '12345678';
		$secret = 'qawsedrftg';
		$hash = sha1($email.$password.$secret);
		$this->layout="ajax";
		$this->set('response',array('hash'=>$hash));
		$this->render('default');
	}

	public function agent_login(){
		$this->layout="ajax";
		$email = Sanitize::clean($this->request->data['email']);
		$password = Sanitize::clean($this->request->data['password']);

		if(strlen($email) > 0 && strlen($password) > 0){
			$this->loadModel('Agent');
			$agent = $this->Agent->findByEmail($email);
			$secret = @$agent['Agent']['secret'];
			$hash = sha1($email.$password.$secret);

			if(@$agent['Agent']['password'] == $hash){
				$status = 1;
				$token = encrypt_param(serialize(array(
							'name'=>$agent['Agent']['name'],
							'email'=>$agent['Agent']['email'],
							'agent_id'=>$agent['Agent']['id'],
							'login_dt'=>date("Y-m-d H:i:s"),
							'ts'=>time()
						 )));
				$data = array(
							'name'=>$agent['Agent']['name'],
							'email'=>$agent['Agent']['email'],
							'agent_id'=>$agent['Agent']['id']
						 );
			}else{
				$status = 0;
				$token = '';
				$data = '';
			}
			$this->set('response',array('status'=>$status,'token'=>$token,'data'=>$data));
		}else{
			$this->set('response',array('status'=>0));
		}

		
		$this->render('default');
	}
	/*
	* /api/agent_catalog?agent_id=[n]&token=[s]
	* Response :JSON
	*/
	public function agent_catalog(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->query['agent_id']);
		$token = @$this->request->query['token'];

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//do something
				$catalog = $this->getAgentCatalog($agent_id);
				$this->set('response',array('status'=>1,'data'=>$catalog));
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	/*
	* api for requesting item quota
	* /api/agent_request_quota?agent_id=[n]&item_id=[n]&qty=[n]
	* Response :JSON
	*/
	public function agent_request_quota(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->query['agent_id']);
		$item_id = intval(@$this->request->query['item_id']);
		$qty = intval(@$this->request->query['qty']);

		$token = @$this->request->query['token'];

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//do something
				$rs = $this->setAgentRequestQuota($agent_id,$item_id,$qty);
				$this->set('response',array('status'=>1,'data'=>$rs));
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	/*
	* api for returning item quotas
	* /api/agent_return_quota?agent_id=[n]&item_id=[n]&qty=[n]
	* Response :JSON
	*/
	public function agent_return_quota(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->query['agent_id']);
		$item_id = intval(@$this->request->query['item_id']);
		$qty = intval(@$this->request->query['qty']);

		$token = @$this->request->query['token'];

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//do something
				$rs = $this->setAgentReturnedQuota($agent_id,$item_id,$qty);
				$this->set('response',array('status'=>1,'data'=>$rs));
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	/*
	* API for vieweing the request quota history
	* /api/agent_request_quota?agent_id=[n]&start=0&total=10
	* Response :JSON
	*/
	public function agent_request_history(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->query['agent_id']);
		$start = intval(@$this->request->query['start']);
		$total = intval(@$this->request->query['total']);
		
		if($total > 20){
			$total = 20;
		}else if($total==0){
			$total = 10;
		}else{}

		$token = @$this->request->query['token'];

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//do something
				$rs = $this->getAgentRequestHistory($agent_id,$start,$total);
				$this->set('response',array('status'=>1,'data'=>$rs));
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	/*
	* API for vieweing the request quota history
	* /api/agent_orders?agent_id=[n]&start=0&total=10
	* Response :JSON
	*/
	public function agent_orders(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->query['agent_id']);
		$start = intval(@$this->request->query['start']);
		$total = intval(@$this->request->query['total']);
		
		if($total > 20){
			$total = 20;
		}else if($total==0){
			$total = 10;
		}else{}

		$token = @$this->request->query['token'];

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//do something
				$rs = $this->getAgentOrders($agent_id,$start,$total);
				$this->set('response',array('status'=>1,'data'=>$rs));
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	/*
	* API for vieweing the request quota history
	* POST :  /api/agent_checkout/
	* PostData : ?agent_id=[n]&d=[hashed_data]
	* Response :JSON
	*/
	public function agent_checkout(){
		$this->layout="ajax";

		$agent_id = intval(@$this->request->data['agent_id']);
		
		$order_data = unserialize(decrypt_param(@$this->request->data['d']));

		$token = @$this->request->data['token'];

		$po_number = '1001'.$agent_id.'-'.date("YmdHis").'-'.rand(1000,9999);

		if($agent_id > 0){
			if($this->agent_validate($agent_id,$token)){
				//save order
				$order_id = $this->saveAgentOrder($agent_id,$po_number,$order_data);
				if($order_id > 0){
					//generate vouchers
					$this->generateAgentVouchers($agent_id,$order_id,$po_number,
													$order_data['item_id'],$order_data['qty']);
					$this->set('response',array('status'=>1,
												'data'=>array('po_number'=>$po_number,
															'order_id'=>$order_id,
															'item_id'=>$order_data['item_id'],
															'qty'=>$order_data['qty'])));	
				}else{
					$this->set('response',array('status'=>0,'error'=>'cannot complete the purchase'));
				}
				
				
			}else{
				$this->set('response',array('status'=>0,'error'=>'invalid token'));
			}
		}else{
			$this->set('response',array('status'=>0,'error'=>'invalid agent'));
		}
		$this->render('default');
	}
	private function saveAgentOrder($agent_id,$po_number,$order_data){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('AgentItem');
		//sanitize
		$order_data['item_id'] = intval($order_data['item_id']);
		$order_data['qty'] = intval($order_data['qty']);
		//

		//item detail
		$item = $this->MerchandiseItem->findById(intval($order_data['item_id']));

		//agent's stock
		$agent_stock = $this->AgentItem->find('first',array(
			'conditions'=>array('agent_id'=>$agent_id,
								'merchandise_item_id'=>$order_data['item_id'])
		));

		//stock before purchased
		$current_stock = intval(@$agent_stock['AgentItem']['qty']);
		//stock after purchased
		$new_stock = $current_stock - intval($order_data['qty']);

		if(isset($item['MerchandiseItem']) && $new_stock >= 0 && $current_stock > 0){
			$total_price = intval($item['MerchandiseItem']['price_money']) * intval($order_data['qty']);
			
			$data = $order_data;

			$shopping_cart = array(
				array('item_id'=>$order_data['item_id'],
					'qty'=>$order_data['qty'],
					'data'=>$item)
			);
			$data['merchandise_item_id'] = $order_data['item_id'];
			$data['user_id'] = 0;
			$data['order_type'] = 1;
			$data['game_team_id'] = 0;
			$data['agent_id'] = $agent_id;
			$data['n_status'] = 3;
			$data['order_date'] = date("Y-m-d H:i:s");
			$data['data'] = serialize($shopping_cart);
			$data['po_number'] = $po_number;
			$data['total_sale'] = intval($total_price);
			$data['payment_method'] = 'direct';
			$data['trace_code'] = 0;
			$data['ongkir_id'] = intval(@$data['city_id']);
			$data['ongkir_value'] = 0;
			
			$this->loadModel('AgentOrder');
			$this->AgentOrder->create();
			$rs = $this->AgentOrder->save($data);
			$order_id = $this->AgentOrder->getInsertID();
			if(intval($order_id) > 0){
				//reduce the stock
				$this->reduceAgentStock($agent_id,$order_data['item_id'],$order_data['qty']);
				return $order_id;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}
	private function reduceAgentStock($agent_id,$item_id,$qty){
		return $this->Game->query("UPDATE fantasy.agent_items SET qty = qty - {$qty} 
							WHERE agent_id={$agent_id} AND merchandise_item_id={$item_id}");
	}
	/*
	* generate ticket voucher, and saves it in fantasy.merchandise_vouchers
	*/
	private function generateAgentVouchers($agent_id,$order_id,$po_number,$item_id,$qty){
		
		$n_vouchers = 0;
		$no = 1;
		$qty = intval($qty);
		
			for($j=0;$j < $qty;$j++){
				$voucher_code = $po_number.$no;
				CakeLog::write('generateAgentVoucher',date("Y-m-d H:i:s")." - ".$po_number.' - '.$voucher_code);
				
				$sql = "
				INSERT IGNORE INTO fantasy.agent_vouchers
				(agent_id,agent_order_id,merchandise_item_id,voucher_code,created_dt,n_status)
				VALUES
				({$agent_id},
				{$order_id},
				 {$item_id},
				 '{$voucher_code}',
				 NOW(),
				 0)";
				CakeLog::write('generateAgentVoucher',' - >'.$sql);
				$rs = $this->Game->query($sql);
				
				$no++;
				$n_vouchers++;
				
			}
			
		return $n_vouchers;
	}
	private function getAgentRequestHistory($agent_id,$start,$total){
		$this->loadModel('AgentRequest');
		$rs = $this->AgentRequest->query("SELECT * FROM 
											fantasy.agent_requests a
											INNER JOIN fantasy.merchandise_items b
											ON a.merchandise_item_id = b.id
											INNER JOIN fantasy.merchandise_items c
											ON b.parent_id = c.id
											WHERE a.agent_id = {$agent_id}
											LIMIT {$start},{$total};");
		$items = array();
		for($i=0;$i<sizeof($rs);$i++){
			$item = $rs[$i]['a'];
			$item['item_name'] = $rs[$i]['c']['name'].' - '.$rs[$i]['b']['name'];
			$item['price'] = $rs[$i]['b']['price_money'];
			$item['data'] = json_decode($rs[$i]['b']['data']);
			$items[] = $item;
		}
		return $items;
	}
	/**
	* basically it returns all the vouchers
	*/
	private function getAgentOrders($agent_id,$start,$total){
		$this->loadModel('AgentRequest');
		$this->loadModel('MerchandiseItem');
		$rs = $this->AgentRequest->query("SELECT * FROM fantasy.agent_vouchers a
											INNER JOIN fantasy.agent_orders b
											ON a.agent_order_id = b.id
											WHERE a.agent_id = {$agent_id} 
											ORDER BY a.id DESC LIMIT {$start},{$total};");
		$items = array();
		for($i=0;$i<sizeof($rs);$i++){
			$item = $rs[$i]['b'];
			$item['voucher_code'] = $rs[$i]['a']['voucher_code'];
			$item_data = unserialize($rs[$i]['b']['data']);
			
			$item_parent = $this->MerchandiseItem->findById($item_data[0]['data']['MerchandiseItem']['parent_id']);
			
			$item['data'] = json_decode($item_data[0]['data']['MerchandiseItem']['data'] );
			$item['item_name'] = $item_parent['MerchandiseItem']['name'].' '.$item_data[0]['data']['MerchandiseItem']['name'];
			$item['voucher_id'] = $rs[$i]['a']['voucher_code'];
			$items[] = $item;
		}
		return $items;
	}
	private function setAgentRequestQuota($agent_id,$item_id,$qty){
		$this->loadModel('AgentRequest');
		$this->AgentRequest->create();
		$data = array(
			'agent_id'=>$agent_id,
			'merchandise_item_id'=>$item_id,
			'request_quota'=>$qty,
			'request_date'=>date("Y-m-d H:i:s"),
			'n_status'=>0
		);
		$rs = $this->AgentRequest->save($data);
		if(isset($rs['AgentRequest'])){
			return $rs['AgentRequest'];	
		}else{
			return 0;
		}
		
	}
	private function setAgentReturnedQuota($agent_id,$item_id,$qty){
		$this->loadModel('AgentReturnedStock');
		$this->AgentReturnedStock->create();
		$data = array(
			'agent_id'=>$agent_id,
			'merchandise_item_id'=>$item_id,
			'returned_quota'=>$qty,
			'request_date'=>date("Y-m-d H:i:s"),
			'n_status'=>0
		);
		$rs = $this->AgentReturnedStock->save($data);
		if(isset($rs['AgentReturnedStock'])){
			
			//reduce agent stock
			$this->reduceAgentStock($agent_id,$item_id,$qty);
			//increase ss stock
			$this->Game->query("UPDATE fantasy.merchandise_items SET stock = stock + {$qty} 
								WHERE id={$item_id}");
			return $rs['AgentReturnedStock'];	
		}else{
			return 0;
		}
		
	}
	//use for validating the token
	private function agent_validate($agent_id,$token){
		
		$tokeninfo = @unserialize(decrypt_param($token));
	
		$this->loadModel('Agent');
		$agent = $this->Agent->findById($agent_id);
		if($agent['Agent']['id'] == $tokeninfo['agent_id'] &&
				$agent['Agent']['email'] == $tokeninfo['email']){
			return true;
		}
	}
	private function getAgentCatalog($agent_id){
		$rs = $this->Game->query("SELECT a.id,a.parent_id,a.name,a.description,a.price_money as price,
							b.qty,b.n_status,c.name AS parent_name,
							c.description AS parent_description,a.data,1 AS agent_id
							FROM fantasy.merchandise_items a
							LEFT JOIN fantasy.agent_items b
							ON a.id = b.merchandise_item_id AND b.agent_id = {$agent_id}
							INNER JOIN fantasy.merchandise_items c
							ON a.parent_id = c.id
							WHERE a.merchandise_category_id= ".Configure::read('ticket_category_id')." 
							AND a.parent_id NOT IN (0,1300,1301) AND a.n_status = 1 LIMIT 1000;");
		$items = array();
		for($i=0;$i<sizeof($rs);$i++){
			$item = $rs[$i]['a'];
			$item['agent_id'] = intval($rs[$i][0]['agent_id']);
			$item['qty'] = intval($rs[$i]['b']['qty']);
			$item['n_status'] = intval($rs[$i]['b']['n_status']);
			$item['parent'] = $rs[$i]['c'];
			$item['data'] = json_decode($item['data']);
			$items[] = $item;
		}
		return $items;
	}
	//--> end of ticketing stuffs
}