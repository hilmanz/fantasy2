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
							async.waterfall([
									function(next){
										update_individual_team_stats(game_id,item,summary,player_stats,
											function(err){
											next(err);	
										});
									},
									function(next){
										console.log('Lets process the extra points if the week has ended....')
										//check the game's matchday
										conn.query("SELECT matchday \
													FROM ffgame.game_fixtures \
													WHERE game_id=? \
													LIMIT 1",[game_id],
													function(err,r){
														console.log('-----',this.sql,'---');
														var matchday = 0;
														if(!err){
															matchday = r[0].matchday;
														}
														next(err,matchday);
													});
										
									},
									function(matchday,next){
										//check if the week has lasted.
										conn.query("SELECT COUNT(*) AS total \
													FROM ffgame.game_fixtures \
													WHERE period = 'FullTime' \
													AND matchday = ? \
													AND is_processed=1",[matchday],
													function(err,r){
														console.log('-----',this.sql,'---');
														var is_finished = false;
														if(!err){
															if(r[0].total==10){
																is_finished = true;
															}
														}
														next(err,matchday,is_finished);
													});
									},
									function(matchday,is_finished,next){
										console.log('matchday : ',matchday,'is finished : ',is_finished);
										console.log('is all player started ?');
										var is_all_player_started = false;

										if(is_finished){
											async.waterfall([
													function(cb){
														conn.query("SELECT game_id \
																	FROM ffgame.game_fixtures \
																	WHERE (home_id =? OR away_id=?) \
																	AND matchday = ?;",
																	[item.team_id,item.team_id,matchday],
																	function(err,r){	
																		console.log('--> we need the exact game_id',
																					this.sql);
																		var the_game_id = '';
																		if(!err){
																			try{
																				the_game_id = r[0].game_id;
																			}catch(e){
																				the_game_id = '';
																			}
																			
																		}
																		cb(err,the_game_id);
																	});
													}
												],
												function(err,the_game_id){
													//check if all lineup is played in real game.
													conn.query("SELECT * FROM \
														ffgame.game_team_lineups_history a\
														INNER JOIN\
														ffgame_stats.master_player_stats b\
														ON a.player_id = b.player_id\
														INNER JOIN ffgame.game_fixtures c\
														ON b.game_id = c.game_id\
														WHERE a.game_team_id=? AND a.game_id = ?\
														AND b.stats_name = 'game_started'\
														AND c.matchday = ?\
														AND a.position_no < 12;",[
															item.id,the_game_id,matchday
														],
														function(err,r){
															console.log('-----',this.sql,'---');
															if(!err){
																if(r.length==11){
																	is_all_player_started = true;
																}
															}
															next(err,the_game_id,matchday,is_finished,is_all_player_started);

													});
												}
											);
											
										}else{
											next(err,'',matchday,is_finished,is_all_player_started);
										}
									},
									function(the_game_id,matchday,is_finished,is_all_player_started,next){
										console.log('is budget below zero ?');
										var is_team_budget_below_zero = false;
										if(is_finished){
											conn.query("SELECT SUM(budget+expenses) AS balance\
														FROM (\
															SELECT budget,0 AS expenses \
															FROM ffgame.game_team_purse \
															WHERE game_team_id=?\
														UNION ALL\
															SELECT 0,SUM(amount) AS total \
															FROM ffgame.game_team_expenditures \
															WHERE match_day <= ? AND game_team_id=?\
														) a;",
												[item.id,matchday,item.id],
												function(err,r){
												console.log('-----',this.sql,'---');
												var balance = 0;
												if(!err){
													balance = r[0].balance;
													if(r[0].balance < 0){
														is_team_budget_below_zero = true;
													}
												}
												next(err,
													the_game_id,
													matchday,
													is_finished,is_all_player_started,
													is_team_budget_below_zero,
													balance);
											});
										}else{
											next(err,
													the_game_id,
													matchday,
													is_finished,is_all_player_started,
													is_team_budget_below_zero,
													0);
										}
									},
									function(the_game_id,matchday,is_finished,is_all_player_started,
											is_team_budget_below_zero,balance,next){
										console.log('wrapping up buddy !');
										if(is_finished){
											async.waterfall([
												function(cb){
													if(is_all_player_started){
														conn.query("INSERT INTO \
																	ffgame_stats.game_team_extra_points\
																	(game_id,matchday,game_team_id,\
																		modifier_name,extra_points)\
																	VALUES\
																	(?,?,?,?,?)\
																	ON DUPLICATE KEY UPDATE\
																	extra_points = VALUES(extra_points);",
																	[
																		the_game_id,
																		matchday,
																		item.id,
																		'ALL_LINEUP_IS_PLAYED_BONUS',
																		20
																	],function(err,r){
																		console.log('-----',this.sql,'---');
																		cb(err);
																	});
													}else{
														cb(err);
													}
												},
												function(cb){
													if(is_team_budget_below_zero){
														var penalty = Math.floor(balance/100000) * 100;
														console.log('PENALTY : ',penalty);
														conn.query("INSERT INTO \
																	ffgame_stats.game_team_extra_points\
																	(game_id,matchday,game_team_id,\
																		modifier_name,extra_points)\
																	VALUES\
																	(?,?,?,?,?)\
																	ON DUPLICATE KEY UPDATE\
																	extra_points = VALUES(extra_points);",
																	[
																		the_game_id,
																		matchday,
																		item.id,
																		'BALANCE_IS_BELOW_ZERO',
																		penalty
																	],function(err,r){
																		console.log('-----',this.sql,'---');
																		cb(err,true);
																	});
													}else{
														cb(err,true);
													}
												},
												function(all_ok,cb){
													//send notification that the player has recieved the extra points
													if(is_all_player_started){
														msg = "Selamat, anda baru saja mendapatkan bonus poin sebesar 20 dipertandingan lalu.";
														conn.query("INSERT INTO "+config.database.frontend_schema+".notifications\
																	(content,url,dt,game_team_id)\
																	VALUES\
																	(?,'#',NOW(),?)",[msg,item.id],function(err,rs){
																		console.log('---',this.sql,'----');
																		cb(err,true);
															});
													}else{
														cb(null,true);
													}
												},
												function(all_ok,cb){
													//send notification that the player has recieved the extra points
													if(is_team_budget_below_zero){
														var penalty = Math.floor(balance/100000) * 100;
														msg = "Kamu mendapatkan potongan poin sebesar "+penalty+" karena keuangan kamu negatif";
														conn.query("INSERT INTO "+config.database.frontend_schema+".notifications\
																	(content,url,dt,game_team_id)\
																	VALUES\
																	(?,'#',NOW(),?)",[msg,item.id],function(err,rs){
																		console.log('---',this.sql,'----');
																		cb(err,true);
															});
													}else{
														cb(null,true);
													}
												}

											],function(err,endOfProcess){
												next(err,endOfProcess);
											});
										}else{
											next(err,true);
										}
									}
								],
								function(err,wf_result){
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
					//@TODO perlu dipikirkan apakah kita perlu narik lineup dari lineup history ?

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
						console.log('track the lineup history for #',team.id);

						//@TODO : ini nanti kayaknya harus dieksekusi sebelum step 2.
						//agar jika kita sedang me regen data,  lineup yg dipakai adalah lineup dari history.
						//bukan lineup actual.
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
						console.log('update lineup history ',this.sql);
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
							async.waterfall([
									function(callback){
										conn.query("SELECT game_id,player_id,stats_name \
													FROM ffgame_stats.master_player_stats \
													WHERE game_id = ?  AND player_id= ? \
													AND stats_name = 'game_started' LIMIT 1;",
													[game_id,item.player_id],function(err,sqlResult){
														
														var isStarter = false;
														
														sqlResult = sqlResult || [];
														if(sqlResult.length>0){
															isStarter = true;
														}
														console.log('check starter status',game_id,item.player_id,isStarter);
														callback(err,isStarter);
													});
									},
									function(isStarter,callback){
										for(var i in player_stats){
											if(item.player_id==player_stats[i].player_id){
												stats.points = player_stats[i].points;
												//if(!isStarter){
												//	console.log('points reduced to 75%',(stats.points*0.75));
												//	stats.points = stats.points * 0.75;
												//}
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
											callback(err,null);
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
													callback(err,rs);	
											});
										}
									}

								],
								function(err,rsWaterfall){
									callback();
								}
							);
							
							
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