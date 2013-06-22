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
var formations = require(path.resolve('./libs/game_config')).formations;


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
	conn.query("SELECT a.player_id,a.position_no,b.name,b.position \
				FROM ffgame.game_team_lineups a\
				INNER JOIN ffgame.master_player b\
				ON a.player_id = b.uid\
				WHERE a.game_team_id=? LIMIT 11",
				[game_team_id],
				function(err,rs){
					
					conn.end(function(e){

						callback(err,rs);	
					});
				});
}
function setLineup(game_team_id,setup,formation,done){
	conn = prepareDb();
	var players = [];
	for(var i in setup){
		players.push(setup[i].player_id);
	}
	async.waterfall(
		[
			function(callback){

				//first, make sure that the players are actually owned by the team
				conn.query("SELECT player_id,b.position \
							FROM ffgame.game_team_players a \
							INNER JOIN ffgame.master_player b\
							ON a.player_id = b.uid\
							WHERE a.game_team_id = ? AND a.player_id IN (?) LIMIT 11",
							[game_team_id,players],
							function(err,rs){
								console.log(this.sql);
								console.log(rs);
								callback(null,rs);
							});
				
			},
			function(players,callback){
				if(players.length==11){
					//make sure that the composition is correct
					//like position 1 must be placed by goalkeeper.
					//the rest is optional
					if(position_valid(players,setup,formation)){
						//player exists
						//then remove the existing lineup
						conn.query("DELETE FROM ffgame.game_team_lineups WHERE game_team_id = ? ",
							[game_team_id],function(err,rs){
								callback(err,rs);
							});
					}else{
						callback(new Error('invalid player positions'),[]);
					}
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
//check if the player's formation is valid
//saat ini kita cuman memastikan bahwa nomor 1 itu harus kiper.
//nomor yg lain mau penyerang semua sih gak masalah.
function position_valid(players,setup,formation){
	console.log(players);
	
	var my_formation = formations[formation];
	
	for(var i in setup){
		for(var j in players){
			if(players[j].player_id == setup[i].player_id){
				console.log(setup[i].no,' ',players[j].position,' vs ',my_formation[setup[i].no]);
				if(players[j].position != my_formation[setup[i].no]){
					return false;
				}
				break;
			}
		}
	}
	return true;
}
//get user's players
function getPlayers(game_team_id,callback){
	conn = prepareDb();
	conn.query("SELECT b.uid,b.name,b.position FROM ffgame.game_team_players a\
				INNER JOIN ffgame.master_player b \
				ON a.player_id = b.uid\
				WHERE game_team_id = ? ORDER BY b.name ASC \
				LIMIT 200;",
				[game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
}

//get user's budget
function getBudget(game_team_id,callback){
	sql = "SELECT SUM(initial_budget+total) AS budget \
			FROM (SELECT budget AS initial_budget,0 AS total FROM ffgame.game_team_purse WHERE game_team_id = ?\
			UNION ALL\
			SELECT 0,SUM(amount) AS total FROM ffgame.game_team_expenditures WHERE game_team_id = ?) a;";
	conn = prepareDb();
	conn.query(sql,
				[game_team_id,game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
}



exports.getLineup = getLineup;
exports.setLineup = setLineup;
exports.getPlayers = getPlayers;
exports.getBudget = getBudget;
exports.match = require(path.resolve('./libs/api/match'));
exports.officials = require(path.resolve('./libs/api/officials'));
exports.sponsorship = require(path.resolve('./libs/api/sponsorship'));