<?php
/**
 * Market Controller

 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class MarketController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Market';

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
