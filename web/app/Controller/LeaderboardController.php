<?php
/**
 * Leaderboard Controller

 */
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class LeaderboardController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Leaderboard';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	protected $userData;

	public function beforeFilter(){
		parent::beforeFilter();
		
		if(!$this->hasTeam()){
			$this->redirect('/login/expired');
		}
		$user = $this->userDetail;
		$this->set('user',$user['User']);
	}
	public function hasTeam(){
		$userData = $this->getUserData();
		if(is_array($userData['team'])){
			return true;
		}
	}
	private function getTier($myRank){
		$ranks = $this->Point->query("SELECT MAX(rank) as total FROM points a
									INNER JOIN teams b
									ON a.team_id = b.id;");
		
		$max_rank = intval($ranks[0][0]['total']);
		$q1 = ceil(0.25 * $max_rank);
		$q2 = ceil(0.5 * $max_rank);
		$q3 = ceil(0.75 * $max_rank);
		
		if($myRank <= $q1){
			return 1;
		}else if($myRank > $q1 && $myRank <= $q2){
			return 2;
		}else if($myRank > $q2 && $myRank <= $q3){
			return 3;
		}else{
			return 4;
		}
		
	}
	public function index(){
		$this->loadModel("Point");
	    $this->loadModel('User');
	    $this->loadModel('Weekly_point');
	    $this->loadModel('Weekly_rank');

	    $next_match = $this->Game->getNextMatch($this->userData['team']['team_id']);
	    $this->set('next_match',$next_match);
		//define week.
		if(!isset($this->request->query['week'])){
		    if($next_match['match']['matchday']<=1){
		    	$matchday = 1;
		    }else{
		    	$matchday = $next_match['match']['matchday']-1;
		    }
		}else{
			$matchday = intval($this->request->query['week']);
		}
		$this->paginate = array(
			'conditions'=>array('matchday'=>$matchday),
	        'limit' => 100,
	        'order' => array(
	            'Weekly_point.points' => 'desc'
	        )
	    );


	 
	    $rs = $this->paginate('Weekly_point');


	    $game_id = '';
	    foreach($rs as $n=>$r){
	    	$rs[$n]['Point'] = $rs[$n]['Weekly_point'];
	    	//get manager's name
	    	$manager = $this->User->findById($r['Team']['user_id']);
	    	$rs[$n]['Manager'] = @$manager['User'];

	    }
	    
	    
	    $this->set('team',$rs);
	    

	    $myRank = $this->Weekly_rank->find('first',
	    									array('conditions'=>
	    											array('team_id'=>$this->userDetail['Team']['id'],
	    												'matchday'=>$matchday)));
	    
	    $this->set('matchday',$matchday);
	    $this->set('rank',$myRank['Weekly_rank']['rank']);

	    $this->set('tier',$this->getTier($myRank['Weekly_rank']['rank']));
	}
	public function monthly(){
		$this->loadModel("Point");
	    $this->loadModel('User');
	    $this->loadModel('Weekly_point');
	    $this->loadModel('Monthly_point');
	   
	   	if(isset($this->request->query['m']) && isset($this->request->query['y'])){
	   		$current_month = intval($this->request->query['m']);
	  		$current_year = intval($this->request->query['y']);
	   	}else{
	   		$current_month = date('m');
	  		$current_year = date('Y');	
	  		$check_count = $this->Weekly_point->find('count',array(
	  			'conditions'=>array('MONTH(matchdate)'=>$current_month,
	  								'YEAR(matchdate)'=>$current_year)
	  		));
		  	//kalau bulan ini tidak ada data, kita pakai data bulan lalu.
		  	if($check_count==0){
		  		$current_month -= 1;
		  		if($current_month==0){
		  			$current_month = 12;
		  			$current_year -= 1;
		  		}
		  	}
	   	}
	  	

	  	

	  	$available_months = $this->Monthly_point->query("SELECT 
														bln,
														thn 
														FROM monthly_points
														GROUP BY thn,bln;");
	  	

	  	$this->set('available_months',$available_months);

	  	/*
	  	$this->paginate = array(
	  		'fields'=>array('Weekly_point.team_id','SUM(Weekly_point.points) as points','Team.*'),
			'conditions'=>array('MONTH(matchdate)'=>$current_month,
	  							'YEAR(matchdate)'=>$current_year),
	        'limit' => 100,
	        'group' => 'Weekly_point.team_id',
	        'order' => array(
	            'Weekly_point.points' => 'desc'
	        )
	    );
	    */
		$this->paginate = array(
			'conditions'=>array('bln'=>$current_month,
	  							'thn'=>$current_year),
	        'limit' => 100,
	        'order' => array(
	            'Monthly_point.points' => 'desc'
	        )
	    );
	  	$rs =  $this->paginate('Monthly_point');
	  	

	  	
	  
	  	foreach($rs as $n=>$r){
	    	$rs[$n]['Point'] = $rs[$n]['Monthly_point'];
	    	
	    	unset($rs[$n]['Monthly_point']);
	    	
	    	//get manager's name
	    	$manager = $this->User->findById($r['Team']['user_id']);
	    	$rs[$n]['Manager'] = @$manager['User'];
	    }

	    $myRank = $this->Monthly_point->find('first',
	    										array('conditions'=>array(	
	    													'Monthly_point.team_id'=>$this->userDetail['Team']['id'],
	    													'bln'=>$current_month,
	  														'thn'=>$current_year),

	    										)
	    									);
	    
	    $this->set('team',$rs);
	    $this->set('monthly',true);
	    $this->set('current_month',intval($current_month));
	    $this->set('current_year',intval($current_year));
	    $this->set('rank',$myRank['Monthly_point']['rank']);
	  	$this->set('tier',$this->getTier($myRank['Monthly_point']['rank']));

	}
	public function overall(){
		$this->loadModel("Point");
	    $this->loadModel('User');
	    $this->paginate = array(
	        'limit' => 100,
	        'order' => array(
	            'Point.points' => 'desc'
	        )
	    );
	    $rs = $this->paginate('Point');
	    foreach($rs as $n=>$r){
	    	//get manager's name
	    	$manager = $this->User->findById($r['Team']['user_id']);
	    	$rs[$n]['Manager'] = @$manager['User'];
	    }
	    $this->set('team',$rs);
	    $this->set('rank',$this->userRank);
	    $this->set('tier',$this->getTier($this->userRank));
	    $this->set('overall',true);
	}

	public function error(){
		$this->render('error');
	}
	public function team_error(){
		$this->set('error_type','team');
		$this->render('error');
	}
	public function success(){
		$this->render('success');
	}
}
