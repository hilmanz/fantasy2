<?php
App::uses('AppModel', 'Model');
/**
 * Game Model
 *
 * @property status_types $status_types
 * @property document_types $document_types
 * @property topics $topics
 */
class Game extends AppModel {
	public $useTable = false; //kita gak pake table database, karena nembak API langsung.

	public function getTeam($fb_id){
		$response = $this->api_call('/team/get/'.$fb_id,array());
		if(!isset($response['error'])){
			return $response;	
		}
		return null;
	}
	public function getBudget($team_id){
		$response = $this->api_call('/team/budget/'.$team_id,array());
		return $response['budget'];
	}

	public function setProfile($data){ /*save profile*/
		$response = $this->api_post('/user/register',$data);
		return $response;
	}

	public function getTeams(){ /*load team list*/
		$response = $this->api_call('/teams');
		return $response;
	}
	
	public function getMasterTeam($team_id){
		$response = $this->api_call('/players/'.$team_id);
		return $response;
	}
	public function create_team($data){
		$response = $this->api_post('/create_team',$data);
		return $response;
	}
	public function get_team_players($fb_id){
		$response = $this->api_call('/team/get/'.$fb_id);
		if(intval($response['id'])>0){
			$team_id = intval($response['id']);
			$team = $this->api_call('/team/list/'.$team_id);
			if(sizeof($team)>0){
				return $team;
			}
		}
	}
}