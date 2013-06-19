/**
* an App for adding dummy teams
* 
*/

var fs = require('fs');
var path = require('path');
var mysql = require('mysql');
var dateformat = require('dateformat');
var config = require('./config').config;
var async = require('async');
var pool = mysql.createPool({
	host: config.database.host,
	user: config.database.username,
	password: config.database.password,
	multipleStatements: true
});

var user_id = 10;

async.waterfall(
	[
		function(callback){
			resetInserts(function(err){
				callback(null);
			});
		},
		function(callback){
			getMasterTeams(function(err,teams){
				callback(err,teams);
			});
		},
		function(teams,callback){
			createTeams(teams,function(err,teams){
					callback(null,teams);
				});
		}
	],
	function(err,result){
		pool.end(function(err){
			console.log('done');
		});
	}
);	
function resetInserts(done){
	pool.getConnection(function(err,conn){
		conn.query("DELETE FROM ffgame.game_teams WHERE id > 1;\
					DELETE FROM ffgame.game_team_players WHERE game_team_id > 1;\
					DELETE FROM ffgame.game_team_officials WHERE game_team_id > 1;",[],
				function(err,teams){
					conn.end(function(err){
						done(err);
					});
				});
	});
}
function getMasterTeams(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame.master_team;",[],
				function(err,teams){
					conn.end(function(err){
						done(err,teams);
					});
				});
	});
	
}

function createTeams(teams,done){
	pool.getConnection(function(err,conn){
		async.each(teams,function(team,team_done){
			async.waterfall([
				function(callback){
					user_id++;
					conn.query("INSERT INTO ffgame.game_teams\
						(user_id,team_id,created_date,n_status)\
						VALUES(?,?,NOW(),1);",[user_id,team.uid],
						function(err,rs){
							console.log(this.sql);
							if(err) console.log(err.message);
							console.log(rs);
							callback(null,rs);
						});
				},
				function(result,callback){
					console.log(result);
					if(result!=null){
						if(result.insertId!=null){
						//insert players
						conn.query("INSERT INTO ffgame.game_team_players\
									(game_team_id,player_id)\
									SELECT ? AS game_team_id,uid AS player_id \
									FROM ffgame.master_player WHERE team_id=?;",
									[result.insertId,team.uid],
									function(err,rs){
										console.log(this.sql);
										callback(null,result);
									});
						}else{
							callback(null,null);
						}
					}else{
						callback(null,null);
					}
				},
				function(result,callback){
					if(result!=null){
						//insert officials
						conn.query(
							"INSERT INTO ffgame.game_team_officials\
							(game_team_id,official_id,recruit_date)\
							SELECT ? AS game_team_id,id AS official_id,NOW() AS recruit_date \
							FROM ffgame.game_officials;",
							[result.insertId],
							function(err,rs){
								console.log(this.sql);
								callback(null,result);
							});
					}else{
						callback(null,result);
					}
				}
			],
			function(err,rs){

				team_done();
			})
			
		},function(err){
			conn.end(function(err){
				done(err,teams);
			});
			
		});
	});
	
	
}