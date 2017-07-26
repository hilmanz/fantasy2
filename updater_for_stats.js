/**
the application which responsible for updating game database with OPTA data.
the application will check if there's a  new file exists in data folder.
use these only to update StatsAPI database.
**/
var fs = require('fs');
var path = require('path');
var config = require('./config_ucl').config;
var xmlparser = require('xml2json');
var master = require('./libs/master_for_stats');

var FILE_PREFIX = config.updater_file_prefix+config.competition.id+'-'+config.competition.year;


//first check if the file is exists
var squad_file = FILE_PREFIX+'-squads.xml';
open_squad_file(squad_file,function(err,doc){
		//console.log(xmlparser.toJson(doc.toString()));
		console.log('opening file');
		master.update_team_data(JSON.parse(xmlparser.toJson(doc.toString())),onDataProcessed);
});


function onDataProcessed(team_data){
	console.log(team_data);
	console.log('squad data updated !');
}

function open_squad_file(squad_file,done){
	var filepath = path.resolve('./data/'+squad_file);
	fs.stat(filepath,onFileStat);
	function onFileStat(err,stats){
		if(!err){
			fs.readFile(filepath, function(err,data){
				if(!err){
					done(null,data);
				}else{
					handleError(err);
				}
			});
		}else{
			console.log(err.message);
			handleError(err);
		}
	}
}
function handleError(err){
	done(err,'<xml><error>1</error></xml>');
}
