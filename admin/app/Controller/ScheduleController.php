<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');


class ScheduleController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Schedule';

	public function index(){
		$rs = $this->Schedule->query("SELECT Fixture.id,game_id,home_id,away_id,
									period,matchday,home_score,away_score,
									is_processed,attendance,match_date,
									Home.name,Away.name,
									Home.stadium_name 
									FROM ffgame.game_fixtures Fixture
									INNER JOIN ffgame.master_team Home
									ON Fixture.home_id = Home.uid
									INNER JOIN ffgame.master_team Away
									ON Fixture.away_id = Away.uid
									ORDER BY Fixture.matchday ASC,
									Fixture.match_date ASC;");
		$this->set('match',$rs);
	}
}
