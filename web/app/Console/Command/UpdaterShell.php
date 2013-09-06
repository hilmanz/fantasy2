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
        if($user['Team']['id']>0){
          $sql = "
            INSERT INTO points
          (team_id,points)
          VALUES
          ({$user['Team']['id']},{$response['points']})
          ON DUPLICATE KEY UPDATE
          points = VALUES(points);
          ";
          try{
      		  $this->Team->query($sql);
          }catch(Exception $e){
            $this->out('Error : '.$e->getMessage());
          }
      		$this->out("Updating #".$user['Team']['id']." -> ".$response['points']);

          if(is_array($response['game_points'])){
            $this->updating_weekly_stats($user['Team']['id'],
                                      $response['game_points']);
          }
        }
    	}
    }
    private function updating_weekly_stats($team_id,$game_points){
      foreach($game_points as $weekly){
        $sql = "INSERT INTO ffg.weekly_points
                (team_id,game_id,matchday,matchdate,points)
                VALUES
                ({$team_id},'{$weekly['game_id']}',
                  '{$weekly['matchday']}',
                  '{$weekly['match_date']}',{$weekly['total_points']})
                ON DUPLICATE KEY UPDATE
                points = VALUES(points);";

                try{
                   $this->Team->query($sql);
                }catch(Exception $e){
                  $this->out('Error : '.$e->getMessage());
                }
                $this->out("Updating #".$user['Team']['id']." week #".$weekly['matchday'].
                                        "-> ".$weekly['total_points']);
       
      }
    }
    private function recalculate_ranks(){
        $sql = "CALL recalculate_rank;";
        $this->Team->query($sql);
        $rs = $this->Team->query("SELECT game_id,matchday FROM weekly_points 
                            GROUP BY game_id ORDER BY game_id 
                            LIMIT 1000");
        if(is_array($rs)){
          foreach($rs as $r){
            $this->out('recalculating matchday #'.$r['weekly_points']['matchday'].' ranks');
            $sql = "CALL recalculate_weekly_rank('{$r['weekly_points']['game_id']}');";
            $this->Team->query($sql);
          }
        }
    }
}