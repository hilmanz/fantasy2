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
class PagesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Pages';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 */
	public function beforeFilter(){
		parent::beforeFilter();
		$user = $this->userDetail;
		$this->set('user',$user['User']);
	}
	public function display() {
		$path = func_get_args();

		$count = count($path);
		if (!$count) {

			$this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));

		//what's happening
		$this->loadModel('Info');
		$this->loadModel('User');
		$info = $this->Info->getLatest($this->User,20);
		
		$this->set('info',$info);
		//-->
		
		//Banner nih
		
		$banners = $this->getBanners('FRONTPAGE',10);
		$this->set('banners',$banners);
		
		$small_banners = $this->getBanners('FRONTPAGE_SMALL_MIDDLE',10,true);
		$this->set('small_banner_1',$small_banners);

		$small_banners = $this->getBanners('FRONTPAGE_SMALL_RIGHT',10,true);
		$this->set('small_banner_2',$small_banners);

		//-->

		if($path[0]=='home'&&$this->userDetail['Team']['id']>0){
			if($this->Session->read('pending_redirect')!=null){
				$redirect_url = $this->Session->read('pending_redirect');
				$this->Session->write('pending_redirect',null);
				$this->redirect($redirect_url);
			}else{
				$this->redirect('/manage/team');
			}
			
		}else if($path[0]=='mobile'){
			$this->layout="mobile";
		}
		$this->render(implode('/', $path));
	}
}
