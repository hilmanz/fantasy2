/*
* the module for dealing with punishment rules.
* there's 2 kind of punishment.
* income cuts, or balance deduction.
* so every rule must returns an object that consists income_cuts and balance_deduction.
*/
//income dikurangi untuk 2 home game berikutnya atau sampai transfer window berikutnya
var home_income_cuts = function(){
	return {income_cuts:0.75,balance_cuts:0,terms:{type:'2home_or_next_transfer',amount:2}};
}
exports.home_income_cuts = home_income_cuts;


//balance dikurangi untuk 2 home game berikutnya atau sampai transfer window berikutnya
var home_balance_cuts = function(){
	return {income_cuts:0,balance_cuts:150000,terms:{type:'2home_or_next_transfer',amount:2}};
}
exports.home_balance_cuts = home_balance_cuts;

//balance dikurangi selama 4 minggu berturut2
var away_balance_cuts = function(){
	return {income_cuts:0,balance_cuts:500000,terms:{type:'weekly',amount:4}};
}
exports.away_balance_cuts = away_balance_cuts;

exports.execute_punishment = function(conn,type,game_id,game_team_id,callback){
	var async = require('async');
	async.waterfall([],function(err,rs){
		callback(err,rs);
	});
}
exports.check_violation = function(conn,game_id,game_team_id,original_team_id,callback){
	var async = require('async');
	var S = require('string');
	async.waterfall([
		function(cb){
			//check the current lineup setup in history
			conn.query("SELECT b.team_id,COUNT(b.team_id) AS total \
						FROM ffgame.game_team_players a\
						INNER JOIN ffgame.master_player b\
						ON a.player_id = b.uid \
						WHERE game_team_id=? GROUP BY team_id;",
						[game_team_id],
						function(err,rs){
							console.log(S(this.sql).collapseWhitespace().s);
							console.log(rs);
							var total_players = 0;
							var total_ori = 0;
							if(rs!=null && rs.length > 0){
								console.log(original_team_id);
								for(var i in rs){
									total_players += rs[i].total;
									if(rs[i].team_id == original_team_id){
										total_ori+=rs[i].total;
									}
								}
							}
							cb(err,{original:total_ori,
									total:total_players});
						});
		},
		function(check,cb){
			/*if((check.original/check.total) < 0.5){

			}*/
			//check jenis game.. home apa away ?
			conn.query(
				"SELECT home_id,away_id FROM ffgame.game_fixtures WHERE game_id = ? LIMIT 1;",
				[game_id],
				function(err,rs){
					var type = '';
					try{
						if(rs[0].home_id == original_team_id){
							type='home';
						}else{
							type='away';
						}
					}catch(e){
						type = '';
					}
					cb(err,check,type);
			});
			
		},
		function(check,game_type,cb){
			if((check.original/check.total) < 0.5){
				console.log(check,game_type);
				add_rules(conn,game_id,game_team_id,game_type,function(err){
					cb(null,null);
				});
				
			}else{
				cb(null,null);
			}
		}
	],
	function(err,rs){
		callback(err,rs);
	});
	
}

function add_rules(conn,game_id,game_team_id,game_type,done){
	var async = require('async');

	if(game_type=='home'){
		console.log('#',game_team_id,'home nih');
		async.waterfall([
			function(cb){
				var can_punish = true;
				//we can only give new punishment if all punishment is done.
				conn.query("SELECT COUNT(*) AS total FROM ffgame.game_punishments \
							WHERE game_team_id = ? AND punishment='home_income_cuts' AND n_status=0;",
							[game_team_id],function(err,rs){
								try{
									if(rs[0].total > 0){

										can_punish = false;
									}
								}catch(e){
									can_punish = false;
								}
								cb(err,can_punish);
							});
			},
			function(can_punish,cb){
				console.log('#',game_team_id,'home_income_cuts');
				if(can_punish){
					console.log('can punish');
					var setting = home_income_cuts();
					async.times(setting.terms.amount,function(n,next){
						conn.query("INSERT INTO ffgame.game_punishments\
								(game_id,game_team_id,game_type,punishment,n_status,submit_dt)\
								VALUES\
								(?,?,?,?,0,NOW());",
								[game_id,game_team_id,game_type,'home_income_cuts'],
								function(err,rs){
									console.log(sqlOut(this.sql));
									next(err,rs);
								});
						},
					function(err,results){
						cb(err);
					});	
				}else{
					console.log('cannot punish');
					cb(null);
				}
				
			},
			function(cb){
				var can_punish = true;
				//we can only give new punishment if all punishment is done.
				conn.query("SELECT COUNT(*) AS total FROM ffgame.game_punishments \
							WHERE game_team_id = ? AND punishment='home_balance_cuts' AND n_status=0;",
							[game_team_id],function(err,rs){
								try{
									if(rs[0].total > 0){
										can_punish = false;
									}
								}catch(e){
									can_punish = false;
								}
								cb(err,can_punish);
							});
			},
			function(can_punish,cb){
				console.log('#',game_team_id,'home_balance_cuts');
				if(can_punish){
					console.log('can punish');
					var setting = home_balance_cuts();
					async.times(setting.terms.amount,function(n,next){
						conn.query("INSERT INTO ffgame.game_punishments\
								(game_id,game_team_id,game_type,punishment,n_status,submit_dt)\
								VALUES\
								(?,?,?,?,0,NOW());",
								[game_id,game_team_id,game_type,'home_balance_cuts'],
								function(err,rs){
									console.log(sqlOut(this.sql));
									next(err,rs);
								});
						},
					function(err,results){
						cb(err);
					});	
				}else{
					console.log('cannot punish');
					cb(null,null);
				}
				
			},
		],

		function(e,r){
			done(e);
		})

	}else if(game_type=='away'){
		console.log('#',game_team_id,'away nih');
		async.waterfall([
			function(cb){
				var can_punish = true;
				//we can only give new punishment if all punishment is done.
				conn.query("SELECT COUNT(*) AS total FROM ffgame.game_punishments \
							WHERE game_team_id = ? AND punishment='away_balance_cuts' AND n_status=0;",
							[game_team_id],function(err,rs){
								try{
									if(rs[0].total > 0){

										can_punish = false;
									}
								}catch(e){
									can_punish = false;
								}
								cb(err,can_punish);
							});
			},
			function(can_punish,cb){
				console.log('#',game_team_id,'away_balance_cuts');
				if(can_punish){
					console.log('can punish');
					var setting = away_balance_cuts();
					async.times(setting.terms.amount,function(n,next){
						conn.query("INSERT INTO ffgame.game_punishments\
								(game_id,game_team_id,game_type,punishment,n_status,submit_dt)\
								VALUES\
								(?,?,?,?,0,NOW());",
								[game_id,game_team_id,game_type,'away_balance_cuts'],
								function(err,rs){
									console.log(sqlOut(this.sql));
									next(err,rs);
								});
						},
					function(err,results){
						cb(err);
					});	
				}else{
					console.log('cannot punish');
					cb(null);
				}
				
			},
		],

		function(e,r){
			done(e);
		})

	}else{
		console.log('#',game_team_id,'no game type specified');
		done(null);
	}
}
function sqlOut(sql){
	var S = require('string');
	
	return S(sql).collapseWhitespace().s
}
