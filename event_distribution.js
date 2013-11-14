/**
event_distribution.js
these is the in-game Event Distribution across all players.
it main jobs is to check the event which need to be distributed when the schedule date is met.
there's 2 kind of events here.  1 that giveaway a money to selected individuals or teams, and one that
give a point bonus/penalty if the user's team owned the several selected players on matchday.
**/
/////THE MODULES/////////
var fs = require('fs');
var path = require('path');
var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');
var mysql = require('mysql');
var S = require('string');

var dbschema = config.database.frontend_schema;
/////DECLARATIONS/////////


/////THE LOGICS///////////////
//mysql pool
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});



async.waterfall([
	//get all events which will happen today and still not been processed.
	function(callback){
		getAllEventsWhichWillHappenToday(function(err,rs){
			callback(err,rs);
		});
	},
	function(schedules,callback){
		processSchedule(schedules,function(err){
			callback(err);
		});
	},
	function(callback){
		//process immediate events
		processImmediateEvents(function(err){
			callback(err);
		});
		
	}
],
function(err){
	pool.end(function(e){
		console.log('done');
	})
});




function getAllEventsWhichWillHappenToday(cb){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame.master_events \
					WHERE n_status=0 \
					AND DATE(schedule_dt) = DATE(NOW()) \
					LIMIT 20;",
					[],function(err,rs){
						conn.end(function(e){
							try{
								if(rs.length>0){
									cb(err,rs);
								}else{
									cb(err,[]);
								}
							}catch(e){
								cb(err,null);
							}
						});
					});
	});
}

//for each events, check its event_type
//event_type:1 -> affects only to player data (gives or reduces money)
//event_type:2 -> affects only to master data (gives or reduces points)
//the player data events will in effect immediately, 
//while the master data only executed on matchday
//after the updater_worker process ended, and before the rank_and_points.js executed.
function processSchedule(schedules,cb){
	console.log(schedules);
	async.eachSeries(schedules,function(schedule,next){
		if(schedule.event_type==1){
			//if event_type 1
			processPlayerEvent(schedule,function(err){
				next();
			});
		}else{
			//if event type 2
			processMasterEvent(schedule,function(err){
				next();
			});
		}
	},function(err){
		cb(err);
	});

}

