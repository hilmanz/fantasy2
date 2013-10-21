<?php
class UpdaterShell extends AppShell{
	 var $uses = array('Game','Team','User');
	 public function main() {
    	 	$limit = 10;
    	 	$start = 0;

        if($this->week_finished()){
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
           CakeLog::write('updater', 'recalculate ranks');
           $this->recalculate_ranks();
           
           $this->out('done');
           CakeLog::write('updater', 'done');
        }else{
           $this->out('the games has not finished yet');
           CakeLog::write('updater', 'current matchday is still ongoing');
        }
    }
    private function week_finished(){
      return true;
    }
    private function get_points($users){
    	foreach($users as $user){
    		$response = $this->Game->getTeamPoints($user['User']['fb_id']);

    		$response['points'] = intval($response['points']);
        $response['extra_points'] = intval($response['extra_points']);
       print_r($response);

        if($user['Team']['id']>0){
          $sql = "
            INSERT INTO points
          (team_id,points,extra_points)
          VALUES
          ({$user['Team']['id']},{$response['points']},{$response['extra_points']})
          ON DUPLICATE KEY UPDATE
          points = VALUES(points),
          extra_points = VALUES(extra_points);
          ";
          try{
      		  $this->Team->query($sql);
          }catch(Exception $e){
            $this->out('Error : '.$e->getMessage());
          }
      		$this->out("Updating #".$user['Team']['id']." -> ".$response['points']);
          CakeLog::write('updater',"Updating #".$user['Team']['id']." -> ".$response['points']);


          if(is_array($response['game_points'])){
            $this->updating_weekly_stats($user['Team']['id'],
                                      $response['game_points']);
           
          }
        }
    	}
    }
   
    private function updating_weekly_stats($team_id,$game_points){
      foreach($game_points as $weekly){
        $sql = "INSERT INTO weekly_points
                (team_id,game_id,matchday,matchdate,points,extra_points)
                VALUES
                ({$team_id},'{$weekly['game_id']}',
                  '{$weekly['matchday']}',
                  '{$weekly['match_date']}',{$weekly['total_points']},{$weekly['extra_points']})
                ON DUPLICATE KEY UPDATE
                points = VALUES(points),
                extra_points = VALUES(extra_points);";

                try{
                   $this->Team->query($sql);
                }catch(Exception $e){
                  $this->out('Error : '.$e->getMessage());
                }
                $this->out("Updating #".$team_id." week #".$weekly['matchday'].
                                        "-> ".$weekly['total_points']);

                CakeLog::write('updater',"Updating #".$team_id." week #".$weekly['matchday'].
                                        "-> ".$weekly['total_points']);
       
      }
    }
    private function recalculate_ranks(){
        $sql = "CALL recalculate_rank;";
        $this->Team->query($sql);
        $rs = $this->Team->query("SELECT matchday FROM weekly_points 
                            GROUP BY matchday ORDER BY matchday 
                            LIMIT 1000");
        if(is_array($rs)){
          foreach($rs as $r){
            $this->out('recalculating matchday #'.$r['weekly_points']['matchday'].' ranks');
            $sql = "CALL recalculate_weekly_rank('{$r['weekly_points']['matchday']}');";
            $this->Team->query($sql);
          }
        }

        $this->out('recalculating monthly ranks');
        $months = $this->Team->query("SELECT YEAR(matchdate) AS thn,MONTH(matchdate) AS bln
                            FROM weekly_points GROUP BY thn,bln;");
        foreach($months as $m){
          $mth = $m[0]['bln'];
          $yr = $m[0]['thn'];
          $sql = "CALL recalculate_monthly_rank({$mth},{$yr});";
          $this->Team->query($sql);  
        }
    }
}