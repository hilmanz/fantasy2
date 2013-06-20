/**
api related to gameplay
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

function prepareDb(){
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
}

//get current lineup setup
function getLineup(game_team_id,callback){
	conn = prepareDb();
	conn.query("SELECT player_id,position_no \
				FROM ffgame.game_team_lineups \
				WHERE game_team_id=? LIMIT 11",
				[game_team_id],
				function(err,rs){
					console.log(this.sql);
					conn.end(function(e){

						callback(err,rs);	
					});
				});
}
function setLineup(game_team_id,setup,done){
	conn = prepareDb();
	var players = [];
	for(var i in setup){
		players.push(setup[i].player_id);
	}
	async.waterfall(
		[
			function(callback){

				//first, make sure that the players are actually owned by the team
				conn.query("SELECT player_id \
							FROM ffgame.game_team_players \
							WHERE game_team_id = ? AND player_id IN (?) LIMIT 11",
							[game_team_id,players],
							function(err,rs){
								callback(null,rs);
							});
				
			},
			function(players,callback){
				console.log(players);
				if(players.length==11){
					//player exists
					//then remove the existing lineup
					conn.query("DELETE FROM ffgame.game_team_lineups WHERE game_team_id = ? ",
						[game_team_id],function(err,rs){
							console.log(this.sql);
							callback(err,rs);
						});
				}else{
					callback(new Error('one or more player doesnt belong to the team'),[]);
				}
			},
			function(rs,callback){
				var sql = "INSERT INTO ffgame.game_team_lineups\
							(game_team_id,player_id,position_no)\
							VALUES\
							";
				var data = [];
				for(var i in setup){
					if(i>0){
						sql+=',';
					}
					sql+='(?,?,?)';
					data.push(game_team_id);
					data.push(setup[i].player_id);
					data.push(setup[i].no);
				}
				conn.query(sql,data,function(err,rs){
								console.log(this.sql);
								callback(err,rs);
				});
			}
			
		],
		function(err,result){
			conn.end(function(e){
				done(err,result);	
			});
		}
	);

}
//get user's players
function getPlayers(game_team_id,callback){
	conn = prepareDb();
	conn.query("SELECT b.uid,b.name,b.position FROM ffgame.game_team_players a\
				INNER JOIN ffgame.master_player b \
				ON a.player_id = b.uid\
				WHERE game_team_id = ? LIMIT 200;",
				[game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
}
exports.getLineup = getLineup;
exports.setLineup = setLineup;
exports.getPlayers = getPlayers;