<?php
App::uses('AppModel', 'Model');
/**
 * Profile Model
 *
 * @property status_types $status_types
 * @property document_types $document_types
 * @property topics $topics
 */
class Matchinfo extends AppModel {
	public $useTable = 'matchinfo';
}