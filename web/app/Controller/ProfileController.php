<?php
/**
 * Profile Controller
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
App::uses('File', 'Utility');
require_once APP . 'Vendor' . DS. 'Thumbnail.php';

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
		$userData = $this->getUserData();
		$this->loadModel('ProfileModel');
		$this->ProfileModel->setAccessToken($this->getAccessToken());
	}
	public function hasTeam(){
		$userData = $this->getUserData();
		if(is_array($userData['team'])){
			return true;
		}
	}
	public function index(){
		if($this->hasTeam()){
			$this->set('avatar_dir',Configure::read('avatar_web_dir'));
			$userData = $this->getUserData();
			$this->loadModel('User');
			//data user
			$user = $this->User->findByFb_id($userData['fb_id']);
			
			$this->set('user',$user['User']);
			//budget
			$budget = $this->Game->getBudget($userData['team']['id']);
			$this->set('team_bugdet',$budget);
			$this->render('details');
		}else{
			$this->redirect('/');
		}
		
	}

	public function update(){
		$data = array(
			'name'=>$this->request->data['name'],
			'email'=>$this->request->data['email'],
			'location'=>$this->request->data['location']
		);
		$userData = $this->getUserData();
		$this->loadModel('User');
		$user = $this->User->findByFb_id($userData['fb_id']);

		$this->User->id = $user['User']['id'];
		$rs = $this->User->save($data);
		if(isset($rs)){

			$this->Session->setFlash('Profil Anda telah berhasil diubah!');
			$this->redirect('/profile/success');
		}else{
			$this->Session->setFlash('Gagal menyimpan perubahan, coba beberapa saat lagi!');
			$this->redirect('/profile/error');
		}
	
		die();
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
		$this->set('avatar_dir',Configure::read('avatar_img_dir'));
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
	
	public function register_team(){
		$userData = $this->getUserData();

		if(@$userData['register_completed']!=1){
			$team = $this->Session->read('TeamRegister');
			$this->set('previous_team',$team);
			if($userData==null){
				$this->redirect('/login/expired');
			}
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
		}else{
			$this->redirect("/");
		}
	}

	public function create_team(){
		if($userData['register_completed']!=1){
			$userData = $this->getUserData();
			$team = $this->Session->read('TeamRegister');
			$players = explode(',',$this->request->data['players']);
			$data = array(
				'team_id'=>Sanitize::paranoid($team['team_id']),
				'fb_id'=>Sanitize::paranoid($userData['fb_id'])
			);
			
			foreach($players as $n=>$p){
					$players[$n] = Sanitize::clean(trim($p));
			}
			$data['players'] = json_encode($players);


			$result = $this->Game->create_team($data);
			
			if(isset($result['error'])){
				$this->Session->setFlash('Maaf, Anda tidak dapat membentuk tim lagi. Nampaknya Anda sudah melakukan pembentukan tim sebelumnya.');
				$this->redirect('/profile/team_error');
			}else{
				$this->loadModel('User');			
				$user = $this->User->findByFb_id($userData['fb_id']);
				$userData['team'] = $this->Game->getTeam(Sanitize::paranoid($userData['fb_id']));
				$this->loadModel('Team');
				$this->Team->create();
				$InsertTeam = $this->Team->save(array(
					'user_id'=>$user['User']['id'],
					'team_id'=>Sanitize::paranoid($team['team_id']),
					'team_name'=>Sanitize::clean($team['team_name'])
				));
				$this->Session->write('Userlogin.info',$userData);
				$this->Session->write('TeamRegister',null);
				$this->Session->setFlash('Congratulations, Your team is ready !');
				$this->redirect('/profile/register_staff');
			}
		}
	}
	/**
	* @todo harus pastiin bahwa halaman ini hanya bisa 
	* diakses kalo user uda ada register
	*/
	public function select_player(){
		if(@$userData['register_completed']!=1){

			$userData = $this->getUserData();
			$selected_team = $this->Session->read('TeamRegister');
			
			if(is_array($this->Session->read('TeamRegister'))){
				
				$userData = $this->getUserData();
				$this->set('INITIAL_BUDGET',Configure::read('INITIAL_BUDGET'));
				$teams = $this->Game->getTeams();
				$this->set('team_list',$teams);
				$this->set('selected_team',$selected_team);
				$original = $this->Game->getClub($selected_team['team_id']);
				$this->set('original',$original);

			}else{
				$this->redirect('/profile/register_team');
			}

		}else{
			$this->redirect('/');
		}
	}
	public function register_staff(){
		if(@$this->userData['register_completed']==1){
			$this->redirect('/');
		}	
		$userData = $this->getUserData();

		$user = $this->User->findByFb_id($userData['fb_id']);
				
		$this->User->id = $user['User']['id'];
		$this->User->set('register_completed',1);
		$rs = $this->User->save();

		//set register_completed ==1 in session
		$this->userData['register_completed'] = 1;
		$this->Session->write('Userlogin.info',$this->userData);
		if($rs){
			$this->Session->write('first_time',true);
			$this->redirect('/manage/team');
		}else{
			$this->redirect('/profile/error');
		}
				
		/*
		if($this->request->is('post')){
			$this->loadModel('User');
			if($this->request->data['complete_registration']==1){
				$user = $this->User->findByFb_id($userData['fb_id']);
				
				$this->User->id = $user['User']['id'];
				$this->User->set('register_completed',1);
				$rs = $this->User->save();

				//set register_completed ==1 in session
				$this->userData['register_completed'] = 1;
				$this->Session->write('Userlogin.info',$this->userData);
				if($rs){
					$this->Session->write('first_time',true);
					$this->redirect('/manage/team');
				}else{
					$this->redirect('/profile/error');
				}
			}
		}else{
			
			//budget
			$budget = $this->Game->getBudget($userData['team']['id']);
			$this->set('team_bugdet',$budget);

			//get officials
			$officials = $this->Game->getAvailableOfficials($userData['team']['id']);
			

			//estimated costs
			$total_weekly_salary = 0;

			//current staff's salary (if exists)
			foreach($officials as $official){
				if(isset($official['hired'])){
					$total_weekly_salary += intval($official['salary']);
				}
			}

			//player's salary
			$players = $this->Game->get_team_players($userData['fb_id']);

			foreach($players as $player){
				$total_weekly_salary += intval($player['salary']);
			}

			$this->set('officials',$officials);
			$this->set('weekly_salaries',$total_weekly_salary);
		}
		*/
	}
	
	public function register(){
		
		if(@$this->userData['register_completed']!=1){
			$this->loadModel('User');
		
			$this->set('INITIAL_BUDGET',Configure::read('INITIAL_BUDGET'));
			$user_fb = $this->Session->read('UserFBDetail');
			$this->set('user',$user_fb);
			$this->set('phone_empty',false);
			if($user_fb['id']==null){
				$this->Session->setFlash('Mohon maaf, tidak berhasil login menggunakan akun facebook kamu. 
															Silahkan coba kembali beberapa saat lagi!');
								$this->redirect('/profile/error');
			}else{
				if($this->request->is('post') && $this->request->data['phone_number']!=null){
					$this->request->data['hearffl'] = (isset($this->request->data['hearffl'])) ? $this->request->data['hearffl'] : 0;
					$this->request->data['daylyemail'] = (isset($this->request->data['daylyemail'])) ? $this->request->data['daylyemail'] : 0;
					$this->request->data['daylysms'] = (isset($this->request->data['daylysms'])) ? $this->request->data['daylysms'] : 0;
					$this->request->data['firstime'] = (isset($this->request->data['firstime'])) ? $this->request->data['firstime'] : 0;
					$birthdate = intval($this->request->data['bod_yr']).'-'.intval($this->request->data['bod_mt']).'-'.intval($this->request->data['bod_dt']);
					$data = array('fb_id'=>$user_fb['id'],
								  'name'=>$this->request->data['name'],
								  'email'=>$this->request->data['email'],
								  'location'=>$this->request->data['city'],
								  'phone_number'=>$this->request->data['phone_number'],
								  'register_date'=>date("Y-m-d H:i:s"),
								  'survey_about'=>$this->request->data['hearffl'],
								  'survey_daily_email'=>$this->request->data['daylyemail'],
								  'survey_daily_sms'=>$this->request->data['daylysms'],
								  'survey_has_play'=>$this->request->data['firstime'],
								  'faveclub'=>Sanitize::clean($this->request->data['faveclub']),
								  'birthdate'=>$birthdate,
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
								//send info
								$msg = "@p1_".$rs['User']['id']." sudah terdaftar dalam fantasy football.";
								$this->Info->write('new player',$msg);
								
								$this->redirect("/profile/register_team");
							}else{
								$this->User->delete($this->User->id);
								$this->Session->setFlash('Mohon maaf, tidak berhasil mendaftarkan akun kamu. 
															Silahkan coba kembali beberapa saat lagi!');
								$this->redirect('/profile/error');
							}
						}
					}
				}else if($this->request->is('post') && $this->request->data['phone_number']==null){
					$this->Session->setFlash('Harap mengisi nomor mobile phone terlebih dahulu !');
					$this->set('phone_empty',true);
				}
			}
			
		}else{
			$this->redirect('/manage/team');
		}
	}
	public function error(){
		$this->render('error');
	}
	public function team_error(){
		$this->set('error_type','team');
		$this->render('error');
	}
	public function success(){

		$user = $this->User->findByFb_id($this->userData['fb_id']);

		$this->set('user',$user['User']);
		$this->render('success');
	}

	public function logout(){
		$this->Session->destroy();
		$this->redirect('/login/thankyou');
	}

	public function upload_image(){
		$this->layout = 'ajax';
		$_FILES['file']['name'] = str_replace(array(' ','\''),"_",$_FILES['file']['name']);
		if(move_uploaded_file($_FILES['file']['tmp_name'],
				Configure::read('avatar_img_dir').$_FILES['file']['name'])){
			//resize to 120x120 pixels
			$thumb = new Thumbnail();
			$thumb->resizeImage('resizeCrop', $_FILES['file']['name'], 
							Configure::read('avatar_img_dir'), 
							'120x120_'.$_FILES['file']['name'], 
							120, 
							120, 
							100);
			//save to db
			$data = array(
				'avatar_img'=>$_FILES['file']['name']
			);
			$userData = $this->getUserData();
			$this->loadModel('User');
			$user = $this->User->findByFb_id($userData['fb_id']);
			$this->User->id = $user['User']['id'];
			$rs = $this->User->save($data);
			print json_encode(array('status'=>1,'files'=>$_FILES['file']['name']));
		}else{
			print json_encode(array('status'=>0));
		}
		die();
	}
}