function processPlayerEvent(schedule,cb){
	console.log('process player event');
	//check the target recipients
	switch(schedule.target_type){
		case 1:
			//if selected individual teams, then queue those selected individual team to event_immediate table
			//and then queue the email notifications
			distributeEventToIndividualTeam(schedule,function(err){
				flagSchedule(schedule,1,function(err,rs){
					cb(err);
				});
				
			});
		break;
		case 2:
			//if all teams, then simply queue all teams and insert into event_immediate table.
			//and then queue the email notifications
			distributeEventToAllTeams(schedule,function(err){
				flagSchedule(schedule,1,function(err,rs){
					cb(err);
				});
			});
		break;
		case 3:
			//if by tier, then query the n-tier teams, then put those n-tier tam to event_immediate table
			//and then queue the email notifications
			distributeEventByTier(schedule,function(err){
				flagSchedule(schedule,1,function(err,rs){
					cb(err);
				});
			});
		break;
		default:
			//do nothing, something is not right here.
			cb(null);
		break;
	}
}
function flagSchedule(schedule,flag,cb){
	pool.getConnection(function(err,conn){
		conn.query("UPDATE ffgame.master_events SET n_status=? WHERE id=?",
					[flag,schedule.id],
					function(err,rs){
						conn.end(function(err){
							cb(err,rs);	
						});
					});
	});
}
function distributeEventToIndividualTeam(schedule,cb){
	console.log('distributeEventToIndividualTeam');
	var targets = JSON.parse(schedule.target_value);
	pool.getConnection(function(err,conn){
		//queue those selected individual teams to event_immediate table
		distributeEachTeam(conn,schedule,targets,function(err){
			conn.end(function(e){
				cb(err);
			})
		});
	});
}
function distributeEachTeam(conn,schedule,targets,cb){
	async.eachSeries(targets,function(target,next){
		async.waterfall([
			function(callback){
				//queueing
				conn.query("INSERT IGNORE INTO ffgame.job_event_immediate\
							(master_event_id,game_team_id,apply_date,n_status)\
							VALUES\
							(?,?,NOW(),0)",[schedule.id,target],
							function(err,rs){
								callback(err,rs);
							});
			},function(rs,callback){
				if(rs!=null){
					//queue email notification
					async.waterfall([
						function(c){
							conn.query("SELECT a.name,a.email FROM "+dbschema+".users a\
										INNER JOIN ffgame.game_users b\
										ON a.fb_id = b.fb_id\
										INNER JOIN ffgame.game_teams c\
										ON c.user_id = b.id\
										WHERE c.id = ? LIMIT 1",[target],function(err,r){
											try{
												c(err,r[0]);
											}catch(e){
												c(null,'');
											}
										});
						},
						function(user,c){
							if(typeof user !== 'undefined'){
								conn.query("INSERT INTO ffgame.email_queue\
										(subject,email,plain_txt,html_text,queue_dt,n_status)\
										VALUES\
										(?,?,?,?,NOW(),0);",[
											schedule.email_subject,
											user.email,
											schedule.email_body_txt,
											schedule.email_body_txt
										],
										function(err,ins){
											c(err,ins);
										});
							}else{
								c(null,null);
							}
							
						}
					],
					function(err,r){
						callback(err);
					});
				}
			}
		],
		function(err){
			next();
		});
	},function(err){
		cb(err);	
	});
}
function distributeEventToAllTeams(schedule,cb){
	console.log('distributeEventToAllTeams');
	pool.getConnection(function(err,conn){
		var has_data = true;
		var since_id = 0;
		async.whilst(function(){
			return has_data;
		},function(next){
			console.log(since_id);
			conn.query("SELECT id FROM ffgame.game_teams WHERE id > ? ORDER BY id ASC LIMIT 100",[since_id],
			function(err,teams){
				if(teams.length>0){
					var targets = [];
					for(var i in teams){
						targets.push(teams[i].id);
					}
					since_id = teams[teams.length-1].id;

					distributeEachTeam(conn,schedule,targets,function(err){
						next();
					});
				}else{
					has_data = false;
					next();
				}
			});
		},function(err){
			conn.end(function(e){
				cb(err);
			});
		});
	});
}

function distributeEventByTier(schedule,cb){
	console.log('distributeEventByTier');
	var targets = JSON.parse(schedule.target_value);
	var tier = targets[0];
	console.log('Tier : ',tier);

	var start_rank = 0;
	var end_rank = 0;
	pool.getConnection(function(err,conn){
		async.waterfall([
				function(callback){
					conn.query("SELECT MAX(rank) AS max_rank FROM "+dbschema+".points;",
								[],
								function(err,rs){
						callback(err,rs[0].max_rank);
					});
				},
				function(max_rank,callback){
					console.log('max_rank',max_rank);
					switch(tier){
						case '1':
							start_rank = 0;
							end_rank 	= Math.floor(0.25 * max_rank);
						break;
						case '2':
							
							start_rank = Math.ceil(0.25 * max_rank);
							end_rank 	= Math.floor(0.5 * max_rank);
						break;
						case '3':
							start_rank = Math.ceil(0.5 * max_rank);
							end_rank 	= Math.floor(0.75 * max_rank);
						break;
						case '4':
							start_rank = Math.ceil(0.75 * max_rank);
							end_rank 	= Math.floor(1.0 * max_rank);
						break;
						default:
							start_rank = 0;
							end_rank 	= 0;
						break;
					}
					callback(null);
				},
				function(callback){
					//now get the teams in these tiers
					var has_data = true;
					var since_id = 0;
					async.whilst(function(){
						return has_data;
					},function(next){
						console.log(since_id);
						conn.query("SELECT e.id AS game_team_id \
							FROM "+dbschema+".points a\
							INNER JOIN "+dbschema+".teams b\
							ON a.team_id = b.id\
							INNER JOIN "+dbschema+".users c\
							ON c.id = b.user_id\
							INNER JOIN ffgame.game_users d\
							ON d.fb_id = c.fb_id\
							INNER JOIN ffgame.game_teams e\
							ON e.user_id = d.id\
							WHERE e.id > ? AND rank > ? AND rank <= ? LIMIT 100;",
						[since_id,start_rank,end_rank],
						function(err,teams){
							console.log(S(this.sql).collapseWhitespace().s);
							if(teams.length>0){
								var targets = [];
								for(var i in teams){
									targets.push(teams[i].game_team_id);
								}
								since_id = teams[teams.length-1].game_team_id;

								distributeEachTeam(conn,schedule,targets,function(err){
									next();
								});
							}else{
								has_data = false;
								next();
							}
						});
					},function(err){
						callback(err);
					});
				}
			],
			function(err){
				conn.end(function(e){
					cb(err);
				});
		});
	});
	
	
}

