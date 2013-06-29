var path = require('path');
var gameplay = require(path.resolve('./libs/api/gameplay'));


exports.getLineup = function(req,res){
	gameplay.getLineup(req.params.id,
		function(err,rs){
			if(rs!=null){
				res.send(200,rs);
			}else{
				res.send(200,[]);
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
			console.log('hello !');
			if(err){
				handleError(res);
			}else{
				res.json(200,{status:1,officials:rs.insertId});
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
					res.json(200,{status:1,budget:rs[0].budget});
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
		req.body.team_id,
		req.body.sponsor_id,
			function(err,rs){
				if(err){
					handleError(res);
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
				gameplay.getPlayerDetail(req.params.id,function(err,player){
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
function handleError(res){
	res.send(501,{error:'no data available'});
}