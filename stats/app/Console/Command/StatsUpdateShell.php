<?php
class StatsUpdateShell extends AppShell{
	var $uses = array('Matchinfo');

	public function main() {
       $this->dummy();
       $this->process_queue();
  }
  private function process_queue(){
    $queue = $this->getNewQueue();
   
    if(sizeof($queue)>0){
      $this->addToQueue($queue[0]['a']);
      $this->generateStats($queue[0]['a']);
      $this->flagDone($queue[0]['a']['game_id']);
      $this->process_queue();
    }
  }
  private function getNewQueue(){
    $sql = "SELECT game_id,home_team,away_team 
            FROM ".Configure::read('optadb').".matchinfo a 
            WHERE period = 'FullTime' 
            AND  NOT EXISTS
            (SELECT 1 FROM statsjob_queue b 
            WHERE b.game_id = a.game_id LIMIT 1) LIMIT 1";
    
   
    $rs = $this->Matchinfo->query($sql,false);

    
    return $rs;
  }
  private function addToQueue($queue){
    $sql = "INSERT IGNORE INTO ".Configure::read('optadb').".statsjob_queue
            (game_id,update_time,n_status)
            VALUES
            ('{$queue['game_id']}',NOW(),0)";
    
    $this->Matchinfo->query($sql);
  }
  private function flagDone($game_id){
    $sql = "UPDATE ".Configure::read('optadb').".statsjob_queue
            SET n_status=1
            WHERE game_id = '{$game_id}'";
    
    $this->Matchinfo->query($sql);
  }

  private function generateStats($game){
    $this->out('processing stats for game #'.$game['game_id']);
    $home_id = $game['home_team'];
    $away_id = $game['away_team'];
    
    //home stats
    $this->calculateTeamStats($game['game_id'],$home_id,$away_id);

    //away stats
    $this->calculateTeamStats($game['game_id'],$away_id,$home_id);
  }

  private function calculateTeamStats($game_id,$teamA,$teamB){
    $this->out('generating stats for team#'.$teamA);
    //get players
    $players = $this->getPlayers($game_id,$teamA);
    foreach($players as $player){
      $this->processPlayer($game_id,$player['player_stats']['player_id'],$teamA,$teamB);
    }

  }
  private function processPlayer($game_id,$player_id,$teamA,$teamB){
     $this->out('processing player #'.$player_id);
     $stats = $this->Matchinfo->query("SELECT stats_name,stats_value 
                                      FROM player_stats 
                                      WHERE game_id='{$game_id}' AND 
                                      team_id='{$teamA}' AND 
                                      player_id = '{$player_id}' 
                                      LIMIT 1000;",
                                      false);
     $player_stats = array();
     while(sizeof($stats)>0){
        $st = array_shift($stats);
        $player_stats[$st['player_stats']['stats_name']] = $st['player_stats']['stats_value'];
     }
  }
  private function getPlayers($game_id,$team_id){
    $sql = "SELECT player_id FROM player_stats 
            WHERE game_id='{$game_id}' AND team_id='{$team_id}' 
            GROUP BY player_id LIMIT 100;";
    return $this->Matchinfo->query($sql,false); 
  }
  function most_influence($game_id,$team_id,$player_id,$stats){

  }
  function def_influence($game_id,$team_id,$player_id,$stats){

  }
  function mid_influence($game_id,$team_id,$player_id,$stats){

  }
  function fw_influence($game_id,$team_id,$player_id,$stats){

  }
  function def_score($game_id,$team_id,$player_id,$stats){

  }
  function atk_score($game_id,$team_id,$player_id,$stats){

  }
  function creativity($game_id,$team_id,$player_id,$stats){

  }
  function most_accurate_pass($game_id,$team_id,$player_id,$stats){

  }
  function least_accurate_pass($game_id,$team_id,$player_id,$stats){

  }
  function shoot_accuracy($game_id,$team_id,$player_id,$stats){

  }
  function chance_created($game_id,$team_id,$player_id,$stats){

  }
  function dangerous_pass($game_id,$team_id,$player_id,$stats){

  }
  function assist($game_id,$team_id,$player_id,$stats){

  }
  function best_cross_percentage($game_id,$team_id,$player_id,$stats){

  }
  function worst_cross_percentage($game_id,$team_id,$player_id,$stats){

  }
  function ball_wins($game_id,$team_id,$player_id,$stats){

  }
  function def_fails($game_id,$team_id,$player_id,$stats){

  }
  function liable($game_id,$team_id,$player_id,$stats){

  }
  function gk_score($game_id,$team_id,$player_id,$stats){

  }
  function shot_stopping_percentage($game_id,$team_id,$player_id,$stats){

  }
  function best_at_crosses($game_id,$team_id,$player_id,$stats){

  }
  function one_v_one($game_id,$team_id,$player_id,$stats){

  }
  function deadkick($game_id,$team_id,$player_id,$stats){

  }
  private function dummy(){
    $sql = 'TRUNCATE TABLE statsjob_queue';
    $this->Matchinfo->query($sql);
  }
}