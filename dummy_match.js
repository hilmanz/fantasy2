/**
* an App for generating dummy matches
*/

var fs = require('fs');
var path = require('path');
var mysql = require('mysql');
var dateformat = require('dateformat');
var config = require('./config').config;
var async = require('async');
var pool = mysql.createPool({
	host: config.database.host,
	user: config.database.username,
	password: config.database.password,
	multipleStatements: true
});

async.waterfall(
	[
		function(callback){
			getTeams(function(err,team){
				//console.log(team);
				callback(err,team);
			});
		},
		function(team,callback){
			generateListOfMatches(team,function(err,matches){
				callback(err,team,matches);
			});
		},
		function(team,matches,callback){

			generateFixtures(matches,function(err,fixtures){
				callback(err,fixtures);	
			});
		},
		function(fixtures,callback){
			saveFixtures(fixtures,function(err,fixtures){
				callback(null,fixtures);	
			});
		}
	],
	function(err,result){
		/*
		var matchlist = {};
		for(var i in result){
			for(var j in result[i].games){
				if(typeof matchlist[result[i].games[j][0]] === 'undefined'){
					matchlist[result[i].games[j][0]] = 0;
				}
				if(typeof matchlist[result[i].games[j][1]] === 'undefined'){
					matchlist[result[i].games[j][1]] = 0;
				}
				matchlist[result[i].games[j][0]]++;
				matchlist[result[i].games[j][1]]++;
			}
		}
		console.log(matchlist);
		*/
		for(var i in result){
			console.log('Match day',result[i].matchday);
			console.log(result[i].games);
		}

		pool.end(function(err){
			console.log('done');
		});
	}
);
function saveFixtures(fixtures,done){
	
	async.eachSeries(fixtures,function(fixture,callback){
		async.eachSeries(fixture.games,function(item,onSaveItemDone){
			pool.getConnection(function(err,conn){
				var game_id = 'f'+fixture.matchday+dateformat(new Date(),'yyyymmddhhMMss')+''+(Math.round(Math.random()*9999));
				console.log(game_id);
				conn.query('INSERT INTO ffgame.game_fixtures\
							(game_id,home_id,away_id,period,matchday,\
							competition_id,session_id,home_score,away_score,\
							attendance,is_dummy,is_processed)\
							VALUES\
							(?,?,?,?,?,?,?,0,0,0,1,0);',
							[game_id,item[0],item[1],'PreMatch',fixture.matchday,
							config.competition.id,config.competition.year],
							function(err,rs){
								console.log(this.sql);
								if(err){
									console.log(err.message);
								}
								conn.end(function(err){
									onSaveItemDone();				
								});
					
				});
			});
			
		},function(err){
			callback();	
		});
		
	},function(err){
		console.log('new fixtures created');
		done(err,fixtures);
	});
}

function getTeams(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame.master_team LIMIT 1000",
					[],
					function(err,team){
						console.log(this.sql);
						conn.end(function(err){
						done(err,team);
					});
		});
	});
}
function generateListOfMatches(team,done){
	var games = [];

	//generate game list
	for(var i=0;i<team.length;i++){
		for(var j=0; j < team.length; j++){
			if(team[i].uid!=team[j].uid){
				games.push([team[i].uid,team[j].uid]);
			}
		}
	}
	games = shuffle(games);
	//console.log(games);
	done(null,games);
}

function generateFixtures(games,done){
	var fixtures = [];
	
	//generate 38 days of matches
	var match = [];
	for(var i=0;i<43;i++){
		var fixture = {
			matchday:(i+1),
			games:[]
		};
		match = [];
		//console.log(games);
		for(var j in games){
			if(games[j]!=null){
				//console.log('game',games[j]);
				//console.log('aranged',match);
				if(!isExist(games[j][0],match)&&!isExist(games[j][1],match)){
					console.log(j,games[j][0],'-',games[j][1]);
				//	console.log('pushed');
					match.push(games[j][0]);
					match.push(games[j][1]);
					//console.log(games[j]);
					fixture.games.push(games[j]);
					games[j] = null;
				}
			}
		}
		console.log(fixture.games.length);
		console.log('------------------');
		
		fixtures.push(fixture);
	}
	for(var x in games){
		if(games[x]!=null){
			console.log('->',games[x]);
		}
	}
	done(null,fixtures);
}
function isExist(subject,items){
	for(var i in items){
		if(items[i]==subject){
			return true;
		}
	}
}

function shuffle(o){ //v1.0
    for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};
