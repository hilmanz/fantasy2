var path = require('path');
var gameplay = require(path.resolve('./libs/api/gameplay'));


exports.setPool = function(pool){
	gameplay.setPool(pool);
}

exports.getLineup = function(req,res){
	gameplay.getLineup(req.params.id,
		function(err,rs){
			if(rs!=null){
				res.send(200,rs);
			}else{
				//send the default configurations
				res.send(200,{lineup:[],formation:'4-4-2'});
			}
	});
}
exports.getPlayers = function(req,res){
	gameplay.getPlayers(req.params.id,function(err,rs){
		if(rs!=null){
			res.json(200,rs);
		}else{
			res.send(200,[]);
		}
	});
}
exports.getTransferWindow = function(req,res){
	gameplay.getTransferWindow(function(err,rs){
		if(rs!=null){
			
			res.json(200,rs);
		}else{
			res.send(200,[]);
		}
	});
}
exports.setLineup = function(req,res){
	
	gameplay.setLineup(req.body.team_id,
						JSON.parse(req.body.players),
						req.body.formation,
		function(err,rs){

			if(err){
				console.log(err.message);
				handleError(res);
			}else{
				if(rs!=null){
					res.json(200,{status:1,lineup:rs});
				}else{
					res.send(200,{status:0});
				}
			}
	});
	
}
exports.fixtures = function(req,res){
	gameplay.match.fixtures(function(err,rs){
		if(err){
			
			handleError(res);
		}else{
			if(rs!=null){
				res.json(200,{status:1,matches:rs});
			}else{
				res.send(200,{status:0});
			}
		}
	});
}
exports.official_list = function(req,res){
	gameplay.officials.official_list(req.params.game_team_id,
		function(err,rs){
			if(err){
				handleError(res);
			}else{
				if(rs!=null){
					res.json(200,{status:1,officials:rs});
				}else{
					res.send(200,{status:0});
				}
			}
	});
}
exports.hire_staff = function(req,res){
	gameplay.officials.hire_official(req.body.team_id,
									 req.body.official_id,
		function(err,rs){
			console.log(rs);
			if(err){
				handleError(res);
			}else{
				if(typeof rs !== 'undefined'){
					res.json(200,{status:1,officials:rs});
				}else{
					res.json(200,{status:0,officials:rs});
				}
			}
	});
}
exports.fire_staff = function(req,res){
	gameplay.officials.remove_official(req.body.team_id,
									 req.body.official_id,
		function(err,rs){
			if(err){
				handleError(res);
			}else{
				res.json(200,{status:1,message:'the staff is been removed successfully !'});
			}
	});
}
exports.getBudget = function(req,res){
	gameplay.getBudget(req.params.game_team_id,
		function(err,rs){
			if(err){
				handleError(res);
			}else{
				if(rs!=null){
					try{
						res.json(200,{status:1,budget:rs[0].budget});
					}catch(e){
						console.log('failed to get budget',rs,'at service/gameplay line 114');
						res.send(200,{status:0});
					}
				}else{
					res.send(200,{status:0});
				}
			}
	});
}
exports.getSponsors = function(req,res){
	gameplay.sponsorship.getAvailableSponsorship(
		req.params.team_id,
			function(err,rs){
				if(err){
					handleError(res);
				}else{
					if(rs!=null){
						res.json(200,{status:1,sponsorships:rs});
					}else{
						res.send(200,{status:0});
					}
				}
		});
}
exports.getActiveSponsors = function(req,res){
	gameplay.sponsorship.getActiveSponsors(
		req.params.game_team_id,
			function(err,rs){
				if(err){
					handleError(res);
				}else{
					if(rs!=null){
						res.json(200,{status:1,sponsors:rs});
					}else{
						res.send(200,{status:0});
					}
				}
		});
}
exports.applySponsorship = function(req,res){
	gameplay.sponsorship.applySponsorship(
		req.body.game_id,
		req.body.matchday,
		req.body.game_team_id,
		req.body.sponsor_id,
			function(err,rs){
				if(err){
					console.log('error:',err.message);
					if(err.message=='ALREADY_HAVE_SPONSOR'){
						res.json(200,{status:2,message:'Already have a sponsor'});
					}else if(err.message=='ALREADY_APPLIED'){
						res.send(200,{status:0,message:'Sponsorship Denied !'});
					}else{
						handleError(res);	
					}
				}else{
					if(rs){
						res.json(200,{status:1,message:'Sponsorship Accepted'});
					}else{
						res.send(200,{status:0,message:'Sponsorship Denied !'});
					}
				}
		});
};

