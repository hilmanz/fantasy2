/**
* module for updating the business part of the game.
* includings : 
* - post-match incomes / expenses.
* - salaries
* - bonus calculations
* Make sure each team is not processed twice.
* please notes that the stadium income is for home team only.
* away team only get sponsorship income + sponsor_bonus, also calculate the salary only.

 @TODO : 
 - calculate away team.
  so we need to create dummy away-team first.
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
var cost_mods = require(path.resolve('./libs/game_config')).cost_modifiers;
var mysql = require('mysql');
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});
console.log('business_stats : pool opened');
exports.update = function(game_id,start,done){
	
	async.waterfall(
		[
			function(callback){
				getGameFixture(game_id,function(err,game){
					callback(err,game);
				});
			},
			function(game,callback){
				getTeamProfile(game[0],function(err,team){
					callback(null,game,team.home,team.away);
				});
			},
			function(game,home_team,away_team,callback){
				console.log(game,home_team,away_team);
				async.parallel([
						function(callback){
							calculateIncomeForAllHomeTeams(game_id,game,home_team,away_team,
								function(err,team_updated){
									callback(err,team_updated);
								});
						},
						function(callback){
							calculateIncomeForAllAwayTeams(game_id,game,home_team,away_team,
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
			
			done(null);			
		
			
		}

	);
	
}
exports.done = function(){
	pool.end(function(err){
		if(err) console.log('business_stats','error',err.message);
		console.log('business_stats','pool closed');
	});
}
function calculateIncomeForAllHomeTeams(game_id,game,home_team,away_team,done){
	console.log('calculate all home teams');
	var limit = 100;
	var start = 0;
	var team_id = home_team[0].uid;
	processHomeTeams(start,limit,team_id,game_id,home_team[0].rank,away_team[0].rank,game,done);
	
}
function calculateIncomeForAllAwayTeams(game_id,game,home_team,away_team,done){
	console.log('calculate all away teams');
	var limit = 100;
	var start = 0;
	var team_id = away_team[0].uid;
	processAwayTeams(start,limit,team_id,game_id,home_team[0].rank,away_team[0].rank,game,done);
	
}
function processHomeTeams(start,limit,team_id,game_id,rank,away_rank,game,done){
	pool.getConnection(function(err,conn){
		console.log('open connection');
		conn.query("SELECT * FROM ffgame.game_teams WHERE team_id = ? AND n_status=1 LIMIT ?,?;",
					[team_id,start,limit],
					function(err,rs){
							conn.end(function(err){
								async.each(rs,
									function(team,callback){
										calculate_home_revenue_stats(team,game_id,game,rank,away_rank,function(err){
											callback();		
										});
									},function(err){
										if(rs.length==limit){
											processHomeTeams(start+100,limit,team_id,game_id,rank,away_rank,game,done)
										}else{
											done(err,[]);
										}	
								});
								
							});	
					});
	});
}
function processAwayTeams(start,limit,team_id,game_id,rank,away_rank,game,done){
	pool.getConnection(function(err,conn){
		console.log('open connection');
		conn.query("SELECT * FROM ffgame.game_teams WHERE team_id = ? AND n_status=1 LIMIT ?,?;",
					[team_id,start,limit],
					function(err,rs){
							conn.end(function(err){
								async.each(rs,
									function(team,callback){
										calculate_away_revenue_stats(team,game_id,game,rank,away_rank,function(err){
											callback();		
										});
									},function(err){
										if(rs.length==limit){
											processAwayTeams(start+100,limit,team_id,game_id,rank,away_rank,game,done)
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
function calculate_home_revenue_stats(team,game_id,game,rank,away_rank,done){
	console.log(team,rank);
	console.log('game:');
	console.log(game);
	console.log('-----');
	var cashflow = [];
	var attendance = game[0].attendance;
	var quadrant = getQuadrant(rank);
	var stadium_income_type = getStadiumIncome(away_rank);
	console.log('home team quadrant : ',quadrant);
	console.log('stadium_income_type : ',stadium_income_type);
	console.log('attendance',attendance);
	console.log(stadium_earnings[quadrant].price[stadium_income_type]);
	console.log(stadium_earnings[quadrant]);

	//var ticket_earnings = stadium_earnings[quadrant].price[stadium_income_type] * attendance;
	 						//stadium_earnings[quadrant].ratio[stadium_income_type];
							//console.log('stadium earnings : ',ticket_earnings);
	var earnings = [];
	var costs = [];
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
					conn.query("SELECT SUM(salary) AS salaries \
								FROM ffgame.game_team_players a\
								INNER JOIN ffgame.master_player b\
								ON a.player_id = b.uid\
								WHERE a.game_team_id = ?;",
								[team.id],
								function(err,rs){
									if(!err){
										callback(err,rs[0].salaries,officials);
									}else{
										callback(err,null,null);
									}
					});
				},
				function(player_salaries,officials,callback){
					console.log(officials);
					//get 100% ticket guarantee from head of security.
					var official = getOfficial('Head of Security',officials);

					//1. TICKET EARNINGS
					var ticket_earnings = 0;
					if(official.attendance_bonus!=0){
						console.log('Head of Security bonus applied');
						//ticket_earnings = (attendance * official.attendance_bonus) *  stadium_earnings[quadrant].price[stadium_income_type];
						console.log('(',attendance,' x ',stadium_earnings[quadrant].ratio[stadium_income_type],') x',stadium_earnings[quadrant].price[stadium_income_type]);
						ticket_earnings = (attendance * stadium_earnings[quadrant].ratio[stadium_income_type]) *  stadium_earnings[quadrant].price[stadium_income_type];
					}else{
						console.log('head of security bonus not applied');
						console.log('(',attendance,' x ',stadium_earnings[quadrant].ratio[stadium_income_type],') x',stadium_earnings[quadrant].price[stadium_income_type],' x 80% [income reduced by 20%]');
						ticket_earnings = (attendance * stadium_earnings[quadrant].ratio[stadium_income_type]) *  stadium_earnings[quadrant].price[stadium_income_type] * 0.8;
						
					}
					earnings.push({name:'tickets_sold',value:ticket_earnings});
					console.log('ticket earnings ',ticket_earnings);


					//2. Earning Bonus from Commercial Director
					official = getOfficial('Commercial Director',officials);
					if(official!=null){
						console.log(ticket_earnings,'*',official.attendance_bonus);	
						earnings.push({name:'commercial_director_bonus',value:ticket_earnings*official.attendance_bonus});
					}

					//3. Earning Bonus from Marketing Manager
					official = getOfficial('Marketing Manager',officials);
					if(official!=null){
						console.log(ticket_earnings,'*',official.attendance_bonus);	
						earnings.push({name:'marketing_manager_bonus',value:ticket_earnings*official.attendance_bonus});
					}
					//4. Earning Bonus from Public Relation Officer
					official = getOfficial('Public Relation Officer',officials);
					if(official!=null){
						console.log(ticket_earnings,'*',official.attendance_bonus);	
						earnings.push({name:'public_relation_officer_bonus',value:ticket_earnings*official.attendance_bonus});
					}
					console.log(earnings);
					

					//5. operating costs
					var basic_cost = cost_mods.operating_cost * ticket_earnings;
					var op_cost = basic_cost;
					console.log(cost_mods.operating_cost,'*',ticket_earnings);
					console.log('basic_cost',basic_cost);

					//6. bonuses that reduce the costs.
					official = getOfficial('Finance Director',officials);
					if(official!=null){
						var deduct = basic_cost * official.op_cost_bonus;
						console.log(deduct);
						op_cost+=(basic_cost * official.op_cost_bonus);
					}
					official = getOfficial('Tax Consultant',officials);
					if(official!=null){
						var deduct = basic_cost * official.op_cost_bonus;
						console.log(deduct);
						op_cost+=(basic_cost * official.op_cost_bonus);
					}
					official = getOfficial('Accountant',officials);
					if(official!=null){
						var deduct = basic_cost * official.op_cost_bonus;
						console.log(deduct);
						op_cost+=(basic_cost * official.op_cost_bonus);
					}
					console.log('operational costs',op_cost);
					costs.push({name:'operating_cost',value:op_cost});

					//7. Player Salaries (to be added soon)
					console.log('player salaries : ',player_salaries);
					costs.push({name:'player_salaries',value:player_salaries});
					//8. Official Salaries
					for(var i in officials){
						console.log(officials[i].salary);
						costs.push({name:reformatName(officials[i].name),value:officials[i].salary});
					}
					console.log('costs',costs);
					callback(null,officials,earnings,costs);
				},
				function(officials,earnings,costs,callback){
					//sponsorship bonuses
					conn.query(
						"SELECT b.*,a.valid_for FROM ffgame.game_team_sponsors a\
							INNER JOIN ffgame.game_sponsorships b\
							ON a.sponsor_id = b.id\
							WHERE a.game_team_id = ? LIMIT 30",
						[team.id],
						function(err,sponsors){
							
							if(err){
								console.log(err.message);
							}
							callback(null,officials,earnings,costs,sponsors);
					});

				},
				function(officials,earnings,costs,sponsors,callback){
					//calculate the sponsorship bonus
					var sponsor_bonus = 0;
					for(var i in sponsors){
						sponsor_bonus += sponsors[i].value;
					}

					earnings.push({name:'sponsorship',value:sponsor_bonus});

					//winning bonuses
					if(game.home_score > game.away_score){
						console.log('home team is winning');
						earnings.push({name:'win_bonus',value:sponsor_bonus*0.01});
					}

					//bonuses from officials
					official = getOfficial('Commercial Director',officials);
					if(official!=null){
						earnings.push({name:'commercial_director_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}

					
					official = getOfficial('Marketing Manager',officials);
					if(official!=null){
						earnings.push({name:'marketing_manager_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}
					
					official = getOfficial('Public Relation Officer',officials);
					if(official!=null){
						
						earnings.push({name:'public_relation_officer_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}
					callback(null,{earnings:earnings,costs:costs});
				},
				function(match_money,callback){
					//save into database
					console.log(match_money);
					var sql = "INSERT INTO ffgame.game_team_expenditures\
								(game_team_id,item_name,item_type,amount,game_id,match_day)\
								VALUES ";

					var vals = [];
					for(var i in match_money.earnings){
						if(i>0){
							sql+=',';
						}
						sql+="(?,?,?,?,?,?)";
						vals.push(team.id);
						vals.push(match_money.earnings[i].name);
						vals.push(1);
						vals.push(match_money.earnings[i].value);
						vals.push(game[0].game_id);
						vals.push(game[0].matchday);
					}
					for(var j in match_money.costs){
						if(i>0||j>0){
							sql+=',';
						}
						sql+="(?,?,?,?,?,?)";
						vals.push(team.id);
						vals.push(match_money.costs[j].name);
						vals.push(2);
						vals.push(match_money.costs[j].value*-1);
						vals.push(game[0].game_id);
						vals.push(game[0].matchday);
					}
					sql+=" ON DUPLICATE KEY UPDATE\
						   amount = VALUES(amount);";

					conn.query(sql,vals,function(err,rs){
						console.log(this.sql);
						callback(null,'ok');
					});
					
				}
			],
			function(err,result){
				console.log(result);
				conn.end(function(err){
					console.log('finished calculation');
					done(err,null);
				});		
			}
		);
		
		
	});
}
/**
* calculate away revenues.
* 1. revenues come from sponsor and winning bonus
* 2. expenses include player salaries and staff salaries
*/
function calculate_away_revenue_stats(team,game_id,game,rank,away_rank,done){
	console.log('calculating away revenue');
	console.log(team,rank);
	console.log('game:');
	console.log(game);
	console.log('-----');


	

	
	var earnings = [];
	var costs = [];
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
					conn.query("SELECT SUM(salary) AS salaries \
								FROM ffgame.game_team_players a\
								INNER JOIN ffgame.master_player b\
								ON a.player_id = b.uid\
								WHERE a.game_team_id = ?;",
								[team.id],
								function(err,rs){
									if(!err){
										callback(err,rs[0].salaries,officials);
									}else{
										callback(err,null,null);
									}
					});
				},
				function(player_salaries,officials,callback){
					console.log(officials);

					//7. Player Salaries (to be added soon)
					console.log('player salaries : ',player_salaries);
					costs.push({name:'player_salaries',value:player_salaries});
					//8. Official Salaries
					for(var i in officials){
						console.log(officials[i].salary);
						costs.push({name:reformatName(officials[i].name),value:officials[i].salary});
					}
					console.log('costs',costs);
					callback(null,officials,earnings,costs);
				},
				function(officials,earnings,costs,callback){
					//sponsorship bonuses
					conn.query(
						"SELECT b.*,a.valid_for FROM ffgame.game_team_sponsors a\
							INNER JOIN ffgame.game_sponsorships b\
							ON a.sponsor_id = b.id\
							WHERE a.game_team_id = ? LIMIT 30",
						[team.id],
						function(err,sponsors){
							
							if(err){
								console.log(err.message);
							}
							callback(null,officials,earnings,costs,sponsors);
					});

				},
				function(officials,earnings,costs,sponsors,callback){
					//calculate the sponsorship bonus
					var sponsor_bonus = 0;
					for(var i in sponsors){
						sponsor_bonus += sponsors[i].value;
					}

					earnings.push({name:'sponsorship',value:sponsor_bonus});

					//winning bonuses
					if(game.home_score < game.away_score){
						console.log('away team is winning');
						earnings.push({name:'win_bonus',value:sponsor_bonus*0.01});
					}

					//bonuses from officials
					official = getOfficial('Commercial Director',officials);
					if(official!=null){
						earnings.push({name:'commercial_director_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}

					
					official = getOfficial('Marketing Manager',officials);
					if(official!=null){
						earnings.push({name:'marketing_manager_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}
					
					official = getOfficial('Public Relation Officer',officials);
					if(official!=null){
						
						earnings.push({name:'public_relation_officer_sponsor_bonus',value:sponsor_bonus*official.sponsor_bonus});
					}
					callback(null,{earnings:earnings,costs:costs});
				},
				function(match_money,callback){
					//save into database
					console.log(match_money);
					var sql = "INSERT INTO ffgame.game_team_expenditures\
								(game_team_id,item_name,item_type,amount,game_id,match_day)\
								VALUES ";

					var vals = [];
					for(var i in match_money.earnings){
						if(i>0){
							sql+=',';
						}
						sql+="(?,?,?,?,?,?)";
						vals.push(team.id);
						vals.push(match_money.earnings[i].name);
						vals.push(1);
						vals.push(match_money.earnings[i].value);
						vals.push(game[0].game_id);
						vals.push(game[0].matchday);
					}
					for(var j in match_money.costs){
						if(i>0||j>0){
							sql+=',';
						}
						sql+="(?,?,?,?,?,?)";
						vals.push(team.id);
						vals.push(match_money.costs[j].name);
						vals.push(2);
						vals.push(match_money.costs[j].value*-1);
						vals.push(game[0].game_id);
						vals.push(game[0].matchday);
					}
					sql+=" ON DUPLICATE KEY UPDATE\
						   amount = VALUES(amount);";

					conn.query(sql,vals,function(err,rs){
						console.log(this.sql);
						callback(null,'ok');
					});
					
				}
			],
			function(err,result){
				console.log(result);
				conn.end(function(err){
					console.log('finished calculation');
					done(err,null);
				});		
			}
		);
		
		
	});
}
function reformatName(str){
	str = str.toLowerCase().split(' ').join('_');
	return str;
}
function isOfficialExists(official_name,officials){
	for(var i in officials){
		if(officials[i].name==official_name){
			return true;
		}
	}
}
function getOfficial(official_name,officials){
	for(var i in officials){
		if(officials[i].name==official_name){
			return officials[i];
		}
	}
	return {
		sponsor_bonus:0,
		attendance_bonus:0,
		op_cost_bonus:0,
		transfer_bonus:0
	};
}
/* Stadium Income
*  High = Against Top 3 Teams in EPL
*  Standard = Against 4 - 10 Teams in EPL
*  Low = Against 11 - Bottom Teams in EPL
*/
function getStadiumIncome(away_rank){
	if(away_rank<=3){
		return "high";
	}if(away_rank>3 && away_rank<=10){
		return "standard"
	}else{
		return "low";
	}
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
		async.parallel([
				function(callback){
					conn.query("SELECT b.*,a.rank FROM ffgame.master_rank a\
						INNER JOIN ffgame.master_team b\
						ON a.team_id = b.uid\
						WHERE a.team_id = ?\
						ORDER BY rank",
						[game.home_id],
						function(err,team){
							callback(err,team);	
						});
				},
				function(callback){
					conn.query("SELECT b.*,a.rank FROM ffgame.master_rank a\
						INNER JOIN ffgame.master_team b\
						ON a.team_id = b.uid\
						WHERE a.team_id = ?\
						ORDER BY rank",
						[game.away_id],
						function(err,team){
							callback(err,team);	
						});
				},
			],
			function(err,result){
				//console.log(result);
				conn.end(function(err){
					done(err,{home:result[0],away:result[1]});	
				});
				
		});
		
			
	});
}