<?php
App::uses('AppModel', 'Model');
/**
 * Matches Model

 */
class Analytics extends AppModel {
	public $useTable = false; //kita gak pake table database, karena nembak API langsung.

	//retrieving unique user daily stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_daily(){
		$sql = "
				SELECT id,the_date,the_day,COUNT(user_id) AS unique_user
				FROM (
				SELECT id,DATE(log_dt) the_date,DAYOFYEAR(log_dt) AS the_day, 
				user_id
				FROM 
				activity_logs
				GROUP BY DAYOFYEAR(log_dt),user_id) a 
				GROUP BY the_day
				ORDER BY id DESC LIMIT 30;";
		$rs = $this->query($sql);

		$results = array();
		for($i=0;$i<sizeof($rs);$i++){
			$p = $rs[$i]['a'];
			$p['total'] = $rs[$i][0]['unique_user'];
			$results[] = $p;
		}
		$results = array_reverse($results);
		return $results;
	}
	
	//retrieving unique user monthly stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_monthly(){
		$sql = "
				SELECT id,the_month,the_year,COUNT(user_id) AS unique_user
				FROM (
				SELECT id,MONTH(log_dt) AS the_month, YEAR(log_dt) AS the_year,
				user_id
				FROM 
				activity_logs
				GROUP BY MONTH(log_dt),user_id) a 
				GROUP BY the_month
				ORDER BY id DESC LIMIT 12";
		$rs = $this->query($sql);

		$results = array();
		for($i=0;$i<sizeof($rs);$i++){
			$p = $rs[$i]['a'];
			$p['total'] = $rs[$i][0]['unique_user'];
			$results[] = $p;
		}

		$results = array_reverse($results);
		return $results;
	}

	//retrieving unique user weekly stats.
	//we can get the data from fantasy.activity_logs
	public function unique_user_weekly(){
		$sql = "
				SELECT id,DATE_ADD(MAKEDATE(the_year, 1), INTERVAL the_week WEEK) AS the_date,the_week,the_year,COUNT(user_id) AS unique_user
				FROM (
				SELECT id,WEEK(log_dt) AS the_week, YEAR(log_dt) AS the_year,
				user_id
				FROM 
				activity_logs
				GROUP BY WEEK(log_dt),user_id) a 
				GROUP BY the_week ORDER BY id DESC LIMIT 12;";
		$rs = $this->query($sql);

		$results = array();
		for($i=0;$i<sizeof($rs);$i++){
			$p = $rs[$i]['a'];
			$p['total'] = $rs[$i][0]['unique_user'];
			$p['the_date'] = $rs[$i][0]['the_date'];
			$results[] = $p;
		}
		
		$results = array_reverse($results);
		return $results;
	}
}