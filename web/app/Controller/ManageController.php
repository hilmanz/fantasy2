<?php
/**
 * Manage Controller

 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ManageController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Manage';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	public function beforeFilter(){
		parent::beforeFilter();
		$userData = $this->getUserData();
		
	}
	public function hasTeam(){
		$userData = $this->getUserData();
		if(is_array($userData['team'])){
			return true;
		}
	}
	public function index(){
		$this->redirect('/manage/club');
	}
	public function club(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();
		//user data
		$user = $this->User->findByFb_id($userData['fb_id']);
		$this->set('user',$user['User']);

		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//club
		$club = $this->Team->findByUser_id($user['User']['id']);
		$this->set('club',$club['Team']);

		//list of players
		$players = $this->Game->get_team_players($userData['fb_id']);
		$this->set('players',$players);
	}
	public function error(){
		$this->render('error');
	}
	public function team_error(){
		$this->set('error_type','team');
		$this->render('error');
	}
	public function success(){
		$this->render('success');
	}
}
