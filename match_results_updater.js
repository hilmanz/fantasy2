/**
match_results_updater.js
these app will check if there's a new matchresults updates available.
it will load the data.. parse the xml.. and update the stats for each player's stats.

steps : 
1. check if there's a new file in the folder by crosschecking the file lists with those in database.
2. if there's a new file, we read the xml. and then update our master_report stats.
3. based on the master_report stats, we update individual user's starting players stats
4. flag the file so we dont have to process it anymore.

remember, each files is related to 1 game_id. so every summary must be grouped by game_id.
PS : these only process the master data.

@TODO
let's process unprocessed match.
**/
/////THE MODULES/////////
var fs = require('fs');
var path = require('path');
var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');
var mysql = require('mysql');

/////DECLARATIONS/////////
var FILE_PREFIX = config.updater_file_prefix+config.competition.id+'-'+config.competition.year;
var stat_maps = require('./libs/stats_map').getStats();


var match_results = require('./libs/match_results');
var lineup_stats = require('./libs/gamestats/lineup_stats');
var business_stats = require('./libs/gamestats/business_stats');
var ranks = require(path.resolve('./libs/gamestats/ranks'));

/////THE LOGICS///////////////
var conn = mysql.createConnection({
 	host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});

conn.query("SELECT * FROM ffgame.game_fixtures \
		WHERE is_processed=0 \
		ORDER BY id ASC LIMIT 10;",[],function(err,games){
			conn.end(function(err){
				generateReports(games);
		});
});

function generateReports(games){
	async.eachSeries(games,function(item,callback){
		//console.log(item.game_id);
		process_report(item.game_id,function(err,result){
			callback();	
		});
	},function(err){
		//console.log('Done generating report.');
		match_results.done();
		lineup_stats.done();
		business_stats.done();
	});
}


/*
@todo generate master player performance summary ( ffgame_stats.master_player_performance)
*/
function process_report(game_id,done){
	console.log('process report #',game_id);
	async.waterfall([
		//1st step - get master reports for recent matches.
		function(callback){
			match_results.getReports(game_id,function(err,rs){
				callback(err,rs);
			});
			//callback(null,null);
		},
		function(result,callback){
			console.log('getReports -> ',result);
			if(result.SoccerFeed.SoccerDocument.MatchData.MatchInfo.Period=='FullTime'){
				console.log('already FullTime');
				console.log('update lineup');
				//@todo
				//update lineup hanya di lakukan jika game yang bersangkutan sudah FT
				var is_finished = false;
				var offset = 0;
				async.doWhilst(
					function(cb){
						console.log('offset',offset);
						lineup_stats.update(game_id,offset,function(err,is_done,next_offset){
							offset = next_offset;
							is_finished = is_done;
							cb();
						});
					},
					function(){
						console.log('is_finished',is_finished);
						if(!is_finished){
							return true;
						}
					},
					function(err){
						async.waterfall([
							function(n){
								console.log('updating business stats');
								business_stats.update(game_id,0,function(err){
									console.log('business stats update completed');
									console.log('all batches has been processed');
									n(err);
								});
							},
							function(n){
								//i dont think its a good idea to run rank update after  each game complete
								//if we run many matchresultsupdater instances, the race condition will be occur.
								ranks.update(function(err,rs){
									n(err,rs);
								});
							}
						],
						function(err,rs){
							callback(err,'done');
						});
						
					}
				);
			}else{
				callback(null,'ON GOING MATCH');
			}
			
			
			/*
			business_stats.update(game_id,0,function(err){
				console.log('business stats update completed');
				console.log('all batches has been processed');
				callback(err,'done');
			});*/
		}
	],
	function(err,result){
		done(err,result);
		console.log(result);
	});
}



