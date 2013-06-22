
/**
 * Module dependencies.
 */

var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('http')
  , path = require('path');

var redis = require('redis');
var dummy_api_key = '1234567890';
var auth = require('./libs/api/auth');
//our api libs
var users = require('./libs/services/users');
var team = require('./libs/services/team'); // soccer team
//var player = require('./libs/services/player'); //soccer player 
var gameplay = require('./libs/services/gameplay'); // gameplay service


var app = express();
var RedisStore = require('connect-redis')(express);

// all environments
app.set('port', process.env.PORT || 3000);
app.set('views', __dirname + '/views');
app.set('view engine', 'jade');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser('i die, you die, everybody die'));
app.use(express.session({ store: new RedisStore }));

var client = redis.createClient();
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
app.get('/fixtures', user.list);
app.get('/players/:team_id',[auth.canAccess],team.getPlayers);
app.get('/teams', [auth.canAccess],team.getTeams);
app.get('/team/get/:fb_id',[auth.canAccess],team.getUserTeam);
app.get('/teams/:id',[auth.canAccess],team.getTeamById);
app.get('/match/preview', user.list);
app.get('/match/results', user.list);
app.get('/match/livestats', user.list);
app.get('/match/list',[auth.canAccess],gameplay.fixtures);
app.get('/game/stats', user.list);
app.get('/team/:id',user.list);
app.get('/player/:id',user.list);
app.get('/score/:id',user.list);
app.post('/user/register',[auth.canAccess],users.register);
app.post('/create_team',[auth.canAccess],team.create);
app.get('/user/info',user.list);
app.get('/user/budget',user.list);
app.get('/user/financial_statement',user.list);
app.get('/game/rank',user.list);
app.get('/test',function(req,res){
	client.get(req.query.access_token,function(err,rs){
		console.log(rs);
	});
	client.ttl(req.query.access_token,function(err,rs){
		console.log('ttl',rs);
	});
	//console.log(req.query.access_token,req.session.access_token);
	res.send(200,'');

});
app.post('/team/lineup/save',[auth.canAccess],gameplay.setLineup);
app.get('/team/lineup/:id',[auth.canAccess],gameplay.getLineup);
app.get('/team/list/:id',[auth.canAccess],gameplay.getPlayers);
app.get('/team/sponsors/:game_team_id',[auth.canAccess],gameplay.getActiveSponsors);
app.get('/team/budget/:game_team_id',[auth.canAccess],gameplay.getBudget);
app.get('/official/list/:game_team_id',[auth.canAccess],gameplay.official_list);
app.post('/official/hire',[auth.canAccess],gameplay.hire_staff);
app.post('/official/fire',[auth.canAccess],gameplay.fire_staff);
app.get('/sponsorship/list/:team_id',[auth.canAccess],gameplay.getSponsors);
app.post('/sponsorship/apply',[auth.canAccess],gameplay.applySponsorship);

app.post('/auth',auth.authenticate);

app.get('/ping',function(req,res){
	res.send(200,{status:1,message:'Server Alive'});
});
http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});

function accessDenied(req,res){
	res.send(401,'Access Denied');
}