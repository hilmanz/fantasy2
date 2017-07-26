<?php
class UpdaterShell extends AppShell{
	 var $uses = array('Game','Team','User');
	 public function main() {
	 	$limit = 10;
	 	$start = 0;
        $this->out('getting points');
       
       	$this->beforeFilter();
       	
        do{
	       	$user = $this->User->find('all',array(
	       		'offset'=>$start,
	       		'limit'=>$limit
	       	));
	       	$this->get_points($user);
	       	$start += $limit;
       	}while(sizeof($user)>0);
       
       $this->out('recalculate ranks');
       $this->recalculate_ranks();
       
       $this->out('done');
    }
    private function get_points($users){
    	foreach($users as $user){
    		$response = $this->Game->getTeamPoints($user['User']['fb_id']);
    		$response['points'] = intval($response['points']);
    		$this->Team->query("
    			INSERT INTO points
				(team_id,points)
				VALUES
				({$user['Team']['id']},{$response['points']})
				ON DUPLICATE KEY UPDATE
				points = VALUES(points);
    		");
    		$this->out("Updating #".$user['Team']['id']." -> ".$response['points']);
    	}
    }
    private function recalculate_ranks(){
        $sql = "CALL recalculate_rank;";
        $this->Team->query($sql);
    }
}