function processMasterEvent(schedule,cb){
	console.log('process master event');
	//check the target recipients
	switch(schedule.target_type){
		case 1:
			//if individual teams, then queue all teams which its original team is matched the selected teams,
			//and scheduled it for the next match_day (can we distribute the point immediately ? )
			//and then queue the email notifications
			distributeMasterEventToTeam(schedule,function(err){
				flagSchedule(schedule,1,function(err,rs){
					cb(err);
				});
			});
		break;
		case 4:
			//if individual players, then queue all teams who has the selected players on their team rooster and
			//scheduled it for the next match_day
			//and then queue the email notifications
			distributeMasterEventToPlayer(schedule,function(err){
				flagSchedule(schedule,1,function(err,rs){
					cb(err);
				});
			});
		break;		
		default:
			//do nothing, something is not right here.
			cb(null);
		break;
	}
}
function distributeMasterEventToPlayer(schedule,cb){
	console.log('distributeMasterEventToPlayer');
	var targets = JSON.parse(schedule.target_value);
	distributeMasterEvents(targets,schedule,function(err){
		
		sendNotificationEmails(schedule,function(err){
			console.log('finished');
			cb(err);
		});
	});
}
function distributeMasterEventToTeam(schedule,cb){
	console.log('distributeMasterEventToTeam');
	var targets = JSON.parse(schedule.target_value);
	var players = [];
	//for each team, we take the list of its players
	async.eachSeries(targets,function(target,next){
		pool.getConnection(function(err,conn){
			conn.query("SELECT uid FROM ffgame.master_player WHERE team_id=? LIMIT 100;",
					[target],function(err,rs){
						for(var i in rs){
							players.push(rs[i].uid);
						}
						conn.end(function(err){
							next();
						});
					});
		});
		
	},function(err){
		console.log(players);
		distributeMasterEvents(players,schedule,function(err){
			sendNotificationEmails(schedule,function(err){
				cb(err);
			});
			
		});
		
	});
	
}
function distributeMasterEvents(targets,schedule,cb){
	pool.getConnection(function(err,conn){
		async.eachSeries(targets,function(target,next){
			async.waterfall([
				function(callback){
					//first we try to check the available matchday closer to schedule_dt.
					conn.query("SELECT matchday \
								FROM ffgame.game_fixtures \
								WHERE match_date > ? \
								ORDER BY matchday ASC LIMIT 1;",[schedule.schedule_dt],
								function(err,fixture){
									console.log(S(this.sql).collapseWhitespace().s);
									callback(err,fixture[0].matchday);
								});
				},
				function(matchday,callback){
					conn.query("INSERT IGNORE INTO ffgame.job_event_master_player\
								(master_event_id,player_id,matchday,apply_date,n_status)\
								VALUES\
								(?,?,?,NOW(),0);",
					[schedule.id,target,matchday],
					function(err,rs){
						console.log(S(this.sql).collapseWhitespace().s);
						callback(err,rs);
					});
				}
			],
			function(err,rs){
				next();
			});
		},function(err){
			conn.end(function(err){
				console.log('done');
				cb(err);
			});
		});
	});
}
function sendNotificationEmails(schedule,cb){
	pool.getConnection(function(err,conn){
		//send email to all users
		conn.query("INSERT INTO ffgame.email_queue\
					(subject,email,plain_txt,html_text,queue_dt,n_status)\
					SELECT ? AS subject,email,? AS plain_txt,? AS html_text,\
					NOW() AS queue_dt,0 AS n_status \
					FROM "+dbschema+".users;",
					[schedule.email_subject,schedule.email_body_txt,schedule.email_body_txt],
					function(err,rs){
						console.log(S(this.sql).collapseWhitespace().s);
						conn.end(function(err){
							cb(err);
						});
					});
	});
	
					
}

