/**
* module for updating the match results across all users's lineups
*
* we only calculate unprocessed team.
* the processed team will have an entry in game_team_lineups_history.
* if there's no existing game_id on the history, we will do the calculation.
* 
* here's the rule
* 1. we only update the lineup history for team which playes in the game (game_id)
* 2. for those team which not played in the game, we only track for the player who played in the real-world.
*    and add the stats to the team.
*/

/**
* the module to read match_results file.
*/
var fs = require('fs');
var path = require('path');
var async = require('async');
var xmlparser = require('xml2json');
var config = require(path.resolve('./config')).config;
var mysql = require('mysql');
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});
console.log('lineup_stats - creating pool');
exports.update = function(game_id,start,done){
	//the updates will run for each 100 entries.
	
	var limit = 100;
	var is_complete = false;
	
	async.waterfall(
		[
			function(callback){
				get_user_teams(start,limit,function(err,team){
					//if there's no more data. we stop :)
					if(team.length<limit){
						is_complete = true;
					}else{
						start+=limit;
					}
					callback(err,team);
				});	
			},
			function(team,callback){
				get_master_team_stats(game_id,function(err,player_stats){
					callback(err,game_id,team,player_stats);
				});
			},
			function(game_id,team,player_stats,callback){
				get_master_match_summary(game_id,function(err,team_summary){
					callback(err,game_id,team,player_stats,team_summary);
				})
			},
			function(game_id,team,player_stats,team_summary,callback){
				update_team_stats(game_id,team,player_stats,team_summary,function(err){
					callback(err,'ok');
				});
			},
			function(result,callback){
				update_team_points(function(err){
					callback(err,'ok');
				})
			}
		],
		function(err,result){		
			done(err,is_complete,start);
		}
	);	
		
	
	
}
exports.done = function(){
	pool.end(function(err){
		if(err) console.log('match_results','error',err.message);
		console.log('lineup_stats','pool closed');
	});
}

