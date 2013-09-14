<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Pages';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 */
	public function display() {
		$this->loadModel('Service');

		$path = func_get_args();

		$count = count($path);
		if (!$count) {
			$this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));

		switch($page){
			case 'home':
				$this->home();
			break;
			case 'team':
				$this->team();
			break;
			case 'matchstats':
				$this->matchstats();
			break;
			case 'matchdetails':
				$this->matchdetails();
			break;
			case 'player':
				$this->player();
			break;
			case 'playerstats':
				$this->playerstats();
			break;
		}

		$this->render(implode('/', $path));
	}
	private function home(){
		$rs = $this->Service->request('stats/report/1');
		if($rs['status']==1){
			$this->set('data',$rs['data']);	
		}
		$rs = $this->Service->request('stats/report/2');
		if($rs['status']==1){
			$this->set('data2',$rs['data']);	
		}
		$rs = $this->Service->request('stats/report/7');
		if($rs['status']==1){
			$this->set('data3',$rs['data']);	
		}
	}
	private function team(){
		$team_id = $this->request->query['team_id'];
		$rs = $this->Service->request('stats/report/5?team_id='.$team_id);

		if($rs['status']==1){
			$this->set('data',$rs['data']);	
		}

		//match report collection
		$rs = $this->Service->request('stats/report/13?team_id='.$team_id);
		if($rs['status']==1){
			$this->set('report',$rs['data']);	
		}		
		$this->set('team_id',$team_id);
	}
	private function matchstats(){
		$team_id = $this->request->query['team_id'];
		$game_id = $this->request->query['game_id'];
		
		$rs = $this->Service->request('stats/report/9?game_id='.$game_id.'&team_id='.$team_id);
		//overall stats
		$rs2 = $this->Service->request('stats/report/5?team_id='.$team_id);
		if($rs['status']==1){
			$this->set('data',$rs['data']);
			$this->set('overall_stats',$rs2['data']);
		}	
		$this->set('team_id',$team_id);
	}
	private function matchdetails(){
		$game_id = $this->request->query['game_id'];
		$team_id = $this->request->query['team_id'];
		$rs = $this->Service->request('stats/report/8?game_id='.$game_id);
		
		
		//home overall stats
		$rs2 = $this->Service->request('stats/report/5?team_id='.$rs['data']['results']['home_team']);
		//away overall stats
		$rs3 = $this->Service->request('stats/report/5?team_id='.$rs['data']['results']['away_team']);
		
		if($rs['status']==1){
			$this->set('report',$rs['data']);
			$this->set('home_overall',$rs2['data']);
			$this->set('away_overall',$rs3['data']);
		}
		$this->set('team_id',$team_id);
	}
	private function player(){
		
		$player_id = $this->request->query['player_id'];
		$rs = $this->Service->request('stats/report/6?player_id='.$player_id);

		if($rs['status']==1){
			$this->set('data',$rs['data']);	
		}
		$this->set('player_id',$player_id);
	}
	private function playerstats(){
		$game_id = $this->request->query['game_id'];
		$player_id = $this->request->query['player_id'];
		$rs = $this->Service->request('stats/report/10?game_id='.$game_id.'&player_id='.$player_id);

		if($rs['status']==1){
			$this->set('data',$rs['data']);	
		}
		$this->set('player_id',$player_id);
		$this->set('game_id',$game_id);
	}
}