exports.match_results = function(req,res){
	gameplay.match.results(
		req.params.game_id,
		
			function(err,rs){
				if(err){
					handleError(res);
				}else{
					if(rs){
						res.json(200,{status:1,data:rs});
					}else{
						res.send(200,{status:0,data:[]});
					}
				}
		});
}
exports.match_results_for_user_team = function(req,res){
	gameplay.match.getMatchResultForUserTeam(
		req.params.game_team_id,
		req.params.game_id,
		
			function(err,rs){
				if(err){
					handleError(res);
				}else{
					if(rs){
						res.json(200,{status:1,data:rs});
					}else{
						res.send(200,{status:0,data:[]});
					}
				}
		});
}
exports.player_data = function(req,res){
	var async = require('async');
	async.waterfall(
		[
			function(callback){
				gameplay.getPlayerDetail(req.params.id,function(err,player){
					callback(err,player);
				});
			},
			function(player,callback){
				gameplay.getPlayerStats(
							req.params.id,
							function(err,rs){
								callback(err,{player:player,stats:rs});	
							});
			},
			function(result,callback){
				if(result.player != null){
					gameplay.getPlayerOverallStats(0,
												   result.player.player_id,
												   function(err,rs){
						result.overall_stats = rs;
						callback(err,result);
					});
				}else{
					//we don't need to throw an error, 
					//so just return empty result.
					callback(null,result);
				}
			},
			function(result,callback){
				
				if(result.player != null){
					gameplay.getPlayerDailyTeamStats(0,
												   result.player.player_id,
												   result.player.position,
												   function(err,rs){
						result.daily_stats = rs;
						callback(err,result);
					});
				}else{
					//we don't need to throw an error, 
					//so just return empty result.
					callback(null,result);
				}
			}
		],
		function(err,result){
			if(err){
				handleError(res);
			}else{
				if(result){
					res.json(200,{status:1,data:result});
				}else{
					res.send(200,{status:0,data:{player:{},stats:[]}});
				}
			}
		}
	);
	
}

