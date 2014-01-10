<?php
App::uses('Component', 'Controller');
class ActivityLogComponent extends Component {
	private $model;
	public function initialize(Controller $controller){
		$controller->loadModel('ActivityLogs');
      
        $this->model = $controller->ActivityLogs;
	}
    public function writeLog($user_id, $activity) {

        $this->model->create();
        $this->model->save(array(
        		'user_id'=>$user_id,
        		'log_dt'=>date("Y-m-d H:i:s"),
        		'log_type'=>$activity
        	));
    }

    // logTime is used for logging the time spent in the website
    // there would be 2 variable to track : 
    // a. initial_ts (unix timestamp)
    // b. last_ts (unix timestamp)
    // time_spent = last_ts - initial_ts
    // initial_ts is resetted when 'LOGIN' activity captured.

    public function logTime($user_id,
                            $reset_time=false){
        $initial_ts = Configure::read('track_time_initial_ts');
        $last_ts = Configure::read('track_time_initial_ts');
        if($reset_time){
            //we save the time first to database ?
            $diff_ts = $last_ts - $initial_ts;
            if($diff_ts > 0){
                //save the time difference to database
                $this->model->query("
                    
                ");
            }
            Configure::write('track_time_initial_ts',time());
        }else{
            Configure::write('track_time_current_ts',time());
        }
    }
}