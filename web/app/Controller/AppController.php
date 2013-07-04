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
	public function beforeFilter(){
		$this->set('FB_APP_ID',Configure::read('FB.APP_ID'));
		$this->set('FB_SECRET',Configure::read('FB.SECRET'));
		$this->set('FB_AFTER_LOGIN_URL',Configure::read('FB.AFTER_LOGIN_REDIRECT_URL'));
		$this->set('DOMAIN',Configure::read('DOMAIN'));
		$this->FB_APP_ID = Configure::read('FB.APP_ID');
		$this->FB_SECRET = Configure::read('FB.SECRET');
		$this->initAccessToken();
		if($this->isUserLogin()){
			$this->set('USER_IS_LOGIN',true);
			$this->set('USER_DATA',$this->getUserData);
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
		$ckfile = tempnam ("/tmp", "CURLCOOKIE");
		$response = $this->api_post('/auth',array(),
									$ckfile);

		$challenge_code = $response['challenge_code'];
		$request_code = sha1($this->getAPIKey().'|'.$challenge_code.'|'.$this->salt());
		$response = $this->api_post('/auth',
									array('request_code'=>$request_code),
									$ckfile);

		unlink($ckfile);
		$this->Session->write('access_token',$response['access_token']);
		$access_token = $this->Session->read('access_token');
		return $access_token;
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
}

