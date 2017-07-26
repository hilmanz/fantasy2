/**
ranks and points updater
**/
/////THE MODULES/////////
var fs = require('fs');
var path = require('path');
var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');
var mysql = require('mysql');
var util = require('util');
var argv = require('optimist').argv;
var S = require('string');
/////DECLARATIONS/////////
var FILE_PREFIX = config.updater_file_prefix+config.competition.id+'-'+config.competition.year;
var stat_maps = require('./libs/stats_map').getStats();

var http = require('http');


var match_results = require('./libs/match_results_dummy');
var lineup_stats = require('./libs/gamestats/lineup_stats.worker');
var business_stats = require('./libs/gamestats/business_stats');
var ranks = require(path.resolve('./libs/gamestats/ranks'));

/////THE LOGICS///////////////
var conn = mysql.createConnection({
 	host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});


var bot_id = (typeof argv.bot_id !=='undefined') ? argv.bot_id : Math.round(1000+(Math.random()*999999));

//The url we want is: 'www.random.org/integers/?num=1&min=1&max=10&col=1&base=10&format=plain&rnd=new'
var options = {
  host: config.job_server.host,
  port: config.job_server.port,
  path: '/refresh'
};
console.log(options);
http.request(options, function(response){
	var str = '';
	response.on('data', function (chunk) {
	    str += chunk;
	});
	response.on('end',function(){
		var resp = JSON.parse(str);
		console.log(resp);
		
		//resp.status=1;//DEBUG ONLY
		
		if(resp.status==1){
			update_points_and_ranks(conn,function(err){
				console.log('Update Completed');
			});	
		}else{
			conn.end(function(err){
				console.log('no need to update points and ranks close db anyway');
			});
		}
	});
}).end();

function update_points_and_ranks(conn,done){
	async.waterfall([
		function(cb){
			console.log('updating game team points');
			var since_id = 0;
			var has_data = true;
			async.doWhilst(
				function(callback){
					conn.query("SELECT id as game_team_id,team_id as original_team_id \
								FROM ffgame.game_teams \
								WHERE id > ?\
								ORDER BY id ASC LIMIT 100;",[since_id],
								function(err,rs){
									if(rs!=null && rs.length > 0){
										console.log('since_id',since_id);
										since_id = rs[rs.length-1].game_team_id;
										async.waterfall([
											function(c){
												update_team_points(conn,rs,function(err){
													c(err);
												});
											},
											function(c){
												apply_perks(conn,rs,function(err){
													c(err);
												});
											}
										],
										function(err,r){
											callback();
										});
										
									}else{
										has_data = false;
										conn.end(function(err){
											callback();
										});
										
									}
								});
				},
				function(){
					return has_data;
				},
				function(err){
					cb(err);
				}
			);
			//cb(null);
			
		},
		function(cb){
			console.log('updating ranks');
			ranks.update(function(err,rs){
				console.log('done');
				cb(err,rs);
			});
		}

	],
	function(err,rs){
		done(err);
	});
}
function apply_perks(conn,teams,done){
	async.eachSeries(teams,function(team,next){
		async.waterfall([
			function(cb){
				conn.query("SELECT matchday \
							FROM \
							ffgame_stats.game_team_player_weekly \
							WHERE game_team_id=? ORDER BY matchday DESC LIMIT 1;",
							[team.game_team_id],function(err,rs){
								console.log('PERK',console.log(S(this.sql).collapseWhitespace().s));
								try{
									if(rs!=null){
										cb(err,rs[0].matchday);
									}else{
										cb(null,0);
									}
								}catch(err){
									cb(null,0);
								}
								
							});
			},
			function(matchday,cb){
				//get the game id
				conn.query("SELECT game_id FROM ffgame.game_fixtures \
							WHERE matchday=? \
							AND (home_id = ? OR away_id = ?) \
							LIMIT 1;",
							[matchday,
							 team.original_team_id,
							 team.original_team_id],
							 function(err,fixture){
							 	if(fixture!=null && fixture.length > 0){
							 		cb(err,fixture[0].game_id,matchday);	
							 	}else{
							 		cb(err,'',matchday);
							 	}
								
							});
			},
			function(game_id,matchday,cb){
				conn.query("SELECT * FROM ffgame.game_perks \
					WHERE game_team_id=? \
					AND matchday=? \
					AND n_status=0 \
					LIMIT 100;",
					[team.game_team_id,matchday],
					function(err,perks){
						console.log('PERK',console.log(S(this.sql).collapseWhitespace().s));
						process_perks(conn,team,perks,game_id,function(err){
							cb(err);
						});
					});
			}

		],
		function(err){
			next();
		});
		
	},function(err){
		done(err);
	});
}
function process_perks(conn,team,perks,game_id,done){
	console.log('PERK','processing perk for #',team.game_team_id);
	if(perks!=null && perks.length>0){
		console.log('PERK',perks);
		async.eachSeries(perks,function(perk,next){
			async.waterfall([
				function(cb){
					if(perk.money_reward > 0){
						//perk_money_reward(conn,team.game_team_id,perk,game_id,
						//function(err){
						//	cb(err);
						//});
						cb(err);
					}else{
						cb(err);
					}
				},
				function(cb){
					//flag as done
					flagPerkAsDone(conn,perk.id,function(err){
						cb(err);
					});
				}
			],
			function(err){
				next();
			});
		},function(err){
			console.log('PERK','done');
			done(err);
		});
	}else{
		console.log('PERK','no perk to process');
		done(null);
	}
}
function flagPerkAsDone(conn,perk_id,done){
	conn.query("UPDATE ffgame.game_perks SET n_status=1 WHERE id = ?",
				[perk_id],function(err,rs){
					done(err);
				});
}
function perk_money_reward(conn,game_team_id,perk,game_id,done){
	conn.query("INSERT IGNORE INTO ffgame.game_team_expenditures\
				(game_team_id,item_name,item_type,\
				 amount,game_id,match_day,item_total,base_price)\
				VALUES\
				(?,?,?,?,?,?,?,?);",
				[game_team_id,'perk-'+perk.event_id+' '+perk.name,
				 1,perk.money_reward,game_id,perk.matchday,1,1],
				function(err,rs){
					done(err);
				});
}
function update_team_points(conn,teams,done){
	async.eachSeries(teams,function(item,next){
		conn.query("INSERT INTO ffgame_stats.game_team_points\
					(game_team_id,points)\
					SELECT game_team_id,SUM(points) AS total_points\
					FROM ffgame_stats.game_team_player_weekly\
					WHERE game_team_id = ?\
					ON DUPLICATE KEY UPDATE\
					points = VALUES(points);",
					[item.game_team_id],
					function(err,rs){
						console.log(S(this.sql).collapseWhitespace().s);
						next();
					});
	},function(err){
		done(err);
	});
}