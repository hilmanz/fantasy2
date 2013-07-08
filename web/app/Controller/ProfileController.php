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
	public function beforeFilter(){
		parent::beforeFilter();
		$this->loadModel('ProfileModel');
		$this->ProfileModel->setAccessToken($this->getAccessToken());
	}
	public function index(){
		$this->redirect("/profile/details");
	}
	private function getBudget($team_id){
		/*Budget*/
		
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
	public function register_team(){
		$userData = $this->getUserData();
		if($this->request->is('post')){
			if(strlen($this->request->data['team_name']) > 0
				&& strlen($this->request->data['team_id']) > 0
				&& strlen($this->request->data['fb_id']) > 0){
				$this->Session->write('TeamRegister',$this->request->data);
				$this->redirect('/profile/select_player');
			}else{
				$this->Session->setFlash('Kamu harus memilih salah satu team terlebih dahulu !');
				$this->redirect('/profile/team_error');
			}
			
		}else{
			$teams = $this->Game->getTeams();
			$this->set('team_list',$teams);
			$this->set('INITIAL_BUDGET',Configure::read('INITIAL_BUDGET'));
		}
	}
	/**
	/*@todo harus pastiin bahwa halaman ini hanya bisa diakses kalo user uda ada register
	*/
	public function select_player(){
		$userData = $this->getUserData();

		if(is_array($this->Session->read('TeamRegister'))){
			$userData = $this->getUserData();
			$this->set('INITIAL_BUDGET',Configure::read('INITIAL_BUDGET'));
			$teams = $this->Game->getTeams();
			$this->set('team_list',$teams);
			$this->set('selected_team',$this->Session->read('TeamRegister'));
		}else{
			$this->redirect('/profile/register_team');
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

	public function register(){
		$this->loadModel('User');
	
		$this->set('INITIAL_BUDGET',Configure::read('INITIAL_BUDGET'));
		$user_fb = $this->Session->read('UserFBDetail');
		$this->set('user',$user_fb);
		
		if($this->request->is('post')){
			$data = array('fb_id'=>$user_fb['id'],
						  'name'=>$this->request->data['name'],
						  'email'=>$this->request->data['email'],
						  'location'=>$this->request->data['city'],
						  'register_date'=>date("Y-m-d H:i:s"),
						  'survey_about'=>$this->request->data['hearffl'],
						  'survey_daily_email'=>$this->request->data['daylyemail'],
						  'survey_daily_sms'=>$this->request->data['daylysms'],
						  'survey_has_play'=>$this->request->data['firstime'],
						  'n_status'=>1,
						  'register_completed'=>0
						  );
			//make sure that the fb_id is unregistered
			$check = $this->User->findByFb_id($user_fb['id']);

			if(isset($check['User'])){
				$this->Session->destroy();
				$this->Session->setFlash('Mohon maaf, akun kamu sudah terdaftar sebelumnya. !');
				$this->redirect('/profile/error');
			}else{
				$this->User->create();
				$rs = $this->User->save($data);
				if(isset($rs['User'])){

					//register user into gameAPI.
					
					$response = $this->ProfileModel->setProfile($data);
					if($response['status']==1){
						$this->redirect("/profile/teams");
					}else{
						$this->User->delete($this->User->id);
						$this->Session->setFlash('Mohon maaf, tidak berhasil mendaftarkan akun kamu. 
													Silahkan coba kembali beberapa saat lagi!');
						$this->redirect('/profile/error');
					}
				}
			}
		}
	}
	public function error(){

		$this->render('error');
	}
	public function team_error(){
		$this->set('error_type','team');
		$this->render('error');
	}
}
