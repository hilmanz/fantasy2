<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	protected $FB_APP_ID;
	protected $FB_SECRET;
	protected $userData;
	protected $userDetail;
	protected $userPoints;
	protected $userRank;
	protected $nextMatch;
	protected $redisClient;
	protected $gameApiAccessToken;
	protected $closeTime;
	public function beforeFilter(){
		if($this->request->is('mobile') &&
			$this->request->params['pass'][0]!='mobile'){
			$this->redirect('/pages/mobile');
			die();
		}
		
		$this->disableCache();
		$this->response->disableCache();
		
		$this->loadModel('Game');
		//set acces token
		$this->initAccessToken();
		$this->Game->setAccessToken($this->getAccessToken());

		if($this->params['controller']=='api'){
			$this->ApiInit();
			
		}else{
			
			

			$this->set('FB_APP_ID',Configure::read('FB.APP_ID'));
			$this->set('FB_SECRET',Configure::read('FB.SECRET'));
			$this->set('FB_AFTER_LOGIN_URL',Configure::read('FB.AFTER_LOGIN_REDIRECT_URL'));
			$this->set('debug',Configure::read('debug'));
			$this->set('DOMAIN',Configure::read('DOMAIN'));
			$this->FB_APP_ID = Configure::read('FB.APP_ID');
			$this->FB_SECRET = Configure::read('FB.SECRET');

			
		

		
			if($this->isUserLogin()){
				$this->userData = $this->getUserData();
				$this->set('USER_IS_LOGIN',true);
				$this->set('USER_DATA',$this->userData);
				$this->loadModel('User');
				$this->loadModel('Point');
				$this->loadModel('Info');
				$this->loadModel('Ticker');


				$this->userDetail = $this->User->findByFb_id($this->userData['fb_id']);
				
				$point = $this->Point->findByTeam_id(@$this->userDetail['Team']['id']);
				$this->userPoints = @$point['Point']['points'] + @$point['Point']['extra_points'];
				$this->userRank = @$point['Point']['rank'];
				$this->set('USER_RANK',$this->userRank);
				$this->set('USER_POINTS',$this->userPoints);

				$this->nextMatch = $this->Game->getNextMatch(@$this->userData['team']['team_id']);
				$this->nextMatch['match']['match_date_ts'] = strtotime(@$this->nextMatch['match']['match_date']);
				
				$this->set('match_date_ts',$this->nextMatch['match']['match_date_ts']);

				$previous_close_dt = date("Y-m-d", strtotime("previous Saturday"))." 17:00:00";
				

				$close_dt = date("Y-m-d", strtotime("next Saturday"))." 17:00:00";
				
				
				
				if($this->nextMatch['match']['match_date_ts'] > strtotime($close_dt)){
					$close_time = array("datetime"=>$close_dt,
									"ts"=>strtotime($close_dt));
				}else{
					$close_time = array("datetime"=>$previous_close_dt,
									"ts"=>strtotime($previous_close_dt));
				}
				

				
				
				

				$this->closeTime = $close_time;
				$this->set('close_time',$close_time);

				//news ticker
				$this->set('tickers',$this->Ticker->find('all',array('limit'=>5)));
				
			}else{
				$this->set('USER_IS_LOGIN',false);
			}
		}
		
	}
	private function ApiInit(){
		require_once APP . 'Vendor' . DS. 'lib/Predis/Autoloader.php';
		//tell the browser that we're outputing JSON data.
		header('Content-type: application/json');

		$this->loadModel('Apikey');
		$this->loadModel('User');
		$this->loadModel('Team');
		Predis\Autoloader::register();
		$this->redisClient = new Predis\Client(array(
											    'host'     => Configure::read('REDIS.Host'),
											    'port'     => Configure::read('REDIS.Port'),
											    'database' => Configure::read('REDIS.Database')
											));
		$this->layout = 'ajax';
		if($this->request->params['action']!='auth'){
			$access_token = @$_REQUEST['access_token'];
			
			if(!$this->validateAPIAccessToken($access_token)){
				print json_encode(array('status'=>401,'error'=>'invalid access token !'));
				die();
			}
		}
	}
	protected function readAccessToken(){
		$sessionContent = unserialize($this->redisClient->get($this->gameApiAccessToken));
		return $sessionContent;
	}
	private function validateAPIAccessToken($access_token){
		//$this->redisClient->get($access_token);
		if($this->redisClient->ttl($access_token)>0){
			$sessionContent = unserialize($this->redisClient->get($access_token));
			$rs = $this->Apikey->findByApi_key($sessionContent['api_key']);
			if(isset($rs['Apikey']) && $rs['Apikey']['api_key']==$sessionContent['api_key']){
				$this->gameApiAccessToken = $access_token;
				return true;
			}
		}
	}
	public function isUserLogin(){
		if($this->Session->read('Userlogin.is_login')==true){
	  		return true;
	  	}
	}
	public function getUserData(){
		return $this->Session->read('Userlogin.info');
	}
	public function getAccessToken(){
		$access_token = $this->Session->read('access_token');
		
		return $access_token;
	}
	public function initAccessToken(){
		
		if($this->getAccessToken()!=null){
			
			$check = $this->api_call('/checkSession',array('access_token'=>$this->getAccessToken()));
			if($check['status']==0){
				$this->Session->write('access_token',null);
			}
		}
		if($this->Session->read('access_token')==null){
			$ckfile = tempnam ("/tmp", "CURLCOOKIE");
			$response = $this->api_post('/auth',array(),
									$ckfile);
			if(!isset($response['error'])){
				$challenge_code = $response['challenge_code'];
				$request_code = sha1($this->getAPIKey().'|'.$challenge_code.'|'.$this->salt());
				$response = $this->api_post('/auth',
											array('request_code'=>$request_code),
											$ckfile);

				unlink($ckfile);
				$this->Session->write('access_token',$response['access_token']);
				$access_token = $this->Session->read('access_token');
				return $access_token;
				
			}else{
				unlink($ckfile);
				if($this->request->params['controller']!='login'
					&& $this->request->params['action']!='service_unavailable' 
					&& $this->request->params['controller']!='api'){
					$this->redirect('/login/service_unavailable');
				}else{
					//die(json_encode(array('error'=>'service unavailable')));
				}
			}
			return 0;
		}else{

			return $this->Session->read('access_token');
		}
		
	}
	public function getAPIUrl(){
		return Configure::read('API_URL');
	}
	public function getAPIKey(){
		return Configure::read('API_KEY');
	}


	public function api_post($uri,$params,$cookie_file='',$timeout=15){
		App::import("Vendor","common");
		if($this->getAccessToken()!=null){
			$params['access_token'] = $this->getAccessToken();
		}
		$params['api_key'] = $this->getAPIKey();
		$response = json_decode(curlPost($this->getAPIUrl().$uri,$params,$cookie_file,$timeout),true);
		return $response;
	}
	public function api_call($uri,$params,$cookie_file='',$timeout=15){
		App::import("Vendor","common");
		if($this->getAccessToken()!=null){
			$params['access_token'] = $this->getAccessToken();
		}
		$params['api_key'] = $this->getAPIKey();
		$response = json_decode(curlGet($this->getAPIUrl().$uri,$params,$cookie_file,$timeout),true);
		return $response;
	}
	public function salt(){
		return Configure::read('API_SALT');
	}

	public function getBanners($slot,$total=10,$random=false){
		$this->loadModel('Banners');
		if($random){
			$banners = $this->Banners->find('all',array('conditions'=>array('slot'=>$slot),
													'limit'=>$total,
													'order' => 'rand()'));
		}else{
			$banners = $this->Banners->find('all',array('conditions'=>array('slot'=>$slot),
													'limit'=>$total));	
		}
		
		return $banners;
	}
}

