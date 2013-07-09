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

		//list of staffs
		//get officials
		$officials = $this->Game->getAvailableOfficials($userData['team']['id']);
		$staffs = array();
		foreach($officials as $official){
			if(isset($official['hired'])){
				$staffs[] = $official;
			}
		}
		$this->set('staffs',$staffs);
	}
	public function hiring_staff(){
		$this->loadModel('Team');
		$this->loadModel('User');
		$userData = $this->getUserData();

		if(isset($this->request->query['hire'])){
			$official_id = intval($this->request->query['id']);
			if($official_id>0){
				$this->Game->hire_staff($userData['team']['id'],$official_id);
			}
		}
		if(isset($this->request->query['dismiss'])){
			$official_id = intval($this->request->query['id']);
			if($official_id>0){
				$this->Game->dismiss_staff($userData['team']['id'],$official_id);
			}
		}
		//budget
		$budget = $this->Game->getBudget($userData['team']['id']);
		$this->set('team_bugdet',$budget);

		//get officials
		$officials = $this->Game->getAvailableOfficials($userData['team']['id']);

		//estimated costs
		$total_weekly_salary = 0;
		foreach($officials as $official){
			if(isset($official['hired'])){
				$total_weekly_salary+=$official['salary'];
			}
		}
		$this->set('officials',$officials);
		$this->set('weekly_salaries',$total_weekly_salary);
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
