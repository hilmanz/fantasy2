/**
 * Module dependencies.
 */

var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('http')
  , path = require('path');
var mysql = require('mysql');
var redis = require('redis');
var dummy_api_key = '1234567890';
var auth = require('./libs/api/auth');
var config = require('./config').config;
var async = require('async');
var request = require('request');
var argv = require('optimist').argv;

//mysql pool
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});


var app = express();
var RedisStore = require('connect-redis')(express);

// all environments
var app_port =  config.port;

if(typeof argv.port !== 'undefined'){
	app_port = argv.port;
}

app.set('port', app_port);
app.set('views', __dirname + '/views');
app.set('view engine', 'jade');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser('i die, you die, everybody die'));
app.use(express.session({ store: new RedisStore(config.redis) }));

var client = redis.createClient(config.redis.port,config.redis.host);
client.on("error", function (err) {
    console.log("Error " + err);
});
app.use(function(req,res,next){
	//bind everything we need
	req.redisClient = client;
	next();
});
app.use(app.router);

app.use(express.static(path.join(__dirname, 'public')));

// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', function(req,res){
	res.send(200,{status:1});
});

app.get('/simulator/reset', [], function(req,res){
	resetData(function(err,rs){
		res.send(200,{status:1});	
	});
});
app.get('/simulator/update', [], function(req,res){
	simulate(function(err,rs){
		res.send(200,{status:1,data:rs});	
	});
	
});

app.get('/get/:game_id',[],function(req,res){
	
	client.get('lvrt_'+req.params.game_id,function(err,resp){
		
		var data = JSON.parse(resp);
		res.send(200,{status:1,data:data});
	});
});

http.createServer(app).listen(3999, function(){
  console.log('ready');
});


function simulate(done){
	var game_id = 'f750260';
	var str_game_id = '750260';

	pool.getConnection(function(err,conn){
		async.waterfall([
			function(cb){
				//get all pushlogs 
				conn.query("SELECT * FROM optadb.pushlogs WHERE gameId=? AND feedType = 'F9' ORDER BY id ASC;",
							[str_game_id],function(err,rs){
								cb(err,rs);
							});
			},
			function(rs,cb){
				client.get('simulator_'+game_id,function(err,data){
					var d = rs[parseInt(data)];
					var fs = require('fs');
					fs.writeFile("./data/"+d.saved_file,d.content, 
					function(err) {
					    if(err) {
					        console.log(err.message);
					    } else {
					        console.log("The file was saved!");
					    }
					    cb(err,d.saved_file);
					});
				});
			},
			function(filename,cb){
				console.log('http://localhost:3080/?file='+filename);
				request('http://localhost:3080/?file='+filename,
				function(err,response,body){
					cb(err,response);
				});
			},
			function(response,cb){
				console.log('simulator_'+game_id);
				client.incr('simulator_'+game_id,function(err,rs){
					console.log('simulator_'+game_id);
					if(err) console.log(err.message);
					cb(err,response);
				});
			}
		],
		function(err,rs){
			console.log('foo');
			conn.end(function(err){
				done(err,rs);
			});
		});
	});



	//done(null);
}
function resetData(done){
	var game_id = 'f750260';
	pool.getConnection(function(err,conn){
		async.waterfall([
			function(cb){
				conn.query("UPDATE optadb.matchinfo \
							SET period = 'PreMatch', home_score = 0, away_score=0, matchtime = 0 WHERE game_id=?;",
							[game_id],function(err,rs){
								cb(err);
							});
			},
			function(cb){
				conn.query("DELETE FROM optadb.player_stats WHERE game_id=?;",
							[game_id],function(err,rs){
								cb(err);
							});
			},
			function(cb){
				conn.query("DELETE FROM optadb.goals WHERE game_id=?;",
							[game_id],function(err,rs){
								cb(err);
							});
			},
			function(cb){
				conn.query("DELETE FROM optadb.substitutions WHERE game_id=?;",
							[game_id],function(err,rs){
								cb(err);
							});
			},
			function(cb){
				client.set('simulator_'+game_id,0,function(err,rs){

					cb(err);
				});
			},
			function(cb){
				client.del('lvrt_'+game_id,function(err,rs){
					cb(err);
				});
			}
		],
		function(err,rs){
			conn.end(function(err){
				done(err,rs);
			});
		});
	});
}

function accessDenied(req,res){
	res.send(401,'Access Denied');
}