//process the immediate events,
//at the moment, we only send or deduct the money.
function processImmediateEvents(cb){
	pool.getConnection(function(err,conn){
		var has_data = true;
		async.whilst(function(){
			return has_data;
		},
		function(next){
			conn.query(
				"SELECT a.id,a.game_team_id,a.master_event_id,b.event_name,\
				b.affected_item,b.amount,b.event_type,\
				b.name_appear_on_report,b.target_type,\
				b.target_value \
				FROM ffgame.job_event_immediate a \
				INNER JOIN ffgame.master_events b\
				ON a.master_event_id = b.id \
				WHERE a.n_status=0 LIMIT 10;",
				[],
				function(err,rs){
				if(rs!=null && rs.length>0){
					apply_event_modifier(conn,rs,function(err){
						next();
					});
				}else{
					has_data = false;
					next();
				}
				
			});
		},function(err){
			conn.end(function(err){
				cb(err);
			});
			
		});
	});
}

function apply_event_modifier(conn,queue,cb){
	async.eachSeries(queue,function(q,next){
		if(q.affected_item==1){
			//apply money
			addMoney(conn,q,function(err){
				next();
			});
		}else{
			//skip dulu, yg apply per points nanti.
			next();
		}
	},function(err){
		cb(err);
	});
}
function addMoney(conn,queue,cb){
	console.log(queue);
	async.waterfall([
		function(callback){
			//get the next match's game_id
			next_match(conn,queue.game_team_id,function(err,next_match){
				if(next_match!=null && next_match.length > 0){
					
					callback(err,next_match[0]);	
				}else{
					callback(err,'');
				}
			});
		},
		function(next_match,callback){
			if(next_match!=''){
				if(queue.amount>0){
					transaction_type = 1;
				}else{
					transaction_type = 2;
				}
				conn.query("INSERT IGNORE INTO ffgame.game_team_expenditures\
				(game_team_id,item_name,item_type,amount,game_id,match_day,item_total,base_price)\
				VALUES\
				(?,?,?,?,?,?,?,?);",
				[queue.game_team_id,queue.name_appear_on_report,transaction_type,
				  queue.amount,next_match.game_id,next_match.matchday,1,1],
				function(err,rs){
					console.log(S(this.sql).collapseWhitespace().s);
					callback(err,rs);
				});
			}else{
				callback(null,null);
			}
		},
		function(isDone,callback){
			conn.query("UPDATE ffgame.job_event_immediate SET n_status=1 WHERE id=?",
						[queue.id],
						function(err,rs){
							callback(err,rs);
						});
		}
	],
	function(err,rs){
		cb(err);
	});
}

function next_match(conn,game_team_id,done){
	
	
	async.waterfall(
		[
			function(callback){
				conn.query("SELECT team_id FROM ffgame.game_teams WHERE id = ? LIMIT 1",
							[game_team_id],function(err,rs){
								callback(err,rs[0].team_id);
							});
			},
			function(team_id,callback){
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
			},
			function(rs,callback){
				try{
					conn.query("SELECT match_date \
								FROM \
								ffgame.game_fixtures \
								WHERE matchday = ? \
								ORDER BY match_date DESC \
								LIMIT 1;",
								[(rs[0].matchday - 1)],
								function(err,last_match){
									rs[0].last_match = last_match[0].match_date;
									callback(err,rs);
								});
				}catch(e){
					callback(null,rs);
				}
			}
		],
		function(err,result){
			done(err,result);
		}
	);
	
}