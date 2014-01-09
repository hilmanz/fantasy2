<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');


class AnalyticsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Analytics';

	public function beforeFilter(){
		parent::beforeFilter();
		$this->loadModel('Analytics');
	}
	public function index(){
		
	}

	//retrieving unique user daily stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_daily(){
		$rs = $this->Analytics->unique_user_daily();
		
		for($i=0;$i<sizeof($rs);$i++){
			$categories[] = $rs[$i]['the_date'];
			$xValue[] = intval($rs[$i]['total']);
		}
		$this->layout = "ajax";
		$this->set('response',
					array('status'=>1,
						  'data'=>array('categories'=>$categories,
						  				'values'=>$xValue)));
		$this->render('response');
	}
	
	//retrieving unique user monthly stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_weekly(){
		$rs = $this->Analytics->unique_user_weekly();
		
		for($i=0;$i<sizeof($rs);$i++){
			$categories[] = $rs[$i]['the_date'].'('.$rs[$i]['the_week'].')';
			$xValue[] = intval($rs[$i]['total']);
		}

		$this->layout = "ajax";
		$this->set('response',
					array('status'=>1,
						  'data'=>array('categories'=>$categories,
						  				'values'=>$xValue)));
		$this->render('response');
	}
	//retrieving unique user monthly stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_monthly(){
		$rs = $this->Analytics->unique_user_monthly();
		$month = array('','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		for($i=0;$i<sizeof($rs);$i++){
			$categories[] = $month[$rs[$i]['the_month']].'/'.$rs[$i]['the_year'];
			$xValue[] = intval($rs[$i]['total']);
		}
		$this->layout = "ajax";
		$this->set('response',
					array('status'=>1,
						  'data'=>array('categories'=>$categories,
						  				'values'=>$xValue)));
		$this->render('response');
	}
}
