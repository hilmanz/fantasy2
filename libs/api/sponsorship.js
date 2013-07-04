/**
api related to sponsorship
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
var sponsorship_chance = require(path.resolve('./libs/game_config')).sponsorship_chance;
function prepareDb(){
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
}
/** get the list of available sponsorships **/

function getAvailableSponsorship(game_team_id,callback){
	conn = prepareDb();
	conn.query("SELECT a.id,name,value,expiry_time,is_available \
				FROM ffgame.game_sponsorships a\
				WHERE a.is_available = 1 AND NOT EXISTS(\
					SELECT 1 FROM ffgame.game_team_sponsors b\
					WHERE b.game_team_id = ? AND b.sponsor_id = a.id LIMIT 1\
				) \
				LIMIT 100;",
				[game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
}
function applySponsorship(game_team_id,sponsor_id,callback){
	var chance = roll();
	var upper = sponsorship_chance * 24;

	if(chance<=upper){
		//got the sponsorship, let's claim it.
		conn = prepareDb();
		async.waterfall([
			function(callback){
				conn.query("SELECT id,name,value,expiry_time,is_available \
				FROM ffgame.game_sponsorships a\
				WHERE a.id = ? AND a.is_available = 1 AND NOT EXISTS(\
					SELECT 1 FROM ffgame.game_team_sponsors b\
					WHERE b.game_team_id = ? AND b.sponsor_id = a.id LIMIT 1\
				) \
				LIMIT 1;",[sponsor_id,game_team_id],function(err,rs){
					
					if(err) console.log(err.message);
					callback(err,rs[0]);
				});
			},
			function(sponsor,callback){
				if(sponsor!=null){
					conn.query("INSERT IGNORE INTO ffgame.game_team_sponsors\
								(game_team_id,sponsor_id,valid_for)\
								VALUES(?,?,?)",
								[game_team_id,sponsor.id,sponsor.expiry_time],
								function(err,result){
									console.log(this.sql);
									callback(err,sponsor,result);
								});
				}else{
					
					callback(new Error('the sponsorship is not available anymore !'),null,null)
				}
			},
			function(sponsor,insertResult,callback){
				conn.query("UPDATE ffgame.game_sponsorships SET is_available=0 WHERE id = ?",
					[sponsor_id],
					function(err,result){
						callback(err,result);
					});
			}
		],
		function(err,result){
			conn.end(function(err){
				if(result){
					callback(err,true);
				}else{
					callback(err,false);
				}
			});
			
		});
	}else{
		callback(null,false);
	}
}
function getActiveSponsors(game_team_id,callback){
	conn = prepareDb();
	conn.query("SELECT b.name,b.value,a.valid_for \
				FROM ffgame.game_team_sponsors a\
				INNER JOIN ffgame.game_sponsorships b\
				ON a.sponsor_id = b.id\
				WHERE a.game_team_id = ?;",
				[game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
}
function roll(){
	var n = Math.random()*24;
	return Math.round(n);

}
exports.getAvailableSponsorship = getAvailableSponsorship;
exports.applySponsorship = applySponsorship;
exports.getActiveSponsors = getActiveSponsors;