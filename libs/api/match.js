/**
api related to match
*/

var crypto = require('crypto');
var fs = require('fs');
var path = require('path');
var xmlparser = require('xml2json');
var async = require('async');
var config = require(path.resolve('./config')).config;
var mysql = require('mysql');
var dateFormat = require('dateformat');
var redis = require('redis');
var formations = require(path.resolve('./libs/game_config')).formations;
var stats_map = require(path.resolve('./libs/stats_map')).stats_map;


function prepareDb(){
	var connection = mysql.createConnection({
  		host     : config.database.host,
	   	user     : config.database.username,
	   	password : config.database.password,
	});
	
	return connection;
}

function fixtures(done){
	var conn = prepareDb();
	conn.query("SELECT a.id,\
				a.game_id,a.home_id,b.name as home_name,a.away_id,c.name as away_name,a.home_score,a.away_score,\
				a.matchday,a.period,a.session_id,a.attendance\
				FROM ffgame.game_fixtures a\
				INNER JOIN ffgame.master_team b\
				ON a.home_id = b.uid\
				INNER JOIN ffgame.master_team c\
				ON a.away_id = c.uid\
				ORDER BY a.matchday\
				LIMIT 100;",
				[],
				function(err,match){
					conn.end(function(e){
						done(err,match);						
					});
				});
}
function next_match(team_id,done){
	var conn = prepareDb();
	conn.query("",
				[],
				function(err,match){
					conn.end(function(e){
						done(err,match);						
					});
				});
}
function results(game_id,done){
	var conn = prepareDb();
	async.waterfall([
				function(callback){
					//get game details
					conn.query("SELECT a.id,\
				a.game_id,a.home_id,b.name as home_name,a.away_id,c.name as away_name,a.home_score,a.away_score,\
				a.matchday,a.period,a.session_id,a.attendance\
				FROM ffgame.game_fixtures a\
				INNER JOIN ffgame.master_team b\
				ON a.home_id = b.uid\
				INNER JOIN ffgame.master_team c\
				ON a.away_id = c.uid\
				WHERE a.game_id = ?\
				LIMIT 1;",[game_id],
					function(err,game){
						
						callback(err,game[0]);
					});
				},
				function(game,callback){

					//get overall stats
					conn.query("SELECT team_id,stats_name,SUM(stats_value) AS total \
								FROM ffgame_stats.master_match_result_stats \
								WHERE game_id=? GROUP BY team_id,stats_name;",
					[game_id],
					function(err,stats){
					
						callback(err,game,stats);
					});
				},
				function(game,stats,callback){

					//get player stats for these match
					conn.query("SELECT a.team_id,a.player_id,b.name,b.position,a.stats_name,SUM(a.stats_value) AS total \
								FROM ffgame_stats.master_match_result_stats a\
								INNER JOIN\
								ffgame.master_player b\
								ON a.player_id = b.uid\
								WHERE a.game_id = ?\
								GROUP BY a.team_id,a.player_id,a.stats_name \
								LIMIT 30000;",
						[game_id],
					function(err,player_stats){
					
						callback(err,game,stats,player_stats);
					});
				},
				function(game,stats,player_stats,callback){
					//wrapping all up
					getMatchResultData(game,stats,player_stats,function(err,result){
						callback(err,result);
					});
				}
			],
			function(err,result){
				conn.end(function(e){
					console.log(result);
					done(err,result);	
				});
			}
		);
}
function getMatchResultData(game,stats,player_stats,callback){
	var result = [];

	var home = {
		team_id:game.home_id,
		name:game.home_name,
		score:game.home_score,
		overall_stats:{},
		player_stats:{}
	};
	var away = {
		team_id:game.away_id,
		name:game.away_name,
		score:game.away_score,
		overall_stats:{},
		player_stats:{}
	};
	
	console.log(stats);
	//console.log(stats_map);
	var stats_list = require(path.resolve('./libs/stats_map')).getStats();
	var home_overall_stats = {};
	var away_overall_stats = {};
	for(var i in stats_map){
		home_overall_stats[i] = 0;
		away_overall_stats[i] = 0;
	}
	console.log(home_overall_stats);
	//generate overall stats for each team
	for(var i in stats){
		if(typeof stats_list[stats[i].stats_name]!=='undefined'){

			if(stats[i].team_id == home.team_id){
				home_overall_stats[stats_list[stats[i].stats_name]] += stats[i].total;
			}else{
				away_overall_stats[stats_list[stats[i].stats_name]] += stats[i].total;
			}
		}
	}
	home.overall_stats =  home_overall_stats;
	away.overall_stats = away_overall_stats;
	
	



	//process player's stats
	for(var i in player_stats){
		if(typeof stats_list[player_stats[i].stats_name]!=='undefined'){
			if(player_stats[i].team_id == home.team_id){
				if(typeof home.player_stats[player_stats[i].player_id] === 'undefined'){
					home.player_stats[player_stats[i].player_id] = {
						name:player_stats[i].name,
						position:player_stats[i].position,
						stats:{}
					};
				}
				if(typeof home.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] === 'undefined'){
					 home.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] = 0;
				}
				 home.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] += player_stats[i].total;
			}else{
				if(typeof away.player_stats[player_stats[i].player_id] === 'undefined'){
					away.player_stats[player_stats[i].player_id] = {
						name:player_stats[i].name,
						position:player_stats[i].position,
						stats:{}
					};
				}
				if(typeof away.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] === 'undefined'){
					 away.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] = 0;
				}
				 away.player_stats[player_stats[i].player_id]['stats'][stats_list[player_stats[i].stats_name]] += player_stats[i].total;
			}
		}
	}

	result.push(home);
	result.push(away);
	console.log(away.player_stats);
	callback(null,result);

}
exports.fixtures = fixtures;
exports.results = results;