exports.player_team_data = function(req,res){
	var async = require('async');
	async.waterfall(
		[
			function(callback){
				gameplay.getTeamPlayerDetail(req.params.game_team_id,
											 req.params.id,
											 function(err,player){
					callback(err,player);
				});
			},
			function(player,callback){
				gameplay.getPlayerTeamStats(
							req.params.game_team_id,
							req.params.id,
							function(err,rs){

								callback(err,{player:player,stats:rs});	
							});
			},
			function(result,callback){
				if(result.player != null){
					gameplay.getPlayerOverallStats(req.params.game_team_id,
												   result.player.player_id,
												   function(err,rs){
						result.overall_stats = rs;
						callback(err,result);
					});
				}else{
					//we don't need to throw an error, 
					//so just return empty result.
					callback(null,result);
				}
			},
			function(result,callback){
				
				if(result.player != null){
					gameplay.getPlayerDailyTeamStats(req.params.game_team_id,
												   result.player.player_id,
												   result.player.position,
												   function(err,rs){
						result.daily_stats = rs;
						callback(err,result);
					});
				}else{
					//we don't need to throw an error, 
					//so just return empty result.
					callback(null,result);
				}
			}
		],
		function(err,result){
			console.log(result);
			if(err){
				handleError(res);
			}else{
				if(result){
					res.json(200,{status:1,data:result});
				}else{
					res.send(200,{status:0,data:{player:{},stats:[]}});
				}
			}
		}
	);
	
}
exports.financial_statements = function(req,res){
	var async = require('async');
	async.waterfall([
		function(callback){
			gameplay.getBudget(req.params.game_team_id,function(err,result){
				if(result==null){
					callback(new Error('budget not found'),null);
				}else{
					callback(err,result[0]);	
				}
				
			});
		},
		function(budget,callback){
			gameplay.getFinancialStatement(req.params.game_team_id,function(err,result){
				try{
					result.budget = budget.budget;	
					callback(err,result);
				}catch(e){
					callback(err,null);
				}
				
				
			});
		}
	],
	function(err,result){
		if(err){
			handleError(res);
		}else{
			if(result){
				res.json(200,{status:1,data:result});
			}else{
				res.send(200,{status:0});
			}
		}
	});
	
}
exports.weekly_finance = function(req,res){
	var async = require('async');
	async.waterfall([
		function(callback){
			gameplay.getBudget(req.params.game_team_id,function(err,result){
				callback(err,result[0]);
			});
		},
		function(budget,callback){
			gameplay.getWeeklyFinance(req.params.game_team_id,req.params.week,
				function(err,result){
					callback(err,{budget:budget.budget,
								  transactions:result});
			});
		}
	],
	function(err,result){
		if(err){
			handleError(res);
		}else{
			if(result){
				res.json(200,{status:1,data:result});
			}else{
				res.send(200,{status:0});
			}
		}
	});
	
}
exports.next_match = function(req,res){
	gameplay.next_match(req.params.team_id,function(err,match){
		if(!err){
			res.json(200,{status:1,match:match[0]});
		}else{
			res.send(200,{status:0});
		}
	});
}
exports.best_match = function(req,res){
	gameplay.best_match(req.params.game_team_id,function(err,result){
		if(!err){
			res.json(200,{status:1,data:result});
		}else{
			res.send(200,{status:0});
		}
	});
}
exports.best_player = function(req,res){
	gameplay.best_player(req.params.game_team_id,function(err,result){
		if(!err){
			res.json(200,{status:1,data:result});
		}else{
			res.send(200,{status:0});
		}
	});
}
exports.last_earning = function(req,res){
	gameplay.last_earning(req.params.game_team_id,function(err,result){
		if(!err){
			res.json(200,{status:1,data:result});
		}else{
			res.send(200,{status:0});
		}
	});
}
exports.last_expenses = function(req,res){
	gameplay.last_expenses(req.params.game_team_id,function(err,result){
		if(!err){
			res.json(200,{status:1,data:result});
		}else{
			res.send(200,{status:0});
		}
	});
}
exports.get_venue = function(req,res){
	gameplay.getVenue(req.params.team_id,function(err,venue){
		if(!err){
			res.json(200,{status:1,venue:venue});
		}else{
			res.send(200,{status:0});
		}
	});
}
function handleError(res){
	res.send(501,{error:'no data available'});
}
//sale a player
exports.sale = function(req,res){
	gameplay.sale(
		req.body.window_id,
		req.body.game_team_id,
		req.body.player_id,
		function(err,result){
			if(err){
				
				if(err.message=='INVALID_TRANSFER_WINDOW'){

					res.send(200,{status:-1,
									message:'you cannot sale a player who already bought from the same transfer window'});
				}else{
					handleError(res);
				}
				
			}else{
				if(result!=null){
					res.send(200,{status:1,data:result,message:'the player has been successfully sold.'});
				}else{
					res.send(200,{status:0,message:'Oops, cannot sale the player.'});
				}	
			}
		}
	);
}
//buy a player
exports.buy = function(req,res){
	gameplay.buy(
		req.body.window_id,
		req.body.game_team_id,
		req.body.player_id,
		function(err,result){
			if(err){
				
				if(err.message=='no money'){
					res.send(200,{status:2,data:{player:{},stats:[]}});
				}else if(err.message == 'INVALID_TRANSFER_WINDOW'){
					res.send(200,{status:-1,
									message:'you cannot sale a player who already bought from the same transfer window'});
				}else{
					handleError(res);	
				}
			}else{
				if(result!=null){
					res.send(200,{status:1,data:result,message:'the player has been successfully bought.'});
				}else{
					res.send(200,{status:0,message:'Oops, cannot sale the player.'});
				}	
			}
		}
	);
}
//get the list of match result statistics
exports.leaderboard = function(req,res){
	gameplay.leaderboard(function(err,rs){
		if(err){
			handleError(res);
		}else{
			if(rs!=null){
				res.json(200,{status:1,data:rs});
			}else{
				res.send(200,{status:0});
			}
		}
	});
}