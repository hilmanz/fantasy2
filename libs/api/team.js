/**
* API for team
*/
var fs = require('fs');
var path = require('path');
var xmlparser = require('xml2json');
var async = require('async');
var config = require(path.resolve('./config')).config;
var mysql = require('mysql');
var initial_money = require(path.resolve('./libs/game_config')).initial_money;
var pool = {};
function prepareDb(callback){

	pool.getConnection(function(err,conn){
		callback(conn);
	});
	/*
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
	*/
}

/** get master team data**/
function getTeams(callback){
	
	prepareDb(function(conn){
		conn.query("SELECT uid,name FROM ffgame.master_team ORDER BY name LIMIT 100;",
			[],function(err,team){
				conn.end(function(err){
					callback(err,team);
				});
			});
	});
	
	

}
/** get master player data **/
function getPlayers(team_uid,callback){
	prepareDb(function(conn){
		async.waterfall([
				function(callback){
					conn.query("SELECT uid,name,birth_date,real_position,known_name,join_date,\
					position,country,salary,transfer_value \
					FROM ffgame.master_player \
					WHERE team_id=? ORDER BY last_name ASC,position ASC LIMIT 100",
					[team_uid],
					function(err,players){
						callback(err,players);
					});
				},
				function(players,callback){
					var player_with_stats = [];
					async.eachSeries(players,function(player,next){
						conn.query("SELECT SUM(points) AS points,AVG(performance) AS performance \
									FROM ffgame_stats.master_player_performance \
									WHERE \
									player_id = ? \
									AND game_id IS NOT NULL;",
									[player.uid],
									function(err,rs){
										if(rs!=null){
											player.stats = rs[0];	
										}else{
											player.stats = {};
										}
										player_with_stats.push(player);
										next();
									}
								);
					},function(err){

						callback(err,player_with_stats);
					});
				}
			],
			function(err,result){
				conn.end(function(err){
					callback(err,result);
				});
			}
		);
	});
	
	
}

/** get team detail from master **/
function getTeamById(team_uid,callback){
	prepareDb(function(conn){
		conn.query("SELECT uid,name FROM ffgame.master_team WHERE uid = ? LIMIT 1;",
		[team_uid],function(err,team){
			conn.end(function(err){
				callback(err,team[0]);
			});
		});
	});
	
}

/** create user team **/
function create(data,callback){
	prepareDb(function(conn){
			async.waterfall(
			[
				function(callback){
					conn.query("SELECT id FROM ffgame.game_users WHERE fb_id=? LIMIT 1",
								[data.fb_id],
								function(err,rs){
									console.log(this.sql);
									callback(null,rs[0]);
								});
					
				},
				function(user,callback){
					if(user==null){
						callback(new Error('no user'),'');
					}else{
						console.log(user);
						conn.query("INSERT INTO ffgame.game_teams\
								(user_id,team_id,created_date,n_status)\
								VALUES\
								(?,?,NOW(),1);",[user.id,data.team_id],function(err,rs){
									console.log(this.sql);
									if(err){
										console.log(err.message);
									}
									callback(err,rs);
								});
					}
				},
				function(result,callback){
					if(result!=null){

						var sql = "INSERT IGNORE INTO ffgame.game_team_players\
									(game_team_id,player_id) VALUES\
									";
						var d = [];
						for(var i in data.players){
							if(i>0){
								sql+=",";
							}
							sql+="(?,?)";
							d.push(result.insertId);
							d.push(data.players[i]);
						}
						conn.query(sql,d,function(err,rs){
							console.log(this.sql);
							callback(err,result.insertId);
						});
					}else{
						callback(new Error('no result'),'');
					}
				},
				function(game_team_id,callback){

					if(game_team_id!=null){
						conn.query(
							"INSERT IGNORE INTO ffgame.game_team_purse(game_team_id,budget)\
							 VALUES(?,?)"
						,[game_team_id,initial_money],
						function(err,rs){
							callback(err,game_team_id);
						});
					}
				}
			],
			function(err,result){
				
				conn.end(function(e){
					callback(err,result);	
				});
			}
		);
	});
	
	
}

/**
remove user team
**/
function remove_team(game_team_id,callback){
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("DELETE FROM ffgame.game_teams WHERE id=? LIMIT 1",
								[game_team_id],
								function(err,rs){
									callback(err);
								});
					
				},
				function(callback){
					conn.query("DELETE FROM ffgame.game_team_players WHERE game_team_id=?",
								[game_team_id],
								function(err,rs){
									callback(err,'');
								});
				}
				
			],
			function(err,result){
				conn.end(function(e){
					callback(err,result);	
				});
			}
		);
	});
	
}

function getUserTeam(fb_id,done){
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT id FROM ffgame.game_users WHERE fb_id=? LIMIT 1",
								[fb_id],
								function(err,rs){
									callback(null,rs[0]);
								});
					
				},
				function(user,callback){
					if(typeof user !=='undefined'){
						conn.query("SELECT * FROM ffgame.game_teams WHERE user_id = ? LIMIT 1",[
							user.id
						],
							function(err,team){
								console.log(team);
								console.log(this.sql);
								try{
									callback(err,team[0]);
								}catch(e){
									callback(new Error('team not found'),[]);
								}
						});
					}else{
						callback(new Error('user not found'),[]);
					}
				},
			],
			function(err,result){
				conn.end(function(e){
					done(err,result);	
				});
			}
		);
	});
	
}
function getUserTeamPoints(fb_id,done){
	prepareDb(function(conn){
		async.waterfall(
			[	
				function(callback){
					//get overall points
					conn.query("SELECT a.fb_id,b.user_id,b.id,b.team_id,c.points \
								FROM ffgame.game_users a\
								INNER JOIN ffgame.game_teams b\
								ON a.id = b.user_id\
								LEFT JOIN ffgame_stats.game_team_points c\
								ON b.id = c.game_team_id\
								WHERE a.fb_id = ?;",
								[fb_id],
								function(err,rs){
									if(rs!=null){
										callback(null,rs[0]);
									}else{
										callback(null,null);
									}
								});
					
				},
				function(rs,callback){
					if(rs!=null){
						//get per game stats
						if(rs.id!=null){
							conn.query("SELECT a.game_id,\
										SUM(points) AS total_points,\
										b.matchday,\
										b.match_date\
										FROM ffgame_stats.game_match_player_points a\
										INNER JOIN ffgame.game_fixtures b\
										ON a.game_id = b.game_id\
										WHERE game_team_id=? \
										GROUP BY game_id\
										LIMIT 400;",
										[rs.id],
										function(err,result){
											rs.game_points = result;
											callback(null,rs);
										});
						}
					}else{
						callback(null,rs);
					}
				}
			],
			function(err,result){
				conn.end(function(e){
					done(err,result);	
				});
			}
		);
	});
	

}

//make it accessable from anywhere
exports.getUserTeamPoints = getUserTeamPoints;
exports.getTeams = getTeams;
exports.getPlayers = getPlayers;
exports.getTeamById = getTeamById;
exports.create = create;
exports.remove_team = remove_team;
exports.getUserTeam = getUserTeam;
exports.setPool = function(p){
	pool = p;
}