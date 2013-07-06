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
class ProfileController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Profile';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

	public function index(){
		$this->redirect("/profile/details");
	}
	private function getBudget($team_id){
		/*Budget*/
		$this->loadModel('ProfileModel');
		$this->ProfileModel->setAccessToken($this->getAccessToken());
		$team_id = 1; // sample
		$this->set('team_bugdet',$this->ProfileModel->getBudget($team_id));
	}
	public function details(){
		$this->getBudget($team_id);

		/*facebook detail*/
		$userData = $this->getUserData();

		if($_POST['save']==1){
			$data = array(
				'fb_id'=>$userData['fb_id'],
				'name'=>$_POST['name'],
				'email'=>$_POST['email'],
				'phone'=>$_POST['phone_number'],
				'phone'=>$_POST['city']
			);
			$response = $this->ProfileModel->setProfile($data);
			if($response){
				$this->redirect("/profile/teams");
			}
		}
	}
	public function teams(){
		$this->getBudget($team_id);

		/*facebook detail*/
		$userData = $this->getUserData();

		$this->set('team_list',$this->ProfileModel->getTeams());

		/*sample data for creating team*/
		$this->set('team',array('uid'=>1));

		$this->set('fb_id',$userData['fb_id']);
		if($_POST['create_team']=1){
			
			if($response){
				$this->redirect("/profile/players");
			}
		}

	}
	public function players(){
		$this->getBudget($team_id);
	}
	public function staffs(){
		$this->getBudget($team_id);

	}
	public function clubs(){
		$this->getBudget($team_id);

	}
	public function formations(){

	}
	public function invite_friends(){

	}
}
