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
		$type = intval($type);
		switch($type){
			case 1:
				$response = $this->bpl_leaderboard();
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
	private function output($status,$data){
		$this->layout='ajax';
		$this->set('response',array('status'=>$status,'data'=>$data));
		$this->render('json/index');
	}
}
