<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');


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
		$totalUser = $this->User->find('count');
		$this->paginate = array('limit'=>20);
		$rs = $this->paginate('User');
		foreach($rs as $n=>$r){
			$point = $this->Point->findByTeam_id($r['Team']['id']);
			if(isset($point['Point'])){
				$rs[$n]['Point'] = $point['Point'];
			}
		}
		$this->set('total_users',$totalUser);
		$this->set('rs',$rs);
	}

	public function search(){
		$this->loadModel('User');
		$this->loadModel('Point');
		App::Import('Model', 'PlayerReport');
		$this->PlayerReport = new PlayerReport;
		
		$q = $this->request->query['q'];
		//$this->paginate = array('limit'=>25);
		$this->paginate = array('conditions'=>array('OR'=>array("team_name LIKE '%".Sanitize::clean($q)."%'",
												"User.email LIKE '%".Sanitize::clean($q)."%'")),
								'limit'=>1);
		$rs = $this->paginate('PlayerReport');

		
		$this->set('rs',$rs);
		
	}
	public function view($user_id){
		$this->loadModel('User');
		$this->loadModel('Point');
		$this->loadModel('Game');
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

		$squad = $this->Game->get_team_players($user['User']['fb_id']);

		foreach($squad as $n=>$v){
		
			$r = $this->Game->query("SELECT COUNT(*) AS total FROM (SELECT a.game_id FROM ffgame_stats.game_match_player_points a
								INNER JOIN ffgame.game_fixtures b
								ON a.game_id = b.game_id
								WHERE game_team_id={$team_data[0]['b']['id']}  
								AND player_id='{$v['uid']}'
								GROUP BY matchday) c;");
			$squad[$n]['total_plays'] = intval($r[0][0]['total']);
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

	public function top_weekly($week){
		$sql = "SELECT a.team_id,b.team_name,b.team_id,d.name AS original_team,
						c.*,SUM(points+extra_points) AS total_points 
				FROM fantasy.weekly_points a
				INNER JOIN fantasy.teams b
				ON a.team_id = b.id 
				INNER JOIN fantasy.users c
				ON b.user_id = c.id
				INNER JOIN ffgame.master_team d
				ON b.team_id = d.uid
				WHERE matchday={$week} GROUP BY a.team_id
				ORDER BY total_points DESC LIMIT 20;";
		$rs = $this->query($sql);

	}
	public function overall(){


		$this->loadModel('User');
		$this->loadModel('Point');
		App::Import('Model', 'PlayerReport');
		$this->PlayerReport = new PlayerReport;
		
		$totalUser = $this->User->find('count');
		$this->paginate = array('limit'=>25);
		$rs = $this->paginate('PlayerReport');

		$this->set('total_users',$totalUser);
		$this->set('rs',$rs);
		$this->set('sort',$this->request->params['named']['sort']);
	}
	/*
	* the page that showing the master player's stats
	*/
	public function playerstats(){

	}	
	public function player_performances(){
		$this->layout = 'ajax';
		$data = array();
		$start = intval($this->request->query['start']);
		$modifier = $this->Game->query("SELECT * FROM ffgame.game_matchstats_modifier s");
		$mods = array();
		while(sizeof($modifier)>0){
			$m = array_shift($modifier);
			$mods[$m['s']['name']] = array('goalkeeper'=>$m['s']['g'],
										'defender'=>$m['s']['d'],
										'midfielder'=>$m['s']['m'],
										'forward'=>$m['s']['f']);
		}


		$rs = $this->Game->query("SELECT a.*,b.name as team_name 
									FROM ffgame.master_player a
								  INNER JOIN ffgame.master_team b
								  ON a.team_id = b.uid
								  ORDER BY id ASC LIMIT {$start},20",false);
		while(sizeof($rs)>0){
			$p = array_shift($rs);
			$stats = $this->get_player_statistics($p['a'],$mods);
			$p['a']['stats'] = $stats;
			$p['a']['team_name'] = $p['b']['team_name'];
			$data[] = $p['a'];
		}
		$this->set('response',array('status'=>1,'data'=>$data));
		$this->render('response');
	}
	private function get_player_statistics($player,$modifier){
		$map = $this->getStatsCategories();

		$sql = "SELECT player_id,stats_name,SUM(stats_value) AS total 
				FROM ffgame_stats.master_player_stats s
				WHERE player_id='{$player['uid']}'
				GROUP BY stats_name;";
		$rs = $this->Game->query($sql);
		$games = 0;
		$passing_and_attacking = 0;
		$defending = 0;
		$goalkeeping = 0;
		$mistakes_and_errors = 0;
		$total_points = 0;
		foreach($rs as $n=>$r){

			$stats_name = $r['s']['stats_name'];
			$pos = strtolower($player['position']);
            $poin = ($modifier[$stats_name][$pos] * $r[0]['total']);
            if($this->is_in_category($map,'games',$stats_name)){
              $games += $poin;
            }
            if($this->is_in_category($map,'passing_and_attacking',$stats_name)){
              $passing_and_attacking += $poin;
            }
            if($this->is_in_category($map,'defending',$stats_name)){
              $defending += $poin;
            }
            if($this->is_in_category($map,'goalkeeping',$stats_name)){
              $goalkeeping += $poin;
            }
            if($this->is_in_category($map,'mistakes_and_errors',$stats_name)){
              $mistakes_and_errors += $poin;
            }
            $total_points += $poin;
		}
		return array('games'=>$games,
                      'passing_and_attacking'=>$passing_and_attacking,
                      'defending'=>$defending,
                      'goalkeeping'=>$goalkeeping,
                      'mistakes_and_errors'=>$mistakes_and_errors,
                      'total'=>$total_points);

	}
	private function is_in_category($map,$category,$stats_name){
        foreach($map[$category] as $n=>$v){
            if($v==$stats_name){
              return true;
            }
        }
    }
	private function getStatsCategories(){
     // $this->out('get map');
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
                    'goalkeeping'=>$goalkeeper,
                    'mistakes_and_errors'=>$mistakes_and_errors
                   );

      unset($games);
      unset($passing_and_attacking);
      unset($defending);
      unset($goalkeeper);
      unset($mistakes_and_errors);
      return $map;
    }
}
