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
var ranks = require(path.resolve('./libs/gamestats/ranks.old'));

/////THE LOGICS///////////////
var conn = mysql.createConnection({
 	host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});



update_points_and_ranks(conn,function(err){
	console.log('Update Completed');
});	

function update_points_and_ranks(conn,done){
	async.waterfall([
		function(cb){
			console.log('updating game team points');
			var since_id = 0;
			var has_data = true;
			async.doWhilst(
				function(callback){
					conn.query("SELECT id as game_team_id FROM ffgame.game_teams \
								WHERE id > ?\
								ORDER BY id ASC LIMIT 100;",[since_id],
								function(err,rs){
									if(rs!=null && rs.length > 0){
										console.log('since_id',since_id);
										since_id = rs[rs.length-1].game_team_id;
										//update_team_points(conn,rs,function(err){
											callback();
										//});
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
function update_team_points(conn,teams,done){
	async.eachSeries(teams,function(item,next){
		conn.query("INSERT INTO ffgame_stats.game_team_points\
					(game_team_id,points)\
					SELECT game_team_id,SUM(points) AS total_points\
					FROM ffgame_stats.game_match_player_points\
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