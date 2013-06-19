/**
* test script for /libs/api/team.js
*/
var vows = require('vows');
var assert = require('assert');
var path = require('path');
var team = require(path.resolve('./libs/api/team'));
var mysql = require('mysql');
var config = require(path.resolve('./config')).config;



vows.describe('/libs/api/team.js')
.addBatch({
	'testing team module':{
		topic:team,
		'when getTeams called':{
				topic:function(team){
					team.getTeams(this.callback);
				},
				'an array contains a team lists is returned':function(err,team_list){
					assert.isArray(team_list);
				},
				'has 20 teams listed':function(err,team_list){
					assert.equal(team_list.length,20);
				}
			}
		},	
}).run();


