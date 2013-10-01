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


async.waterfall([
	function(callback){
		open_file('transfer_value_v2.csv',function(err,content){
			callback(err,content.toString());
		});
	},
	function(strData,callback){

		var lines = strData.split('\n');
		var data = [];
		for(var i in lines){
			if(lines[i].length>0){
				
				//lines[i] = lines[i].replace(',','');
				lines[i] = lines[i].split('\"').join('');
				
				var a = lines[i].split(',');
				
				//console.log(a);
				
				data.push({
					last_name:a[0],
					team_id:a[1],
					transfer_value:parseInt(a[3])
				});
				
			}
		}
		//console.log(data);
		callback(null,data);
	},
	
	function(data,callback){
		var total_found = 0;
		console.log('total data',data.length);
		async.eachSeries(
			data,
			function(item,next){
				
				conn.query("INSERT INTO ffgame.tmp_transfer(last_name,team_id,transfer_value)\
							VALUES(?,?,?)",
							[item.last_name,item.team_id,item.transfer_value],
							function(err,rs){
								if(!err){
									console.log(item.last_name,item.team_id,item.transfer_value,'DONE');
								}else{
									console.log(item.last_name,item.team_id,item.transfer_value,'FAILED');
								}
								next();
				});
				
			},function(err){
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
