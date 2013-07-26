<div id="myClubPage">
    <?php echo $this->element('infobar'); ?>
    <div class="headbar tr">
        <div class="club-info fl">
            <a class="thumb-club fl"><img src="<?=$this->Html->url('/images/team/'.str_replace(" ","_",strtolower($original['name'])).'.png')?>" /></a>
            <div class="fl club-info-entry">
                <h3 class="clubname"><?=h($club['team_name'])?></h3>
                <h3 class="datemember"><?=h(date("d-m-Y",strtotime($user['register_date'])))?></h3>
            </div>
        </div>
        <div class="club-money fr">
            <h3 class="clubrank">Rank: <?=number_format($USER_RANK)?></h3>
            <h3 class="clubmoney">EUR <?=number_format($team_bugdet)?></h3>
        </div>
    </div><!-- end .headbar -->
    <div id="thecontent">
        <div class="content">
            <div id="clubtabs">
              <ul>
                <li><a href="#tabs-Info">Info</a></li>
                <li><a href="#tabs-Money">Keuangan</a></li>
                <li><a href="#tabs-Players">Pemain</a></li>
                <li><a href="#tabs-Staff">Staff</a></li>
              </ul>
              <div id="tabs-Info">
                <div class="avatar-big fl">
                    <img src="http://graph.facebook.com/<?=$user['fb_id']?>/picture" />
                </div>
                <div class="user-details fl">
                    <h3 class="username"><?=h($user['name'])?></h3>
                    <h3 class="useremail"><?=h($user['email'])?></h3>
                    <h3 class="usercity"><?=h($user['location'])?></h3>
                </div><!-- end .row -->
              </div><!-- end #Info -->
              <div id="tabs-Money">
                    <table cellspacing="0" cellpadding="0" width="100%">
                      <tr class="head">
                        <td colspan="5">BALANCE</td>
                        <td align="right">EUR 100,000,000</td>
                      </tr>
                      <tr>
                        <td>Tickets</td>
                        <td></td>
                        <td></td>
                        <td>Matches</td>
                        <td>Total Earnings</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Tickets Sold</td>
                        <td align="right"></td>
                        <td align="right"></td>
                        <td align="right"><?=@$finance['total_matches']?></td>
                        <td align="right">EUR <?=number_format(@$finance['tickets_sold'])?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Additional Income</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Commercial Director Bonus</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['commercial_director_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Marketing Manager Bonus</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['marketing_manager_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Public Relations Bonus</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['public_relation_officer_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Sponsors</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['sponsorship']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Bonuses</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Wins</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['win_bonus']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Total Earnings</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['total_earnings']))?></td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Operating Costs</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">EUR <?=number_format(abs(@$finance['operating_cost']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="6">Salaries</td>
                      </tr>
                      
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Commercial Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['commercial_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Marketing Manager</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['marketing_manager']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Public Relations</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['public_relation_officer']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Head of Security</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['head_of_security']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Footbal Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['football_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Chief Scout</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['chief_scout']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>general Scout</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['general_scout']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Finance Director</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['finance_director']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Tax</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['tax_consultant']))?></td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Accountant</td>
                        <td>&nbsp;</td>
                        <td align="right"></td>
                        <td align="right">EUR <?=number_format(abs(@$finance['accountant']))?></td>
                        <td align="right"></td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Transfer Balance</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Running Balance</td>
                        <td align="right">EUR <?=number_format(@$finance['budget'])?></td>
                    </tr>
                   </table>
              </div><!-- end #tabs-Keuagan -->
              <div id="tabs-Players">
                <div class="player-list">
                  <?php foreach($players as $player):?>
                  <?php
                    switch($player['position']){
                      case 'Goalkeeper':
                        $player_pos = "G";
                        $color = "grey";
                      break;
                      case 'Midfielder':
                        $player_pos = "M";
                        $color = "yellow";
                      break;
                      case 'Forward':
                        $player_pos = "F";
                        $color  = "red";
                      break;
                      default:
                        $player_pos = "D";
                        $color = "blue";
                      break;
                    }
                  ?>
                    <div class="jersey-player ">
                      <a href="<?=$this->Html->url('/manage/player/'.$player['uid'])?>">
                        <div class="jersey j-<?=$color?>"><?=$player_pos?></div>
                        <span class="player-name"><?=h($player['name'])?></span>
                      </a>
                    </div><!-- end .jersey-player -->
                  <?php endforeach;?>
                </div><!-- end .player-list -->
              </div><!-- end #tabs-Squad -->
              <div id="tabs-Staff">
                    <div class="staff-list">
                      <?php
                                foreach($staffs as $official):
                          ?>
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="<?=$this->Html->url('/content/thumb/default_avatar.png')?>" />
                                </div><!-- end .avatar-big -->
                                <p><?=h($official['name'])?></p>
                                <div>
                                    $<?=number_format($official['salary'])?> / Week
                                </div>
                            </div><!-- end .thumbStaff -->
                            <?php
                                endforeach;
                            ?>
                    </div><!-- end .staff-list -->
                     <div class="row">
                        <a href="<?=$this->Html->url('/manage/hiring_staff')?>" class="button">
                          Manage Staffs</a>
                    </div>
              </div><!-- end #tabs-Staff -->
            </div><!-- end #clubtabs -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #myClubPage -->