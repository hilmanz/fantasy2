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
var player_stats_category = require(path.resolve('./libs/game_config')).player_stats_category;
var pool = {};
function prepareDb(callback){
	pool.getConnection(function(err,conn){
		callback(conn);
	});
}

//get current lineup setup
function getLineup(game_team_id,callback){
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT a.player_id,a.position_no,\
					b.name,b.position,b.known_name \
					FROM ffgame.game_team_lineups a\
					INNER JOIN ffgame.master_player b\
					ON a.player_id = b.uid\
					WHERE a.game_team_id=? LIMIT 17",
					[game_team_id],
					function(err,rs){
							callback(err,rs);	
					});
				},
				function(result,callback){
					conn.query("SELECT formation FROM ffgame.game_team_formation\
								WHERE game_team_id = ? LIMIT 1",
					[game_team_id],
					function(err,rs){
							var formation = '4-4-2'; //default formation
							if(rs.length>0){
								formation = rs[0].formation;	
							}
							callback(err,{
									lineup:result,
									formation:formation
								});
					});
				}
			],
		function(err,result){
			conn.end(function(e){
				callback(err,result);
			});
		});
	});
	
}
function setLineup(game_team_id,setup,formation,done){
	prepareDb(function(conn){
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
								WHERE a.game_team_id = ? AND a.player_id IN (?) LIMIT 16",
								[game_team_id,players],
								function(err,rs){
									console.log(this.sql);
									console.log(rs);
									callback(null,rs);
								});
					
				},
				function(players,callback){
					if(players.length==16){
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
				},
				function(result,callback){
					//save formation
					conn.query("INSERT INTO ffgame.game_team_formation\
								(game_team_id,formation,last_update)\
								VALUES(?,?,NOW())\
								ON DUPLICATE KEY UPDATE\
								formation = VALUES(formation),\
								last_update = VALUES(last_update)",
								[game_team_id,formation],
								function(err,rs){
									callback(err,result);
								});
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
//check if the player's formation is valid
//saat ini kita cuman memastikan bahwa nomor 1 itu harus kiper.
//nomor yg lain mau penyerang semua sih gak masalah (untuk sementara waktu)
function position_valid(players,setup,formation){
	console.log(players);
	
	var my_formation = formations[formation];
	
	for(var i in setup){
		for(var j in players){
			if(players[j].player_id == setup[i].player_id){
				console.log('--',setup[i].no,' ',players[j].position,' vs ',my_formation[setup[i].no]);
				if(setup[i].no<=11){
					if(my_formation[setup[i].no]=='Forward/Midfielder'){
						//console.log('check : '+setup[i].no,' ',players[j].position,' vs ',my_formation[setup[i].no]);
						if(players[j].position != 'Forward' 
								&& players[j].position != 'Midfielder'){
							return false;
						}

					}else{
						//console.log('foo : '+setup[i].no,' ',players[j].position,' vs ',my_formation[setup[i].no]);
						if(players[j].position != my_formation[setup[i].no]){
							return false;
						}
					}
				}
				break;
			}
		}
	}
	return true;
}
//get user's players
function getPlayers(game_team_id,callback){
	prepareDb(function(conn){
		async.waterfall([
			function(callback){
				conn.query("SELECT b.* \
				FROM ffgame.game_team_players a\
				INNER JOIN ffgame.master_player b \
				ON a.player_id = b.uid\
				WHERE game_team_id = ? ORDER BY b.position ASC,b.last_name ASC \
				LIMIT 200;",
				[game_team_id],
				function(err,rs){
					callback(err,rs);
				});
			},
			function(players,callback){
				var results = [];
				async.eachSeries(players,function(player,done){
					conn.query("SELECT SUM(total_points) AS total_points,\
								SUM(performance) AS performance\
								FROM (\
								(SELECT SUM(points) AS total_points ,0 AS performance\
								FROM ffgame_stats.game_match_player_points \
								WHERE game_team_id = ? AND player_id = ?)\
								UNION ALL\
								(SELECT 0,performance FROM ffgame_stats.game_match_player_points a\
									WHERE game_team_id=? AND player_id=?\
									AND EXISTS (SELECT 1 FROM ffgame.game_fixtures b \
									WHERE b.game_id = a.game_id AND (b.home_id = ? OR b.away_id=?)\
									LIMIT 1\
									)\
									ORDER BY id DESC LIMIT 1)\
								)a;\
								",
					[game_team_id,player.uid,game_team_id,player.uid,player.team_id,player.team_id],
					function(err,rs){
						console.log(this.sql);
						player.points = 0;
						player.last_performance = 0;
						if(!err){
							if(rs!=null){
								if(rs[0].total_points!=null){
									player.points = parseInt(rs[0].total_points);
									player.last_performance = parseFloat(rs[0].performance);	
									
								}
								
							}
						}
						results.push(player);
						done();
					});
				},function(err){
					callback(err,results);
				});
			}
		],
		function(err,result){
			conn.end(function(e){
				callback(err,result);	
			});
		});
	});
	
	
}

//get user's budget
function getBudget(game_team_id,callback){
	sql = "SELECT SUM(initial_budget+total) AS budget \
			FROM (SELECT budget AS initial_budget,0 AS total FROM ffgame.game_team_purse WHERE game_team_id = ?\
			UNION ALL\
			SELECT 0,SUM(amount) AS total FROM ffgame.game_team_expenditures WHERE game_team_id = ?) a;";
	prepareDb(function(conn){
		conn.query(sql,
				[game_team_id,game_team_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
	});
	
}

/**
* get player master detail
*/
function getPlayerDetail(player_id,callback){
	sql = "SELECT a.uid AS player_id,a.name,a.position,\
		a.first_name,a.last_name,a.known_name,a.birth_date,\
		a.weight,a.height,a.jersey_num,a.real_position,a.real_position_side,\
		a.country,team_id AS original_team_id,\
		b.name AS original_team_name,a.salary,a.transfer_value\
		FROM ffgame.master_player a\
		INNER JOIN ffgame.master_team b\
		ON a.team_id = b.uid\
		WHERE a.uid = ? LIMIT 1;";
	prepareDb(function(conn){
		conn.query(sql,
				[player_id],
				function(err,rs){
					conn.end(function(e){
						if(rs.length==1){
							callback(err,rs[0]);	
						}else{
							callback(err,null);
						}
					});
				});
	});
	
}
/**
* get team's player detail
*/
function getTeamPlayerDetail(game_team_id,player_id,callback){
	sql = "SELECT a.uid AS player_id,a.name,a.position,\
			a.first_name,a.last_name,a.known_name,a.birth_date,\
			a.weight,a.height,a.jersey_num,a.real_position,a.real_position_side,\
			a.country,team_id AS original_team_id,\
			b.name AS original_team_name,a.salary,a.transfer_value\
			FROM ffgame.master_player a\
			INNER JOIN ffgame.master_team b\
			ON a.team_id = b.uid\
			WHERE a.uid IN (\
				SELECT player_id FROM ffgame.game_team_players \
				WHERE game_team_id=? AND player_id=?\
			)\
			LIMIT 1;";
	prepareDb(function(conn){
		conn.query(sql,
				[game_team_id,player_id],
				function(err,rs){
					conn.end(function(e){
						if(rs.length==1){
							callback(err,rs[0]);	
						}else{
							callback(err,null);
						}
					});
				});
	});
	
}
/**
* get player master stats
*/
function getPlayerStats(player_id,callback){
	sql = "SELECT a.game_id,a.points,a.performance,b.matchday\
			FROM ffgame_stats.master_player_performance a\
			INNER JOIN ffgame.game_fixtures b\
			ON a.game_id = b.game_id \
			WHERE player_id = ? ORDER BY a.id ASC;";
	prepareDb(function(conn){
		conn.query(sql,
				[player_id],
				function(err,rs){
					conn.end(function(e){
						callback(err,rs);	
					});
				});
	});
	
}
/**
*	get player's overall stats
*/
function getPlayerOverallStats(game_team_id,player_id,callback){
	if(game_team_id!=0){
		sql = "SELECT stats_name,SUM(stats_value) AS total\
				FROM ffgame_stats.master_player_stats a\
				INNER JOIN ffgame.game_fixtures b\
				ON a.game_id = b.game_id\
				WHERE a.player_id=?\
				AND EXISTS(\
				SELECT 1 FROM ffgame.game_team_players c\
				WHERE c.game_team_id=?\
				AND c.player_id = a.player_id\
				LIMIT 1)\
				AND EXISTS(\
				SELECT 1 FROM ffgame_stats.game_match_player_points d\
				WHERE d.game_team_id=? AND d.game_id = a.game_id AND d.player_id = a.player_id LIMIT 1\
				)\
				GROUP BY stats_name;";
	}else{
		sql = "SELECT stats_name,SUM(stats_value) AS total\
				FROM ffgame_stats.master_player_stats a\
				INNER JOIN ffgame.game_fixtures b\
				ON a.game_id = b.game_id\
				WHERE a.player_id=?\
				GROUP BY stats_name;";
	}
	prepareDb(function(conn){
		conn.query(sql,
				[player_id,game_team_id,game_team_id],
				function(err,rs){
					
					conn.end(function(e){
						callback(err,rs);	
					});
				});
	});
	
}
/*
* get player's team stats
*/
function getPlayerTeamStats(game_team_id,player_id,callback){
	sql = "SELECT a.game_id,a.points,a.performance,b.matchday\
			FROM ffgame_stats.game_match_player_points a\
			INNER JOIN\
			ffgame.game_fixtures b\
			ON a.game_id = b.game_id\
			WHERE a.game_team_id = ?\
			AND a.player_id = ? ORDER BY a.game_id LIMIT 300;";
	prepareDb(function(conn){
		conn.query(sql,
				[game_team_id,player_id],
				function(err,rs){
					console.log(this.sql);
					conn.end(function(e){
						callback(err,rs);	
					});
				});
	});
	
}
/**get player daily stats relative to game_team
*/
function getPlayerDailyTeamStats(game_team_id,player_id,player_pos,done){
	var pos = 'g';
	switch(player_pos){
		case 'Forward':
			pos = 'f';
		break;
		case 'Midfielder':
			pos = 'm';
		break;
		case 'Defender':
			pos = 'd';
		break;
		default:
			pos = 'g';
		break;
	}
	if(game_team_id!=0){
		sql = "SELECT a.game_id,stats_name,SUM(stats_value) AS total\
				FROM ffgame_stats.master_player_stats a \
				INNER JOIN ffgame.game_fixtures b\
				ON a.game_id = b.game_id\
				WHERE a.player_id=?\
				AND EXISTS(\
					SELECT 1\
					FROM ffgame.game_team_players c\
					WHERE c.game_team_id=?\
					AND c.player_id = a.player_id\
					LIMIT 1\
				)\
				AND EXISTS(\
					SELECT 1 FROM ffgame_stats.game_match_player_points d\
					WHERE d.game_team_id=? AND d.game_id = a.game_id \
					AND d.player_id = a.player_id LIMIT 1\
				)\
				GROUP BY a.game_id,stats_name \
				ORDER BY game_id ASC LIMIT 20000;";
	}else{
		sql = "SELECT a.game_id,stats_name,SUM(stats_value) AS total\
				FROM ffgame_stats.master_player_stats a \
				INNER JOIN ffgame.game_fixtures b\
				ON a.game_id = b.game_id\
				WHERE a.player_id=?\
				GROUP BY a.game_id,stats_name \
				ORDER BY game_id ASC LIMIT 20000;";
	}
	prepareDb(function(conn){
		async.waterfall([
			function(callback){
				conn.query(sql,
					[player_id,game_team_id,game_team_id],
					function(err,rs){
						console.log(this.sql);			
						callback(err,rs);	
						
					});
			},
			function(result,callback){
				conn.query("SELECT * FROM ffgame.game_matchstats_modifier;",
				[],
				function(err,rs){
					callback(err,rs,result);	
					
				});
			},
			function(modifiers,result,callback){
				//mapping
				var daily = {};
				
				if(result.length>0){
					for(var i in result){
						if(typeof daily[result[i].game_id] === 'undefined'){
							daily[result[i].game_id] = {
								games:0,
								passing_and_attacking:0,
								defending:0,
								goalkeeper:0,
								mistakes_and_errors:0
							};
						}
						//distributed the counts for each categories
						for(var category in player_stats_category){
							for(var j in player_stats_category[category]){

								if(player_stats_category[category][j] == result[i].stats_name){
									if(category=='goalkeeper'){
										console.log(result[i].game_id,category,result[i].stats_name,(parseInt(result[i].total) 
																		  * getModifierValue(modifiers,
																		  					result[i].stats_name,
																		  					pos)));
									}
									daily[result[i].game_id][category] += (parseInt(result[i].total) 
																		  * getModifierValue(modifiers,
																		  					result[i].stats_name,
																		  					pos));
								}
							}
						}
					}
				}
				console.log(daily);
				callback(null,daily);
			}
		],
		function(err,result){
			conn.end(function(e){
				done(err,result);
			});
		});	
	});
	
}
/**
* get modifier value based on player's position
*/
function getModifierValue(modifiers,stats_name,position){
	
	for(var i in modifiers){
		if(modifiers[i].name==stats_name){
			return (parseInt(modifiers[i][position]));
		}
	}
	return 0;
}
/**
* get user's financial statement
*/
function getFinancialStatement(game_team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					//starting budget
					conn.query("SELECT budget FROM ffgame.game_team_purse WHERE game_team_id = ?;",
							[game_team_id],function(err,rs){
								if(!err){
									callback(err,rs[0].budget);
								}else{
									callback(err,null);
								}
							});
				},
				function(starting_budget,callback){
					//get the total matches by these team
					conn.query("SELECT COUNT(*) AS total_matches FROM (SELECT game_id\
								FROM ffgame.game_team_expenditures WHERE game_team_id = ?\
								GROUP BY game_id) a;",
						[game_team_id],
						function(err,result){
						if(typeof result !== 'undefined'){
							callback(err,starting_budget,result[0]);
						}else{
							callback(err,starting_budget,null);
						}
					});
				},
				function(starting_budget,matches,callback){
					//weekly balance
					conn.query("SELECT game_id,SUM(amount) AS total_income,match_day\
								FROM ffgame.game_team_expenditures \
								WHERE game_team_id=?\
								GROUP BY game_id \
								ORDER BY match_day LIMIT 100;",
								[game_team_id],
								function(err,rs){
									if(!err){
										callback(err,starting_budget,matches,rs);
									}else{
										callback(err,null,null,null);
									}
								});
				},
				function(starting_budget,matches,rs,callback){
					var weekly_balance = [];
					var current_balance = starting_budget;
					if(rs.length>0){
						for(var i in rs){
							current_balance += parseInt(rs[i].total_income);
							weekly_balance.push({week:rs[i].match_day,balance:current_balance});
						}
					}else{
						weekly_balance.push({week:1,balance:starting_budget});
					}
					callback(null,starting_budget,weekly_balance,matches,rs);
				},
				function(starting_budget,weekly_balance,matches,expenditures,callback){
					if(matches!=null){
						conn.query("SELECT item_name,item_type,SUM(amount) AS total\
									FROM ffgame.game_team_expenditures\
									WHERE game_team_id=?\
									GROUP BY item_name;",
							[game_team_id],
							function(err,result){
								callback(err,{starting_budget:starting_budget,
											  weekly_balances:weekly_balance,
											  total_matches:matches.total_matches,
											  report:result,
											  expenditures:expenditures});
						});
					}else{	
						callback(null,null);
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
function getWeeklyFinance(game_team_id,week,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall([
				function(callback){
						conn.query("SELECT game_team_id,item_name,item_type,amount,game_id,match_day \
							FROM ffgame.game_team_expenditures \
							WHERE game_team_id=? AND match_day = ?;",
							[game_team_id,week],
							function(err,rs){
								callback(err,rs);
						});
				}
			],
			function(err,result){
				conn.end(function(e){
					done(err,result);
				});
			});
	});
}
function next_match(team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT a.id,\
							a.game_id,a.home_id,b.name AS home_name,a.away_id,\
							c.name AS away_name,a.home_score,a.away_score,\
							a.matchday,a.period,a.session_id,a.attendance,match_date\
							FROM ffgame.game_fixtures a\
							INNER JOIN ffgame.master_team b\
							ON a.home_id = b.uid\
							INNER JOIN ffgame.master_team c\
							ON a.away_id = c.uid\
							WHERE (home_id = ? OR away_id=?) AND period <> 'FullTime'\
							ORDER BY a.matchday\
							LIMIT 1;\
							",[team_id,team_id],function(err,rs){
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
	});
	
}
function getVenue(team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT stadium_name AS name,stadium_capacity AS capacity \
									FROM ffgame.master_team WHERE uid=? LIMIT 1;",
									[team_id],function(err,rs){
								callback(err,rs[0]);
							});
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
function best_match(game_team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT team_id FROM ffgame.game_teams WHERE id = ? LIMIT 1;",
								[game_team_id],function(err,rs){
									callback(err,rs[0].team_id);
								});
				},
				function(team_id,callback){
					conn.query("SELECT game_team_id,b.matchday,SUM(points) AS total_points\
								FROM ffgame_stats.game_match_player_points a\
								INNER JOIN ffgame.game_fixtures b\
								ON a.game_id = b.game_id\
								WHERE a.game_team_id = ?\
								GROUP BY matchday ORDER BY total_points DESC LIMIT 1;",
								[game_team_id],function(err,rs){
								if(err){
									callback(new Error('no data'),{});
								}else{
									if(typeof rs[0] !== 'undefined'){
										console.log(rs[0]);
										callback(err,team_id,rs[0]);	
									}else{
										callback(new Error('no data'),{});
									}
								}
							});
				},
				function(team_id,best_match,callback){
					conn.query("SELECT a.game_id,a.home_score,a.away_score,a.home_id,a.away_id,b.name AS home_name,c.name AS away_name\
								FROM ffgame.game_fixtures a\
								INNER JOIN ffgame.master_team b\
								ON a.home_id = b.uid\
								INNER JOIN ffgame.master_team c\
								ON a.away_id = c.uid\
								WHERE (a.home_id = ? OR a.away_id = ?) AND a.matchday=?\
								AND period='FullTime'\
								LIMIT 1;",
								[team_id,team_id,best_match.matchday],
								function(err,rs){
									console.log(this.sql);
									callback(err,{match:rs[0],points:best_match.total_points});
								});
					
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
/**
*	getting team's last week's earnings.
*/
function last_earning(game_team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT game_id FROM ffgame.game_team_expenditures \
								WHERE game_team_id=? ORDER BY id DESC \
								LIMIT 1;\
							",[game_team_id],function(err,rs){
								
								if(err){
									callback(new Error('no data'),{});
								}else{
									if(typeof rs[0] !== 'undefined'){
										callback(err,rs[0].game_id);	
									}else{
										callback(new Error('no data'),{});
									}
								}
							});
				},
				function(game_id,callback){
					conn.query("SELECT SUM(amount) AS total_earnings \
								FROM ffgame.game_team_expenditures \
								WHERE game_team_id = ? \
								AND \
								game_id = ? AND amount >= 0;",
								[game_team_id,game_id],
								function(err,rs){

									if(!err){
										if(typeof rs[0] !== 'undefined'){
											callback(err,rs[0]);
										}else{
											callback(new Error('no data'),{});
										}
									}else{
										callback(err,{});
									}
								});
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
/**
*	getting team's last week's expenses.
*/
function last_expenses(game_team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT game_id FROM ffgame.game_team_expenditures \
								WHERE game_team_id=? ORDER BY id DESC \
								LIMIT 1;\
							",[game_team_id],function(err,rs){
								
								if(err){
									callback(new Error('no data'),{});
								}else{
									if(typeof rs[0] !== 'undefined'){
										callback(err,rs[0].game_id);	
									}else{
										callback(new Error('no data'),{});
									}
								}
							});
				},
				function(game_id,callback){
					conn.query("SELECT SUM(amount) AS total_expenses \
								FROM ffgame.game_team_expenditures \
								WHERE game_team_id = ? \
								AND \
								game_id = ? AND amount <= 0;",
								[game_team_id,game_id],
								function(err,rs){

									if(!err){
										if(typeof rs[0] !== 'undefined'){
											callback(err,rs[0]);
										}else{
											callback(new Error('no data'),{});
										}
									}else{
										callback(err,{});
									}
								});
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
/**
*	getting team's best player
*/
function best_player(game_team_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					conn.query("SELECT b.uid as player_id,SUM(a.points) AS total_points,b.first_name,\
								b.known_name,b.last_name,b.name,b.position \
								FROM ffgame_stats.game_match_player_points a\
								INNER JOIN ffgame.master_player b\
								ON a.player_id = b.uid\
								WHERE game_team_id=? GROUP BY player_id \
								ORDER BY total_points DESC LIMIT 1;	\
							",[game_team_id],function(err,rs){
								if(err){
									callback(new Error('no data'),{});
								}else{
									if(typeof rs[0] !== 'undefined'){
										callback(err,rs[0]);	
									}else{
										callback(new Error('no data'),{});
									}
								}
							});
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

/*sale player
* step 1 - make sure that the player_id is owned by the team.
* step 2 - count the transfer value accordingly
* step 3 - remove the player from possession and from lineups
* step 4 - add a transfer money to game_team_expenditures
* 
*/
function sale(game_team_id,player_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					console.log('selling player #',player_id,'from team #',game_team_id);
					conn.query(
						"SELECT COUNT(id) AS total\
						FROM \
						ffgame.game_team_players \
						WHERE game_team_id=? AND player_id = ? LIMIT 1;",
						[game_team_id,player_id],
						function(err,rs){
							if(!err && rs[0]['total']==1){
								callback(err,true);	
							}else{
								callback(err,false);
							}
						});
				},
				function(is_valid,callback){
					console.log('player is owned by the club ? ',is_valid);
					if(is_valid){
						//check for transfer value
						conn.query(
						"SELECT name,transfer_value FROM ffgame.master_player WHERE uid = ? LIMIT 1;",
						[player_id],
						function(err,rs){
							if(!err){
								callback(err,rs[0]['name'],rs[0]['transfer_value']);
							}else{
								callback(new Error('player not in master data'),null);
							}
						});
						
					}else{
						callback(new Error('no player in the club'),null);
					}
				},
				function(name,transfer_value,callback){
					//check for player's latest performances
					var performance_diff = 0;
					conn.query(
					"SELECT points,performance FROM ffgame_stats.game_match_player_points \
					 WHERE game_team_id=? AND player_id=? ORDER BY id DESC;",
					[game_team_id,player_id],
					function(err,rs){

						if(!err){
							//@TODO we need to calculate the player's performance value to affect
							//the latest transfer value
							if(rs.length>0){
								rs[0].performance = rs[0].performance || 0;
								if(rs[0].performance!=0){
									transfer_value = transfer_value + ((((rs[0].performance / 10) * 1)/100)*transfer_value);	
								} 
							}
							callback(err,name,transfer_value);
						}else{
							callback(new Error('player_got no performance'),null);
						}
					});
				},
				function(name,transfer_value,callback){
					console.log('the price',transfer_value);
					
					async.waterfall(
						[
							function(callback){
								//remove player from lineup
								conn.query(
										"DELETE FROM ffgame.game_team_lineups\
										 WHERE game_team_id=? AND player_id=?",
										 [game_team_id,player_id],function(err,rs){
										 	callback(err,rs);
										 });
										 
							},
							function(rs,callback){
								//remove player from team's rooster
								if(rs!=null){
									conn.query(
										"DELETE FROM ffgame.game_team_players \
										 WHERE game_team_id=? AND player_id=?;",
										 [game_team_id,player_id],
										 function(err,rs){
										 	callback(err,rs);
										 });
								}
								
							},
							function(rs,callback){
								if(rs!=null){
									//we need to know the next week game_id
									conn.query("SELECT team_id FROM ffgame.game_teams WHERE id=? LIMIT 1",
										 [game_team_id],
										 function(err,rs){
										 	
										 	callback(err,name,transfer_value,rs[0]['team_id']);
										 	
										 });
								}
							},
							function(name,transfer_value,team_id,callback){
								//we need to know next match's game_id and matchdate
								conn.query("SELECT a.id,\
								a.game_id,a.home_id,b.name AS home_name,a.away_id,\
								c.name AS away_name,a.home_score,a.away_score,\
								a.matchday,a.period,a.session_id,a.attendance,match_date\
								FROM ffgame.game_fixtures a\
								INNER JOIN ffgame.master_team b\
								ON a.home_id = b.uid\
								INNER JOIN ffgame.master_team c\
								ON a.away_id = c.uid\
								WHERE (home_id = ? OR away_id=?) AND period <> 'FullTime'\
								ORDER BY a.matchday\
								LIMIT 1;\
								",[team_id,team_id],function(err,rs){
									callback(err,name,transfer_value,rs[0]['game_id'],rs[0]['matchday']);
								});
							},
							function(name,transfer_value,game_id,matchday,callback){
								//ok now we have all the ingridients..  
								//lets insert into financial expenditure
								conn.query("INSERT INTO ffgame.game_team_expenditures\
											(game_team_id,item_name,item_type,amount,game_id,match_day)\
											VALUES\
											(?,?,?,?,?,?)\
											ON DUPLICATE KEY UPDATE\
											amount = amount + VALUES(amount);",
											[game_team_id,
											'player_sold',
											 1,
											 transfer_value,
											 game_id,
											 matchday
											],
											function(err,rs){
												console.log(this.sql);
												callback(err,{name:name,transfer_value:transfer_value});
								});
							}
						],
						function(err,result){
							//we're done :D
							callback(err,result);
						}
					);
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

/*buy player
* step 1 - make sure that the player is not in the club yet
* step 2 - count the transfer value accordingly
* step 3 - check the club's balance, if the money is sufficient, go to step 4
* step 4 - add the player to the club
* step 5 - deduct a transfer money from game_team_expenditures
* 
*/
function buy(game_team_id,player_id,done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					console.log('buying player #',player_id,'from team #',game_team_id);
					conn.query(
						"SELECT COUNT(id) AS total\
						FROM \
						ffgame.game_team_players \
						WHERE game_team_id=? AND player_id = ? LIMIT 1;",
						[game_team_id,player_id],
						function(err,rs){
							if(!err && rs[0]['total']==0){

								callback(err,true);	
							}else{
								callback(err,false);
							}
						});
				},
				function(is_valid,callback){

					console.log('player is not owned by the club ? ',is_valid);
					if(is_valid){
						//check for transfer value
						conn.query(
						"SELECT name,transfer_value \
						 FROM ffgame.master_player WHERE uid = ? LIMIT 1;",
						[player_id],
						function(err,rs){
							console.log(this.sql);
							if(!err){
								callback(err,rs[0]['name'],rs[0]['transfer_value']);
							}else{
								callback(new Error('player not in master data'),null);
							}
						});
						
					}else{
						callback(new Error('the player is already in the club'),null);
					}
				},
				function(name,transfer_value,callback){
					//check for player's latest performances
					var performance_diff = 0;
					conn.query(
					"SELECT points,performance FROM ffgame_stats.master_player_performance \
					 WHERE player_id=? ORDER BY id DESC;",
					[player_id],
					function(err,rs){
						if(!err){
							console.log(this.sql);
							//@TODO we need to calculate the player's performance value to affect
							//the latest transfer value
							if(rs.length>0){
								rs[0].performance = rs[0].performance || 0;
								if(rs[0].performance!=0){
									transfer_value = transfer_value + ((((rs[0].performance / 10) * 1)/100)*transfer_value);
								}
							}
							callback(err,name,transfer_value);

						}else{
							callback(new Error('player got no performance'),null);
						}
					});
				},
				function(name,transfer_value,callback){
					console.log('the price',transfer_value);
					
					async.waterfall(
						[
							function(callback){
								//check for the budget
								conn.query("SELECT SUM(budget+balance) AS money FROM (\
												SELECT budget, 0 AS balance \
												FROM ffgame.game_team_purse \
												WHERE game_team_id=?\
													UNION\
												SELECT 0 AS budget,SUM(amount) AS balance \
												FROM ffgame.game_team_expenditures \
												WHERE game_team_id = ?) a;",
								[game_team_id,game_team_id],function(err,rs){
									if(rs!=null){
										if(rs[0]['money'] >= transfer_value){
											callback(null,true);	
										}else{
											callback(new Error('no money'),false);
										}
									}else{
										callback(new Error('no money'),false);
									}
								});
							},
							function(has_money,callback){
								//remove player from team's rooster
								if(has_money){
									conn.query(
										"INSERT INTO ffgame.game_team_players \
										 (game_team_id,player_id)\
										 VALUES(?,?);",
										 [game_team_id,player_id],
										 function(err,rs){
										 	callback(err,rs);
										 });
								}
								
							},
							function(rs,callback){
								if(rs!=null){
									//we need to know the next week game_id
									conn.query("SELECT team_id FROM ffgame.game_teams WHERE id=? LIMIT 1",
										 [game_team_id],
										 function(err,rs){
										 	console.log(this.sql);
										 	callback(err,name,transfer_value,rs[0]['team_id']);
										 	
										 });
								}
							},
							function(name,transfer_value,team_id,callback){
								//we need to know next match's game_id and matchdate
								conn.query("SELECT a.id,\
								a.game_id,a.home_id,b.name AS home_name,a.away_id,\
								c.name AS away_name,a.home_score,a.away_score,\
								a.matchday,a.period,a.session_id,a.attendance,match_date\
								FROM ffgame.game_fixtures a\
								INNER JOIN ffgame.master_team b\
								ON a.home_id = b.uid\
								INNER JOIN ffgame.master_team c\
								ON a.away_id = c.uid\
								WHERE (home_id = ? OR away_id=?) AND period <> 'FullTime'\
								ORDER BY a.matchday\
								LIMIT 1;\
								",[team_id,team_id],function(err,rs){
									console.log(this.sql);
									callback(err,name,transfer_value,rs[0]['game_id'],rs[0]['matchday']);
								});
							},
							function(name,transfer_value,game_id,matchday,callback){
								//ok now we have all the ingridients..  
								//lets insert into financial expenditure
								conn.query("INSERT INTO ffgame.game_team_expenditures\
											(game_team_id,item_name,item_type,amount,game_id,match_day)\
											VALUES\
											(?,?,?,?,?,?)\
											ON DUPLICATE KEY UPDATE\
											amount = amount + VALUES(amount);",
											[game_team_id,
											'buy_player',
											 1,
											 (transfer_value*-1),
											 game_id,
											 matchday
											],
											function(err,rs){
												console.log(this.sql);
												callback(err,{name:name,transfer_value:transfer_value});
								});
							}
						],
						function(err,result){
							//we're done :D
							callback(err,result);
						}
					);
				}
			],
			function(err,result){
				if(err){
					console.log(err.message);
				}
				conn.end(function(e){
					done(err,result);
				});
			}
		);
	});
	
}
/**
* get master leaderboard
*/
function leaderboard(done){
	var async = require('async');
	prepareDb(function(conn){
		async.waterfall(
			[
				function(callback){
					//get team list
					conn.query("SELECT uid AS team_id,name FROM ffgame.master_team;",
						[],function(err,rs){
						callback(err,rs);
					});
				},
				function(teams,callback){
					//foreach team, get its summaries
					var team_stats = [];
					async.eachSeries(teams,function(team,done){
						getTeamResultStats(conn,team.team_id,function(err,result){
							team_stats.push({team:team,stats:result});
							done();	
						});
					},function(err){
						callback(err,team_stats);
					});
				}
			],
			function(err,result){
				console.log(result);
				conn.end(function(e){
					done(err,result);
				});
			}
		);
	});
	
}
function getTeamResultStats(conn,team_id,callback){
	var stats = {
		games:0,
		wins:0,
		loses:0,
		draws:0,
		goals:0,
		conceded:0,
		top_score:{},
		top_assist:{}
	};
	async.waterfall([
			function(callback){
				//count how many wins, how many lost
				//how many goals, how many goals conceded.
				conn.query("SELECT home_id,away_id,home_score,away_score,period\
							FROM \
							ffgame.game_fixtures \
							WHERE (home_id = ? OR away_id= ?) \
							AND is_processed=1 AND period='FullTime';",
							[team_id,team_id],
							function(err,rs){
								if(rs.length>0){
									callback(err,rs);
								}else{
									callback(new Error('no data yet'),null);
								}
							});
			},
			function(games,callback){
				if(games!=null){
					for(var i in games){
						if(games[i].home_id==team_id){
							if(games[i].home_score > games[i].away_score){
								stats.wins++;
							}else if(games[i].home_score < games[i].away_score){
								stats.loses++;
							}else{
								stats.draws++;
							}
							stats.goals += games[i].home_score;
							stats.conceded += games[i].away_score;
						}else{
							if(games[i].home_score < games[i].away_score){
								stats.wins++;
							}else if(games[i].home_score > games[i].away_score){
								stats.loses++;
							}else{
								stats.draws++;
							}
							stats.goals += games[i].away_score;
							stats.conceded += games[i].home_score;
						}
						stats.games++;
					}

					callback(null,stats);
				}else{
					callback(null,stats);
				}
			},
			function(stats,callback){
				//find for top goals
				conn.query("SELECT a.player_id,SUM(stats_value) AS goals,b.* \
							FROM ffgame_stats.master_match_result_stats a\
							INNER JOIN\
							ffgame.master_player b ON a.player_id = b.uid\
							WHERE a.team_id=? AND stats_name='goals'\
							GROUP BY a.player_id\
							ORDER BY goals DESC LIMIT 1",
							[team_id],
							function(err,rs){
								if(rs.length>0){
									stats.top_score = rs[0];
								}
								callback(err,stats);
							});
			},
			function(stats,callback){
				//find for top assist
				conn.query("SELECT a.player_id,SUM(stats_value) AS assist,b.* \
							FROM ffgame_stats.master_match_result_stats a\
							INNER JOIN\
							ffgame.master_player b ON a.player_id = b.uid\
							WHERE a.team_id=? AND stats_name='goal_assist'\
							GROUP BY a.player_id\
							ORDER BY assist DESC;",
							[team_id],
							function(err,rs){
								if(rs.length>0){
									stats.top_assist = rs[0];
								}
								callback(err,stats);
							});
			}
		],
		function(err,result){
			callback(err,result);
		}
	);
}

var match = require(path.resolve('./libs/api/match'));

exports.leaderboard = leaderboard;
exports.best_player = best_player;
exports.last_earning = last_earning;
exports.last_expenses = last_expenses;
exports.best_match = best_match;
exports.getVenue = getVenue;
exports.next_match = next_match;
exports.getFinancialStatement = getFinancialStatement;
exports.getPlayerDetail = getPlayerDetail;
exports.getPlayerTeamStats = getPlayerTeamStats;
exports.getPlayerStats = getPlayerStats;
exports.getLineup = getLineup;
exports.setLineup = setLineup;
exports.getPlayers = getPlayers;
exports.getBudget = getBudget;
exports.match = match;
exports.officials = require(path.resolve('./libs/api/officials'));
exports.sponsorship = require(path.resolve('./libs/api/sponsorship'));
exports.getPlayerOverallStats = getPlayerOverallStats;
exports.getTeamPlayerDetail = getTeamPlayerDetail;
exports.getPlayerDailyTeamStats = getPlayerDailyTeamStats;
exports.sale = sale;
exports.buy = buy;
exports.getWeeklyFinance = getWeeklyFinance;
exports.setPool = function(p){
	pool = p;
	match.setPool(pool);
}