<div id="fullcontent">
    <div class="section">
        <div class="col12">
            <div class="widget">
                <div class="widget-title">
                    <h3>BPL Leaderboard </h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th colspan="2"><h4>Team</h4></th>
                            <th><h5>Games Played</h5></th>
                            <th><h5>Games Won</h5></th>
                            <th><h5>Games Drawn</h5></th>
                            <th><h5>Games Lost</h5></th>
                            <th><h5>Goals Scored</h5></th>
                            <th><h5>Goals Conceded</h5></th>
                            <th><h5>Points Earned</h5></th>
                            <th><h5>Top Scorer</h5></th>
                            <th><h5>Top Assist</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                          foreach($data as $d):
                            $stats = $d['stats'];
                        ?>
                          <tr>
                            <td><a href="<?=$this->Html->url('/pages/team/?team_id='.$d['team_id'])?>"><img style="height:46px" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$d['team_id'])?>.png"/></a></td>
                            <td><a href="<?=$this->Html->url('/pages/team/?team_id='.$d['team_id'])?>"><?=h($d['name'])?></a></td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['games_played'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['games_won'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['games_drawn'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['games_lost'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['goals'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['goals_conceded'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                  <?=number_format($stats['points_earned'])?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                 <?php
                                  if(is_array($stats['top_scorer'])):
                                 ?>
                                
                                <?=@$stats['top_scorer']['name']?>
                                <?php endif;?>
                              </a>
                            </td>
                            <td>
                              <a class="red-arrow">
                                 <?php
                                  if(is_array($stats['top_scorer'])):
                                 ?>
                                
                                <?=@$stats['top_assist']['name']?>
                                <?php endif;?>
                              </a>
                            </td>
                          </tr>
                        <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div><!-- end .widget-content -->
                
        </div><!-- end .col4 -->
      </div>
    </div><!-- end .section -->

    
    <div class="section">
        <div class="col12">
          <h3>BEST AND WORST IN THE LEAGUE</h3>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST ATTACKING TEAMS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Goals</h5></th>
                            <th><h5>Chances</h5></th>
                            <th><h5>Attack Effeciency</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data2['best_attacking_teams'] as $stats=>$st):
                          
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['goals']?> 
                          </td>
                          <td class="">
                             <?=$st['chances']?>
                          </td>
                          <td class="">
                             <?=round($st['attacking_effeciency'],1)?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST DEFENSIVE TEAMS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Goals Conceded</h5></th>
                            <th><h5>Chances Conceded</h5></th>
                            <th><h5>Defense Effeciency</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data2['best_defensive_teams'] as $stats=>$st):
                          
                        ?>
                        <tr>
                         
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['goals_conceded']?> 
                          </td>
                          <td class="">
                             <?=$st['chances_conceded']?>
                          </td>
                          <td class="">
                             <?=round($st['def_effeciency'],1)?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>MOST AGGRESIVE TEAMS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Ball Recovery</h5></th>
                            <th><h5>Duels Won</h5></th>
                            <th><h5>Challenge Won Ratio</h5></th>
                            <th><h5>Fouling Ratio</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data2['most_aggresive_teams'] as $stats=>$st):
                          
                        ?>
                        <tr>
                         
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['ball_recovery']?> 
                          </td>
                          <td class="">
                             <?=$st['duels_won']?>
                          </td>
                          <td class="">
                             <?=round($st['challenge_won_ratio']*100,1)?>%
                          </td>
                          <td><?=round($st['fouling_ratio']*100,1)?>%</td>
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
        

            <div class="widget">
                  <div class="widget-title">
                      <h3>MOST ERROR PRONE TEAMS</h3>
                  </div><!-- end .widget-title -->
                  <div class="widget-content">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                      <thead>
                            <tr>
                              <th><h4>Name</h4></th>
                              <th><h5>Error lead to goals</h5></th>
                              <th><h5>Error lead to shots</h5></th>
                              <th><h5>Poor Controls</h5></th>
                              <th><h5>Total Errors</h5></th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach($data2['most_error_prone_teams'] as $stats=>$st):
                            
                          ?>
                          <tr>
                           
                            <td class="">
                               <?=$st['name']?>
                            </td>
                            <td class="">
                               <?=$st['error_lead_to_goals']?> 
                            </td>
                            <td class="">
                               <?=$st['error_lead_to_shots']?>
                            </td>
                            <td class="">
                               <?=round($st['poor_controls'],1)?>
                            </td>
                            <td><?=round($st['total_errors'],1)?></td>
                          </tr>
                        <?php endforeach;?>
                        </tbody>                    
                      </table>
                  </div>
              </div>
         

              <div class="widget">
                  <div class="widget-title">
                      <h3>BEST COUNTER ATTACKING TEAMS</h3>
                  </div><!-- end .widget-title -->
                  <div class="widget-content">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                      <thead>
                            <tr>
                              <th><h4>Name</h4></th>
                              <th><h5>Counter Attack Goals</h5></th>
                              <th><h5>Counter Attack shots</h5></th>
                              <th><h5>Counter Attacks</h5></th>
                              <th><h5>Effeciency</h5></th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach($data2['best_counter_attacking_teams'] as $stats=>$st):
                            
                          ?>
                          <tr>
                           
                            <td class="">
                               <?=$st['name']?>
                            </td>
                            <td class="">
                               <?=$st['counter_attack_goals']?> 
                            </td>
                            <td class="">
                               <?=$st['counter_attack_shots']?>
                            </td>
                            <td class="">
                               <?=round($st['counter_attacks'])?>
                            </td>
                            <td><?=round($st['effeciency']*100,1)?>%</td>
                          </tr>
                        <?php endforeach;?>
                        </tbody>                    
                      </table>
                  </div>
              </div>


              <div class="widget">
                  <div class="widget-title">
                      <h3>STRONGEST TEAMS IN THE AIR</h3>
                  </div><!-- end .widget-title -->
                  <div class="widget-content">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                      <thead>
                            <tr>
                              <th><h4>Name</h4></th>
                              <th><h5>Aerial Duels Won</h5></th>
                              <th><h5>Headers on Goal</h5></th>
                              <th><h5>Crosses Dealt</h5></th>
                              <th><h5>Headed Clearance</h5></th>
                              <th><h5>Effeciency</h5></th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach($data2['strongest_teams_in_the_air'] as $stats=>$st):
                            
                          ?>
                          <tr>
                           
                            <td class="">
                               <?=$st['name']?>
                            </td>
                            <td class="">
                               <?=$st['aerial_duels_won']?> 
                            </td>
                            <td class="">
                               <?=$st['headers_on_goal']?>
                            </td>
                            <td class="">
                               <?=round($st['crosses_dealt'])?>
                            </td>
                            <td class="">
                               <?=round($st['headed_clearance'])?>
                            </td>
                            <td><?=round($st['effeciency']*100,1)?>%</td>
                          </tr>
                        <?php endforeach;?>
                        </tbody>                    
                      </table>
                  </div>
              </div>

        </div>
      </div>



      <div class="section">
        <div class="col12">
          <h3>BEST AND WORST PLAYERS</h3>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST PLAYERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach(@$data3['best_players']['overall'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>WORST PLAYERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['worst_players']['overall'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
             <div class="widget">
                <div class="widget-title">
                    <h3>DANGEROUS PASSER</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['dangerous_passers'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>

            <div class="widget">
                <div class="widget-title">
                    <h3>BEST CROSSER</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['best_crossers'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>

            <div class="widget">
                <div class="widget-title">
                    <h3>SHARPEST SHOOTERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['sharpest_shooters'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST BALL WINNERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['best_ball_winners'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST GOALKEEPER</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['best_goalkeeper'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'])?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>BEST SHOT STOPPERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['best_shotstoppers'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'],1)?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>WEAKEST DEFENDERS</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['weakest_defenders'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'],1)?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>
            <div class="widget">
                <div class="widget-title">
                    <h3>MOST LIABLE</h3>
                </div><!-- end .widget-title -->
                <div class="widget-content">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="blacktable">
                    <thead>
                          <tr>
                            <th><h4>Name</h4></th>
                            <th><h5>Team</h5></th>
                            <th><h5>Position</h5></th>
                            <th><h5>Score</h5></th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        foreach($data3['most_liable'] as $stats=>$st):
                        ?>
                        <tr>
                        
                          <td class="">
                             <?=$st['name']?>
                          </td>
                          <td class="">
                             <?=$st['team_name']?> 
                          </td>
                          <td class="">
                             <?=$st['position']?>
                          </td>
                          <td class="">
                             <?=round($st['total'],1)?>
                          </td>
                          
                        </tr>
                      <?php endforeach;?>
                      </tbody>                    
                    </table>
                </div>
            </div>


        </div>
      </div>

  </div>
</div><!-- end #fullcontent -->