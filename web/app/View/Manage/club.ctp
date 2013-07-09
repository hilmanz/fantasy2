<div id="myClubPage">
    <div id="info-bar" class="tr2">
        <h4 class="date-now fl">14 june 2013</h4>
        <div id="newsticker">
              <ul class="slides">
                <li class="newsticker-entry">
                    <h3><a href="#n1">Lorem ipsum FC VS Dolor</a></h3>
                </li><!-- end .newsticker-entry -->
                <li class="newsticker-entry">
                    <h3><a href="#n1">2 Goals Sit amet, consectetuer</a></h3>
                </li><!-- end .newsticker-entry -->
                <li class="newsticker-entry">
                    <h3><a href="#n1">Sdipiscing elit VS Rincidunt Team 3-0,</a></h3>
                </li><!-- end .newsticker-entry -->
                <li class="newsticker-entry">
                    <h3><a href="#n1">Sed diam nonummy nibh euismod tincidunt ut</a></h3>
                </li><!-- end .newsticker-entry -->
              </ul><!-- end #newsticker -->
        </div>
        <h4 class="fr"><span class="yellow">6</span> DAYS <span class="yellow">0</span> HOUR <span class="yellow">0</span> MINUTE to close</h4>
    </div><!-- end #info-bar -->
    <div class="headbar tr">
        <div class="club-info fl">
            <a class="thumb-club fl"><img src="<?=$this->Html->url('/images/team/logo1.png')?>" /></a>
            <div class="fl club-info-entry">
                <h3 class="clubname"><?=h($club['team_name'])?></h3>
                <h3 class="datemember"><?=h(date("d-m-Y",strtotime($user['register_date'])))?></h3>
            </div>
        </div>
        <div class="club-money fr">
            <h3 class="clubrank">Rank: 343</h3>
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
                        <td align="right">$25,000,000</td>
                      </tr>
                      <tr>
                        <td>Earnings</td>
                        <td>Ticket Price</td>
                        <td>Attendance</td>
                        <td>Matches</td>
                        <td>Total Earnings</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td align="right">$50</td>
                        <td align="right">25000</td>
                        <td align="right">19</td>
                        <td align="right">$23,750,000</td>
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
                        <td>MD</td>
                        <td align="right">$3,562,500</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>MM</td>
                        <td align="right">$2,375,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>PR</td>
                        <td align="right">$1,187,500</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Sponsors</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">$5,000,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Bonuses</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Wins</td>
                        <td align="right">$950,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>User Points</td>
                        <td align="right">$950,000</td>
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
                        <td align="right">$37,775,000</td>
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
                        <td align="right">$9,500,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>Less Savings</td>
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
                        <td>FD</td>
                        <td align="right">$1,900,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>TX C</td>
                        <td align="right">$1,425,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>ACC</td>
                        <td align="right">$475,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>NETT Operating Costs</td>
                        <td>&nbsp;</td>
                        <td align="right">$5,700,000</td>
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
                        <td>STARS</td>
                        <td align="right">3</td>
                        <td align="right">150000</td>
                        <td align="right">$17,100,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>TOP</td>
                        <td align="right">4</td>
                        <td align="right">100000</td>
                        <td align="right">$15,200,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>AVERAGE</td>
                        <td align="right">4</td>
                        <td align="right">50000</td>
                        <td align="right">$7,600,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>JUNIORS</td>
                        <td align="right">8</td>
                        <td align="right">25000</td>
                        <td align="right">$7,600,000</td>
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
                      <tr>
                        <td>&nbsp;</td>
                        <td>CD</td>
                        <td>&nbsp;</td>
                        <td align="right">5500</td>
                        <td align="right">$209,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>MM</td>
                        <td>&nbsp;</td>
                        <td align="right">2500</td>
                        <td align="right">$95,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>PR</td>
                        <td>&nbsp;</td>
                        <td align="right">2000</td>
                        <td align="right">$76,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Security</td>
                        <td>&nbsp;</td>
                        <td align="right">2000</td>
                        <td align="right">$76,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Footbal Director</td>
                        <td>&nbsp;</td>
                        <td align="right">15000</td>
                        <td align="right">$570,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Chief Scout</td>
                        <td>&nbsp;</td>
                        <td align="right">7000</td>
                        <td align="right">$266,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>general Scout</td>
                        <td>&nbsp;</td>
                        <td align="right">1500</td>
                        <td align="right">$57,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>FD</td>
                        <td>&nbsp;</td>
                        <td align="right">5000</td>
                        <td align="right">$190,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Tax</td>
                        <td>&nbsp;</td>
                        <td align="right">2000</td>
                        <td align="right">$76,000</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>Accountant</td>
                        <td>&nbsp;</td>
                        <td align="right">2000</td>
                        <td align="right">$76,000</td>
                        <td align="right">$49,191,000</td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Transfer Balance</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr class="head">
                        <td colspan="5">Running Balance</td>
                        <td align="right">$7,884,000</td>
                    </tr>
                   </table>
              </div><!-- end #tabs-Keuagan -->
              <div id="tabs-Players">
                <div class="player-list">
                    <div class="jersey-player ">
                        <div class="jersey j-red">5</div>
                        <span class="player-name">Ashley</span>
                    </div><!-- end .jersey-player -->
                    <div class="jersey-player ">
                        <div class="jersey j-red">6</div>
                        <span class="player-name">Smalling</span>
                    </div><!-- end .jersey-player -->
                    <div class="jersey-player ">
                        <div class="jersey j-red">3</div>
                        <span class="player-name">P.Maldini</span>
                    </div><!-- end .jersey- -->
                    <div class="jersey-player">
                        <div class="jersey j-red">1</div>
                        <span class="player-name">DIDA</span>
                    </div><!-- end .jersey-player -->
                </div><!-- end .player-list -->
              </div><!-- end #tabs-Squad -->
              <div id="tabs-Staff">
                    <div class="col2 staff-list">
                        <div class="thumbStaff">
                            <div class="avatar-big">
                                <img src="content/thumb/default_avatar.png" />
                            </div><!-- end .avatar-big -->
                            <h3>Marketing Staff</h3>
                        </div><!-- end .thumbStaff -->
                    </div><!-- end .staff-list -->              
              </div><!-- end #tabs-Staff -->
            </div><!-- end #clubtabs -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #myClubPage -->