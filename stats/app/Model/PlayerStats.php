<?php
App::uses('AppModel', 'Model');
/**
 * Profile Model
 *
 * @property status_types $status_types
 * @property document_types $document_types
 * @property topics $topics
 */
class PlayerStats extends AppModel {
	public $useTable = 'master_player_summary';
	public $useDbConfig = 'opta';
}