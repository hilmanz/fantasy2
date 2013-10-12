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
}