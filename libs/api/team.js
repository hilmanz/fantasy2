/**
* API for team
*/
var fs = require('fs');
var path = require('path');
var xmlparser = require('xml2json');
var async = require('async');
var config = require(path.resolve('./config')).config;
var mysql = require('mysql');

function prepareDb(){
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
}

function getTeams(callback){
	
	conn = prepareDb();
	conn.query("SELECT uid,name FROM ffgame.master_team ORDER BY name LIMIT 100;",
	[],function(err,team){
		conn.end(function(err){
			callback(err,team);
		});
	});
	

}

//make it accessable from anywhere
exports.getTeams = getTeams;