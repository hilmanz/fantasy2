/**
api related to match
*/

var crypto = require('crypto');
var fs = require('fs');
var path = require('path');
var xmlparser = require('xml2json');
var async = require('async');
var config = require(path.resolve('./config')).config;
var mysql = require('mysql');
var dateFormat = require('dateformat');
var redis = require('redis');
var formations = require(path.resolve('./libs/game_config')).formations;
function prepareDb(){
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
}

function fixtures(done){
	var conn = prepareDb();
	conn.query("SELECT a.id,\
				a.home_id,b.name as home_name,a.away_id,c.name as away_name,a.home_score,a.away_score,\
				a.matchday,a.period,a.session_id,a.attendance\
				FROM ffgame.game_fixtures a\
				INNER JOIN ffgame.master_team b\
				ON a.home_id = b.uid\
				INNER JOIN ffgame.master_team c\
				ON a.away_id = c.uid\
				ORDER BY a.matchday\
				LIMIT 100;",
				[],
				function(err,match){
					conn.end(function(e){
						done(err,match);						
					});
				});
}

exports.fixtures = fixtures;