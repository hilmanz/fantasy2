/**
populating no transfer value players.
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

var price_range = {
	High:[7000000,7500000,8000000,8500000,9000000,9500000,10000000],
	Low:[1000000,1500000,2000000],
	Middle:[4000000,4500000,5000000,5500000,6000000,6500000,7000000],
}
function getRandomInt(category) {
	var min = 0;
	var max = price_range[category].length - 1;
    return price_range[category][(Math.floor(Math.random() * (max - min + 1)) + min)];
}
async.waterfall([
	function(callback){
		open_file('transfer_value_random.csv',function(err,content){
			callback(err,content.toString());
		});
	},
	function(strData,callback){

		var lines = strData.split('\n');
		var data = [];
		for(var i in lines){
			if(lines[i].length>0){
				lines[i] = lines[i].replace(',','');
				lines[i] = lines[i].split('\"').join('');
				
				var a = lines[i].split(';');
				
				data.push({
					player_id:a[1],
					transfer_value:getRandomInt(a[4])
				});
				
			}
		}
		console.log(data);
		callback(null,data);
	},
	
	function(data,callback){
		var total_found = 0;
		console.log('total data',data.length);
		async.eachSeries(
			data,
			function(item,next){
				console.log(item);
				
				conn.query("UPDATE ffgame.master_player SET transfer_value = ? WHERE uid = ?",
							[item.transfer_value,item.player_id],
							function(err,rs){
								if(!err&&rs.length>0){
									total_found++;
								}else{
									console.log(item.name);
								}
								next();
				});
				
				next();
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
