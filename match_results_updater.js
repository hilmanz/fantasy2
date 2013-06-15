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



**/
/////THE MODULES/////////
var fs = require('fs');
var path = require('path');
var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');

/////DECLARATIONS/////////
var FILE_PREFIX = config.updater_file_prefix+config.competition.id+'-'+config.competition.year;
var stat_maps = require('./libs/stats_map').getStats();

var match_results = require('./libs/match_results');
game_id = 'f2895';


/////THE LOGICS///////////////
//1st step - get master reports for recent matches.
/*match_results.getReports(game_id,function(err,rs){
	console.log('done');
});
*/

//2nd step - calculate all affected user's lineup stats.
//match_results.
var lineup_stats = require('./libs/gamestats/lineup_stats');


lineup_stats.update(game_id,0,onLineupUpdate);
function onLineupUpdate(err,is_done,next_offset){
	console.log('lineup stats updated !');
	if(!is_done){
		console.log('processing next batch');
		lineup_stats.update(game_id,next_offset,onLineupUpdate);
	}else{
		console.log('all batches has been processed');
	}
}

