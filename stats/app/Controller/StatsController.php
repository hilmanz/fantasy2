<?php
/**
* OPTA Valde HTTP Push EndPoint Implementation
*/
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
require_once APP.DS.'Vendor'.DS.'common.php';
class StatsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Stats';
	
	public function index($type=null){
		$this->report($type);
	}
	public function report($type){
		$this->loadModel('Stats');
		$this->loadModel('BestAndWorstStats');
		$type = intval($type);
		switch($type){
			case 1:
				$response = $this->bpl_leaderboard();
			break;
			case 2:
				$response = $this->best_and_worsts_in_league();
			break;
			case 3:
				$response = $this->weekly_match_reports();
			break;
			case 4:
				$response = $this->last_week_best_and_worsts_player();
			break;
			default:
				$response = array('status'=>1,'data'=>'ready');
			break;
		}
		$this->output($response['status'],$response['data']);
	}
	public function bpl_leaderboard(){
		$result = $this->Stats->getLeaderboard();
		return array('status'=>'1','data'=>$result);
	}
	public function best_and_worsts_in_league(){
		$result = $this->BestAndWorstStats->getReports();
		return array('status'=>'1','data'=>$result);
	}
	public function weekly_match_reports(){
		$this->loadModel('WeeklyMatchStats');
		$matchday = intval($this->request->query['matchday']);
		if($matchday==0){
			$matchday = 1;
		}
		$result = $this->WeeklyMatchStats->getReports($matchday);
		return array('status'=>'1','data'=>$result);
	}
	public function last_week_best_and_worsts_player(){
		$this->loadModel('PlayerStats');
		$result = $this->PlayerStats->last_week_report();
		return array('status'=>'1','data'=>$result);	
	}
	private function output($status,$data){
		$this->layout='ajax';
		$this->set('response',array('status'=>$status,'data'=>$data));
		$this->render('json/index');
	}
}
