<?php
App::uses('AppModel', 'Model');
/**
 * Matches Model

 */
class MasterMatchday extends AppModel {
	public $useTable = 'master_matchdays'; //kita gak pake table database, karena nembak API langsung.
	public $useDbConfig = 'ffgame';

	
}