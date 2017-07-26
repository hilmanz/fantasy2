/**
 * Job Server for Rank and Points Updater.
 * Changelog
 * 31/01/2014 - duf
 * rank_and_points.worker.js cannot perform very well when updater.worker.js is in process
 * because there will be a table lock in game_team_player_weekly, so we need to avoid that.
 * job server rank must check the job_queue table, 
 * making sure that none active or pending tasks in there
 */

var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('http')
  , path = require('path');
var async = require('async');
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
var is_finished = false;
var need_update = false; //the flag to update team's points and ranking
app.get('/job',[],function(req,res){
	
	async.waterfall([
		function(cb){
			//first we make sure that updater.worker.js is idle
			isUpdaterIdle(function(err,is_idle){
				cb(err,is_idle);
			});
		}
	],function(err,is_idle){
		if(is_idle){
			if(queues.length==0){
				//if queue empty,  we query for new queue
				console.log('jobserver','queue is empty, we need more queues');

				//SSFM-127 - we use new method requeue 
				//so we can do requeue stuffs in other API call such as /refresh
				requeue(req.query.bot_id,true,function(q,has_data){
					if(has_data){
						var last_queue = 0;
						if(queues.length==0){
							last_queue = 1;
						}
						console.log('last_queue : ',last_queue);
						res.send(200,{status:1,data:q,last_queue:last_queue});
					}else{
						res.send(200,{status:0});
					}
				});
			}else{
				var q = queues.shift();
				assignQueue(req.query.bot_id,q,function(err){
					var last_queue = 0;
					if(queues.length==0){
						last_queue = 1;
					}
					console.log('last_queue : ',last_queue);
					res.send(200,{status:1,data:q,last_queue:last_queue});
				});
			}
		}else{
			console.log('updater in process, we wait...');
			res.send(200,{status:0});
		}
	});

	//
	
});

app.get('/refresh',function(req,res){
	console.log(queues);

	//SSFM-127 - 
	//since these revision, we check the queue first
	//if it was empty, we try to requeue.  and IF still empty, 
	//we check if there's on going queue job in progress (n_status=1)
	//if not, we tell the client that we can do the update 
	//as long as is_finished flag still false.
	
	if(queues.length==0){
		console.log('yeah, queue kosong, requeue coba..');

		requeue(0,false,function(q,has_data){
			if(!has_data){
				console.log('udah kosong nih kayaknya.. coba cek ada yg ongoing gak ?');
				getActiveJobs(function(err,rs){
					if(rs.length==0){
						need_update = true;
					}
				});
			}
		});
	}else{
		need_update = false;
	}
	console.log('need_update',need_update);
	console.log('is_finished',is_finished);

	//SSFM-127 we need to make sure that is_finished flag still false before sending an OK
	if(need_update && !is_finished){
		need_update = false;
		is_finished = true;
		res.send(200,{status:1,message:'OK'});
	}else{
		res.send(200,{status:0,message:'NOK'});
	}
});


http.createServer(app).listen(3098, function(){
  console.log('Express server listening on port 3098');
});


function isUpdaterIdle(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT id FROM ffgame_stats.job_queue WHERE n_status IN (0,1) LIMIT 1",
					[],function(err,rs){
						var is_idle = true;
						if(rs!=null && rs.length > 0){
							is_idle = false;
						}
						conn.end(function(e){
							done(err,is_idle);
						});
					});
	});
}

function assignQueue(bot_id,queue,done){
	pool.getConnection(function(err,conn){
		conn.query("UPDATE ffgame_stats.job_queue_rank SET n_done=0,worker_id=?,n_status=1 WHERE id=?",
					[bot_id,queue.id],function(err,rs){
						conn.end(function(e){
							done(err);
						});
					});
	});
}

function getQueues(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame_stats.job_queue_rank WHERE n_status=0 ORDER BY id ASC LIMIT 100;",
					[],function(err,rs){
						conn.end(function(e){
							done(err,rs);
						});
					});
	});
}

//SSFM-127
//check if there's an active job.
//we only need at least 1 job in active.
function getActiveJobs(done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame_stats.job_queue_rank WHERE n_status=1 ORDER BY id ASC LIMIT 1;",
					[],function(err,rs){
						conn.end(function(e){
							done(err,rs);
						});
					});
	});
}
//SSFM-127
//refill the queue with a new jobs if available.
//if the queue is still empty, we flag the need_update to true
//if isnt, we flag the is_finished back to false.
function requeue(bot_id,need_data,done){
	getQueues(function(err,queue){
			for(var i in queue){
				queues.push(queue[i]);
			}
			queue = null;
			if(queues.length>0){
				console.log('we try to feel it up');

				//SSFM-127 set is_finished flag to false if the queue has data.
				is_finished = false;

				if(need_data){
					var q = queues.shift();
					assignQueue(bot_id,q,function(err){
						done(q,true);
					});
				}else{
					done(null,true);
				}
				
				
			}else{
				//SSFM-127 set need_update to true if the queue keep empty
				//i think in the future these will be unnecesarry since it has been covered in
				//   /refresh method.
				need_update = true;
				done(null,false);
			}
		});
}
function accessDenied(req,res){
	res.send(401,'Access Denied');
}