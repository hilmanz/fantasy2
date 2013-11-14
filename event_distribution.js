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
	}
],
function(err){
	pool.end(function(e){
		console.log('done');
	})
});

//process the queue in event_immediate table
//foreach queue add money to game_team_expenditures and use event's name as its item name


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
				cb(err);
			});
		break;
		case 2:
			//if all teams, then simply queue all teams and insert into event_immediate table.
			//and then queue the email notifications
			distributeEventToAllTeams(schedule,function(err){
				cb(err);
			});
		break;
		case 3:
			//if by tier, then query the n-tier teams, then put those n-tier tam to event_immediate table
			//and then queue the email notifications
			distributeEventByTier(schedule,function(err){
				cb(err);
			});
		break;
		default:
			//do nothing, something is not right here.
			cb(null);
		break;
	}
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
				cb(err);
			});
		break;
		case 4:
			//if individual players, then queue all teams who has the selected players on their team rooster and
			//scheduled it for the next match_day
			//and then queue the email notifications
			distributeMasterEventToPlayer(schedule,function(err){
				cb(err);
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
	cb(null);
}
function distributeMasterEventToTeam(schedule,cb){
	console.log('distributeMasterEventToTeam');
	cb(null);
}