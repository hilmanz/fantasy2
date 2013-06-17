/**
* module for updating the business part of the game.
* includings : 
* - post-match incomes / expenses.
* - salaries
* - bonus calculations
* Make sure each team is not processed twice.
* please notes that the stadium income is for home team only.
* away team only get sponsorship income + sponsor_bonus, also calculate the salary only.
*/

/**
* the module to read match_results file.
*/
var fs = require('fs');
var path = require('path');
var async = require('async');
var xmlparser = require('xml2json');
var config = require(path.resolve('./config')).config;
var stadium_earning_category = require(path.resolve('./libs/game_config')).stadium_earning_category;
var stadium_earnings = require(path.resolve('./libs/game_config')).stadium_earnings;
var mysql = require('mysql');
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});

exports.update = function(game_id,start,done){
	
	async.waterfall(
		[
			function(callback){
				getGameFixture(game_id,function(err,game){
					callback(err,game);
				});
			},
			function(game,callback){
				getTeamProfile(game[0],function(err,home_team){
					callback(null,game,home_team);
				});
			},
			function(game,home_team,callback){
				console.log(game,home_team);
				async.parallel([
						function(callback){
							calculateIncomeForAllHomeTeams(game_id,game,home_team,
								function(err,team_updated){
									callback(err,team_updated);
								});
						}
					],
				function(err,result){
						callback(err,result);
				});
				
			}
		],
		function(err,result){
			pool.end(function(err){
				console.log('pool destroyed');

			});
			done(null);			
		}

	);
	
}
function calculateIncomeForAllHomeTeams(game_id,game,home_team,done){
	console.log('calculate home teams');
	var limit = 100;
	var start = 0;
	var team_id = home_team[0].uid;
	processHomeTeams(start,limit,team_id,game_id,home_team[0].rank,game,done);
	
}
function processHomeTeams(start,limit,team_id,game_id,rank,game,done){
	pool.getConnection(function(err,conn){
		console.log('open connection');

		conn.query("SELECT * FROM ffgame.game_teams WHERE team_id = ? AND n_status=1 LIMIT ?,?;",
					[team_id,start,limit],
					function(err,rs){
							conn.end(function(err){
								async.each(rs,
									function(team,callback){
										calculate_home_revenue_stats(team,game_id,game,rank,function(err){
											callback();		
										});
									},function(err){
										if(rs.length==limit){
											processHomeTeams(start+100,limit,team_id,game_id,rank,game,done)
										}else{
											done(err,[]);
										}	
								});
								
							});	
					});
	});
}
/**
* calculate home revenues.
* 1. calculate revenue from tickets + its bonuses
* 2. calculate revenue from sponsors
* 3. calculate expenses on salaries
* 4. calculate winning bonuses and user points bonuses (ini blm ada datanya)
*/
function calculate_home_revenue_stats(team,game_id,game,rank,done){
	console.log(team,rank);
	var cashflow = [];
	var quadrant = getQuadrant(rank);
	pool.getConnection(function(err,conn){
		async.waterfall(
			[
				function(callback){
					//get team's officials
					conn.query(
						"SELECT b.* FROM ffgame.game_team_officials a\
						INNER JOIN ffgame.game_officials b\
						ON a.official_id = b.id WHERE game_team_id=?\
						LIMIT 20;",
						[team.id],
						function(err,officials){
							callback(null,officials);
					});
				},
				function(officials,callback){
					console.log(officials);
					callback(null,'');
				}
			],
			function(err,result){
				conn.end(function(err){
					console.log('finished calculation');
					done(err,null);
				});		
			}
		);
		
		
	});
}
function getQuadrant(rank){
	if(rank>=1 &&  rank<=4){
		return 'q1';
	}else if(rank>=5 && rank<=10){
		return 'q2';
	}else if(rank>=11 && rank<=15){
		return 'q3';
	}else{
		return 'q4';
	}
}
function getHomeTeams(team_id,start,limit,done){
	pool.getConnection(function(err,conn){
		console.log('open connection');
		conn.query("SELECT * FROM ffgame.game_fixtures WHERE game_id=?",
					[game_id],
					function(err,rs){
							conn.end(function(err){
								console.log('disconnected')
								done(err,rs);
							});	
					});
		
	});
}
function getGameFixture(game_id,done){
	console.log('get info for game_id #'+game_id);
	pool.getConnection(function(err,conn){
		console.log('open connection');
		conn.query("SELECT * FROM ffgame.game_fixtures WHERE game_id=?",
					[game_id],
					function(err,rs){
							conn.end(function(err){
								console.log('disconnected')
								done(err,rs);
							});	
					});
		
	});
}
function getTeamProfile(game,done){
	console.log('get team profile');
	var home,away;
	pool.getConnection(function(err,conn){
		conn.query("SELECT b.*,a.rank FROM ffgame.master_rank a\
					INNER JOIN ffgame.master_team b\
					ON a.team_id = b.uid\
					WHERE a.team_id = ?\
					ORDER BY rank",
					[game.home_id],
					function(err,team){
						
						conn.end(function(err){
							done(err,team);	
						});
					});
	});
}