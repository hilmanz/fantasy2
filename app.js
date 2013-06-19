
/**
 * Module dependencies.
 */

var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('http')
  , path = require('path');

//our api libs
var team = require('./libs/services/team'); // soccer team
//var player = require('./libs/services/player'); //soccer player 
var app = express();

// all environments
app.set('port', process.env.PORT || 3000);
app.set('views', __dirname + '/views');
app.set('view engine', 'jade');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser('your secret here'));
app.use(express.session());
app.use(app.router);
app.use(express.static(path.join(__dirname, 'public')));

// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', routes.index);
app.get('/fixtures', user.list);
app.get('/players/:team_id', team.getPlayers);
app.get('/teams', team.getTeams);
app.get('/match/preview', user.list);
app.get('/match/results', user.list);
app.get('/match/livestats', user.list);
app.get('/game/stats', user.list);
app.get('/team/:id',user.list);
app.get('/player/:id',user.list);
app.get('/score/:id',user.list)


http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});