function get_master_team_stats(game_id,done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame_stats.master_match_player_points\
					WHERE game_id = ? LIMIT 50",
					[game_id],
		function(err,rs){
			if(err){console.log('lineupstats - ERROR - ',err.message);}
			conn.end(function(err){
				done(err,rs);	
			});
		});

	});
}
function get_master_match_summary(game_id,done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame_stats.master_match_points\
					WHERE game_id = ? LIMIT 30",
					[game_id],
		function(err,rs){
			if(err){console.log(err.message);}
			conn.end(function(err){
				for(var i in rs){
					rs[i].avg_points = rs[i].overall_points / 11;
				}
				done(err,rs);	
			});
		});

	});
}
function get_user_teams(start,limit,done){
	pool.getConnection(function(err,conn){
		if(err) console.log(err.message);
		conn.query("SELECT * FROM ffgame.game_teams LIMIT ?,?",
				[start,limit],
		function(err,rs){
			if(err){console.log(err.message);}
			conn.end(function(err){
				done(err,rs);	
			});
		});
	});
	
}
function update_team_stats(game_id,team,player_stats,team_summary,done){
	//console.log(team,player_stats,team_summary);
	console.log('team_summary',team_summary);
	var summary = {}; //team summary, this will be used for player's performance change modifier.
	for(var i in team_summary){
		summary[team_summary[i].team_id] = {points:team_summary[i].overall_points,
											average:team_summary[i].avg_points};

	}
	console.log(summary);
	pool.getConnection(function(err,conn){
		async.eachSeries(team,
						function(item,callback){
							update_individual_team_stats(game_id,item,summary,player_stats,function(err){
								callback();	
							});
						},
			function(err){
				conn.end(function(err){
					done(null);
					console.log('done')
				});
			}
		);

		
	});
}
function update_individual_team_stats(game_id,team,summary,player_stats,done){
		async.waterfall(
			[
				function(callback){
					//step 1 - get team lineups
					getTeamLineups(team,function(err,lineups){
						callback(null,lineups);
					});
					
				},
				function(lineups,callback){
					var in_game = true;
					if(typeof summary[team.team_id] === 'undefined'){
						in_game = false;
					}
					//step 2 - update lineup stats
					updateLineupStats(game_id,lineups,summary,player_stats,in_game,function(err,rs){
						callback(err,rs);
					});
					
				},
				function(rs,callback){
					//final steps, add entry on lineup_history
					console.log(team,'vs',summary);
					if(typeof summary[team.team_id]!=='undefined'){
						console.log('track the lineup history for #',team.team_id);
						addToHistory(game_id,team,function(err){
							callback(err,rs);	
						});
					}else{
						console.log('no need to track the lineup history for #',team.team_id);
						callback(null,rs);	
					}
				}
			],
				function(err,result){
					done(err);
				}
			);
	
	
}
function addToHistory(game_id,team,done){
	pool.getConnection(function(err,conn){
		conn.query("INSERT IGNORE INTO \
					ffgame.game_team_lineups_history\
					(game_id,game_team_id,player_id,position_no,last_update)\
					SELECT ? AS game_id,game_team_id,player_id,position_no,NOW() AS last_update\
					FROM ffgame.game_team_lineups WHERE game_team_id=?;",[game_id,team.id],
					function(err,rs){
						conn.end(function(err){
							done(err);
						});
					});
	});
}
function getTeamLineups(team,done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame.game_team_lineups WHERE game_team_id = ? LIMIT 20",
					[team.id],function(err,rs){
					
							conn.end(function(err){
									if(typeof rs !== 'object' && rs.length > 0){
										err = new Error('no lineups :(');
										rs = [];
									}

									done(err,rs);
							});
		});
	});	
}
function updateLineupStats(game_id,lineups,summary,player_stats,in_game,done){
	console.log('player stats : ',player_stats);
	pool.getConnection(function(err,conn){
		async.eachSeries(lineups,
						function(item,callback){
							//console.log(item);
							var stats = {player_id:item.player_id,
										 points: 0,
										 performance: 0};
							var is_found = false;
							for(var i in player_stats){
								if(item.player_id==player_stats[i].player_id){
									stats.points = player_stats[i].points;
									var apts =  summary[player_stats[i].team_id].average;
									stats.performance = ((stats.points - apts)/apts)*100;
									is_found = true;
									break;
								}
							}
							if(!is_found && !in_game){
								//we skip it if the player is not matched one of the player_stats,
								//and the team is not involve with the game at all.
								console.log('skip #',item.player_id,' from team #',item.game_team_id);
								callback();
							}else{
								console.log('add #',item.player_id,' from team #',item.game_team_id,' stats');
								conn.query("INSERT INTO ffgame_stats.game_match_player_points\
									(game_id,game_team_id,player_id,points,performance,last_update)\
									VALUES(?,?,?,?,?,NOW())\
									ON DUPLICATE KEY UPDATE\
									points = VALUES(points),\
									performance = VALUES(performance);",
									[	game_id,
										item.game_team_id,
										stats.player_id,
										stats.points,
										stats.performance ],
									function(err,rs){
										console.log(this.sql);
										callback();	
								});
							}
							
						},
						function(err){
							conn.end(function(err){
								done(null,[]);	
							});
						});
	});
}

/**
* updating the team's overall points
*/
function update_team_points(done){
	pool.getConnection(function(err,conn){
		conn.query("INSERT INTO ffgame_stats.game_team_points\
					(game_team_id,points)\
					SELECT game_team_id,SUM(points) AS total_points \
					FROM ffgame_stats.game_match_player_points\
					GROUP BY game_team_id\
					ON DUPLICATE KEY UPDATE\
					points = VALUES(points);",[],function(err,rs){
						conn.end(function(err){
							done(err);	
						});
						
					});
	});
}