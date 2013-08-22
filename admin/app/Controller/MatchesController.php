<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');


class MatchesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Matches';

	public function index(){
		$this->loadModel('Matches');
		$this->paginate = array('limit'=>10,
								'order'=>array('Matches.matchdate'=>'asc'));
		$rs = $this->paginate('Matches');
		foreach($rs as $n=>$v){
			$home = $this->Matches->getTeam($v['Matches']['home_team']);
			$rs[$n]['home'] = $home;
			$away = $this->Matches->getTeam($v['Matches']['away_team']);
			$rs[$n]['away'] = $away;

		}
		
		$this->set('data',$rs);
	}
}
