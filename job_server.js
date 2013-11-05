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
//our api libs
var users = require('./libs/services/users');
var team = require('./libs/services/team'); // soccer team
//var player = require('./libs/services/player'); //soccer player 
var gameplay = require('./libs/services/gameplay'); // gameplay service

//mysql pool
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});


var app = express();
var RedisStore = require('connect-redis')(express);

// all environments
app.set('port', process.env.PORT || config.port);
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
app.get('/', routes.index);


var queues = [];
var need_update = false; //the flag to update team's points and ranking
app.get('/job',[],function(req,res){
	if(queues.length==0){
		need_update = true;
		//if queue empty,  we query for new queue
		console.log('jobserver','queue is empty, we need more queues');
		getQueues(function(err,queue){
			for(var i in queue){
				queues.push(queue[i]);
			}
			queue = null;
			if(queues.length>0){
				var q = queues.shift();
				assignQueue(req.query.bot_id,q,function(err){
					res.send(200,{status:1,data:q});
				});	
			}else{
				res.send(200,{status:0});
			}
		});
	}else{
		var q = queues.shift();
		assignQueue(req.query.bot_id,q,function(err){
			res.send(200,{status:1,data:q});
		});
	}
});

app.get('/refresh',function(req,res){
	if(need_update){
		need_update = false;
		res.send(200,{status:1,message:'OK'});
	}else{
		res.send(200,{status:0,message:'NOK'});
	}
});


http.createServer(app).listen(3099, function(){
  console.log('Express server listening on port 3099');
});

function assignQueue(bot_id,queue,done){
	pool.getConnection(function(err,conn){
		conn.query("UPDATE ffgame_stats.job_queue SET n_done=0,worker_id=?,n_status=1 WHERE id=?",
					[bot_id,queue.id],function(err,rs){
						conn.end(function(e){
							done(err);
						});
					});
	});
}

function getQueues(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame_stats.job_queue WHERE n_status=0 ORDER BY id ASC LIMIT 100;",
					[],function(err,rs){
						conn.end(function(e){
							done(err,rs);
						});
					});
	});
}
function accessDenied(req,res){
	res.send(401,'Access Denied');
}