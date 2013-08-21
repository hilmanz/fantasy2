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
	}
	public function add_ticker(){
		$this->loadModel('Ticker');
		$this->Ticker->create();
		$rs = $this->Ticker->save(array(
			'content'=>$this->request->data['content']
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
}
