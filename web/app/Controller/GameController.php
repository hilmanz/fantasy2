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
App::uses('Sanitize', 'Utility');


/**
 *GAME API Controller
 *
 * 
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class GameController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Game';
	public $components = array('RequestHandler');
/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	public $layout = null;
	
	/**
	* master data daftar pemain
	*/
	public function players($team_id){
		$team_id = Sanitize::paranoid($team_id);
		header('Content-type: application/json');
		print json_encode($this->Game->getMasterTeam($team_id));
		die();
	}
	/**
	*	daftar lineup saat ini.
	*/
	public function lineup(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		$lineup = $this->Game->getLineup($userData['team']['id']);
		header('Content-type: application/json');
		print json_encode($lineup);
		die();
	}
	/**
	* @todo
	* harus pastikan cuman bisa save lineup sebelum pertandingan dimulai.
	*/
	public function save_lineup(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		$formation = $this->request->data['formation'];
		$players = array();
		foreach($this->request->data as $n=>$v){
			if(eregi('player-',$n)&&$v!=0){
				$players[] = array('player_id'=>str_replace('player-','',$n),'no'=>intval($v));
			}
		}
		$lineup = $this->Game->setLineup($userData['team']['id'],$formation,$players);

		header('Content-type: application/json');
		if(@$lineup['status']==1){
			$msg = "@p1_".$this->userDetail['User']['id']." has set his/her formation.";
			$this->Info->write('set formation',$msg);
		}
		print json_encode($lineup);
		die();
	}

	public function hire_staff(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		$staff_id = intval($this->request->data['id']);
		
		$rs = $this->Game->hire_staff($userData['team']['id'],$staff_id);
		if($rs['status']==1){
			$msg = "@p1_".$this->userDetail['User']['id']." has hired a new {$rs['officials']['name']}.";
			$this->Info->write('set formation',$msg);
		}
		header('Content-type: application/json');
		print json_encode($rs);
		die();
	}
	public function dismiss_staff(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		$staff_id = intval($this->request->data['id']);
		
		$rs = $this->Game->dismiss_staff($userData['team']['id'],$staff_id);
		header('Content-type: application/json');
		print json_encode($rs);
		die();
	}
	public function next_match(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		$result = $this->Game->getNextMatch($userData['team']['team_id']);
		if($result['status']==1){
			$result['match']['match_date_ts'] = strtotime($result['match']['match_date']);
			$result['match']['match_date'] = date("Y-m-d",$result['match']['match_date_ts']);
			$result['match']['match_time'] = date("H:i",$result['match']['match_date_ts']);
		}
		print json_encode($result);
		die();
	}

	public function venue($team_id){
		$result = $this->Game->getVenue($team_id);
		print json_encode($result);
		die();
	}


	public function check_team_name(){
		$team_name = Sanitize::clean($this->request->query['name']);
		$this->loadModel('Team');
		
		$club = $this->Team->findByTeam_name($team_name);
		if(isset($club['Team'])){
			print json_encode(array("status"=>1));
		}else{
			print json_encode(array("status"=>0));
		}
		die();
	}
}
