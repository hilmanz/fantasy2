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
	
}
