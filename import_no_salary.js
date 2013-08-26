/**
populating no salary players.
run these script after you run import_salary.js
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



/////THE LOGICS///////////////
var conn = mysql.createConnection({
 	host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});

var salary_range = {
	H:[60000,150000],
	L:[5000,10000],
	M:[10000,50000]
}
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
async.waterfall([
	function(callback){
		open_file('Wages_no_salary.csv',function(err,content){
			callback(err,content.toString());
		});
	},
	function(strData,callback){

		var lines = strData.split('\n');
		var data = [];
		for(var i in lines){
			if(lines[i].length>0){

				lines[i] = lines[i].replace(',','');
				lines[i] = lines[i].replace('\"','');
				
				var a = lines[i].split(';');
				

				
				data.push({
					player_id:a[2],
					salary:getRandomInt(salary_range[a[4]][0],
										salary_range[a[4]][1])
				});
				
			}
		}
		
		callback(null,data);
	},
	
	function(data,callback){
		var total_found = 0;
		console.log('total data',data.length);
		async.eachSeries(
			data,
			function(item,next){
				console.log(item);
				conn.query("UPDATE ffgame.master_player SET salary = ? WHERE uid = ?",
							[item.salary,item.player_id],
							function(err,rs){
								if(!err&&rs.length>0){
									total_found++;
								}else{
									console.log(item.name);
								}
								next();
				});
				
			},function(err){
				console.log('total_found',total_found);
				callback(err,data);
			});
	}
	
],
function(err,result){
	conn.end(function(err){
		console.log('finished');
	});
});

function open_file(the_file,done){
	var filepath = path.resolve('./updates/'+the_file);
	fs.stat(filepath,onFileStat);
	function onFileStat(err,stats){
		if(!err){
			fs.readFile(filepath, function(err,data){
				if(!err){
					done(null,data);
				}else{
					done(new Error('file cannot be read !'),[]);
				}
			});
		}else{
			console.log(err.message);
			done(new Error('file is not exists !'),[]);
		}
	}
}
