<?php

App::uses('AppController', 'Controller');


class DashboardController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Dashboard';
	public function index(){
		//news tickers
		$this->loadModel('Ticker');
		$tickers = $this->Ticker->find('all');
		$this->set('tickers',$tickers);


		//notifications
		$this->loadModel('Notification');
		$tickers = $this->Notification->find('all');
		$this->set('notifications',$tickers);
	}
	public function add_ticker(){
		$this->loadModel('Ticker');
		$this->Ticker->create();
		$rs = $this->Ticker->save(array(
			'content'=>$this->request->data['content'],
			'url'=>$this->request->data['url']
		));
		if($rs) $this->Session->setFlash("Ticker added !");
		else $this->Session->setFlash("Cannot add news, please try again later !");
		$this->redirect('/dashboard');
	}
	public function delete_tickers(){
		$id = intval($this->request->query['id']);
		$this->loadModel('Ticker');
		$this->Ticker->delete($id);
		$this->redirect('/dashboard');
	}


	public function add_notification(){
		$this->loadModel('Notification');
		$this->Notification->create();
		$rs = $this->Notification->save(array(
			'content'=>$this->request->data['content'],
			'url'=>$this->request->data['url'],
			'dt'=>date("Y-m-d H:i:s")
		));
		if($rs) $this->Session->setFlash("New Notification added !");
		else $this->Session->setFlash("Cannot add notification, please try again later !");
		$this->redirect('/dashboard');
	}
	public function delete_notification(){
		$id = intval($this->request->query['id']);
		$this->loadModel('Notification');
		$this->Notification->delete($id);
		$this->redirect('/dashboard');
	}
}
