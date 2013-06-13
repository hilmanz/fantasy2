/**
* Opta Dummy Server
* these application will simulate Opta Server.
* it will produce the following xml files on interval-basis
* 1. match-preview
* 2. match-results
*/

var keeper_stats = [
'diving_save',
'game_started',
'long_pass_own_to_opp',
'accurate_pass',
'keeper_throws',
'total_final_third_passes',
'attempts_conceded_ibox',
'touches',
'passes_left',
'accurate_launches',
'poss_lost_all',
'total_fwd_zone_pass',
'keeper_pick_up',
'accurate_long_balls',
'accurate_fwd_zone_pass',
'accurate_goal_kicks',
'goals_conceded',
'saves',
'poss_lost_ctrl',
'attempts_conceded_obox',
'final_third_entries',
'ball_recovery',
'poss_won_def_3rd',
'accurate_back_zone_pass',
'passes_right',
'stand_save',
'total_back_zone_pass',
'pen_area_entries',
'total_long_balls',
'accurate_keeper_throws',
'goals_conceded_ibox',
'goals_conceded_obox',
'mins_played',
'error_lead_to_shot',
'cross_not_claimed',
'goal_kicks',
'long_pass_own_to_opp_success',
'saved_ibox',
'total_pass',
'total_launches',
'fwd_pass',
'formation_place'
];
var non_keeper_stats = [
'duel_lost',
'dispossessed',
'ontarget_att_assist',
'accurate_cross',
'game_started',
'long_pass_own_to_opp',
'accurate_chipped_pass',
'duel_won',
'accurate_pass',
'won_tackle',
'successful_final_third_passes',
'total_cross_nocorner',
'total_final_third_passes',
'rightside_pass',
'offside_provoked',
'attempts_conceded_ibox',
'touches',
'total_tackle',
'poss_lost_all',
'total_fwd_zone_pass',
'att_assist_openplay',
'accurate_long_balls',
'total_cross',
'accurate_fwd_zone_pass',
'total_chipped_pass',
'lost_corners',
'goals_conceded',
'poss_lost_ctrl',
'attempts_conceded_obox',
'final_third_entries',
'crosses_18yard',
'ball_recovery',
'accurate_cross_nocorner',
'poss_won_def_3rd',
'effective_clearance',
'accurate_back_zone_pass',
'passes_right',
'total_throws',
'won_corners',
'backward_pass',
'total_back_zone_pass',
'pen_area_entries',
'total_long_balls',
'accurate_throws',
'goals_conceded_obox',
'mins_played',
'error_lead_to_shot',
'total_contest',
'total_clearance',
'long_pass_own_to_opp_success',
'total_att_assist',
'total_pass',
'fwd_pass',
'outfielder_block',
'crosses_18yardplus',
'goals',
'goal_assist',
'formation_place'];
var fs = require('fs');
var path = require('path');
var mysql = require('mysql');
var config = require('./config').config;
var player_team = 't1'; //we use Manchester United as example
var squads = [];
var pool = mysql.createPool({
			host: config.database.host,
			user: config.database.username,
			password: config.database.password
		});

/*
var conn = mysql.createConnection(
	{
		host: config.database.host,
		user: config.database.username,
		password: config.database.password,
	}
);

conn.query("SELECT * FROM ffgame.master_team LIMIT 100",function(err,result){
	if(!err){
		squads.push(result);
	}
});
conn.end(function(err){
	if(!err){
		console.log('Db Disconnected !');
	}else{
		throw err;
	}
});
*/
function getTeamData(callback){
	console.log('[team data] pool opened');
	pool.getConnection(function(err,conn){
		conn.query("SELECT * FROM ffgame.master_team LIMIT 100",function(err,result){
			if(!err){
				squads = result;
			}
			conn.end(function(err){
				if(!err){
					console.log('[team data] pool closed');
					onGetTeamData(callback);
				}
			});	
		});
		
	});
	
}
function onGetTeamData(callback){
	pool.getConnection(function(err,conn){
		//console.log(squads);
		var n_squads = squads.length;
		var n_done = 0;
		for(var i in squads){
			(function(){
				var n = i;
				conn.query("SELECT * FROM ffgame.master_player WHERE team_id=? LIMIT 100",
							[squads[i].uid],function(err,players){
								//console.log(players);
								//console.log(squads[n]);
								squads[n].players = players;
								n_done++;
								if(n_done==n_squads){
									conn.end(function(err){
										onGetOfficialData(callback);
									});
								}
							});
			}());	
		}
	});
}
function onGetOfficialData(callback){
	pool.getConnection(function(err,conn){
		//console.log(squads);
		var n_squads = squads.length;
		var n_done = 0;
		for(var i in squads){
			(function(){
				var n = i;
				conn.query("SELECT * FROM ffgame.master_officials WHERE team_id=? LIMIT 100",
							[squads[i].uid],function(err,officials){
								//console.log(players);
								//console.log(squads[n]);
								squads[n].officials = officials;
								n_done++;
								if(n_done==n_squads){
									conn.end(function(err){
										callback();
									});
								}
							});
			}());	
		}
	});
}
function onQueryFinished(){
	console.log('done');
	pool.end();
	
	setupMatch();
}

//step 1 get team data
getTeamData(onQueryFinished);

function setupMatch(){
	//step 2 simulate the match schedule.
	//assume that the player team is a home team.
	var home_team,away_team;
	var n_home = 0;
	for(var i in squads){
		if(squads[i].uid=='t1'){
			home_team = squads[i];
			n_home = i;
			break;
		}
	}
	do{
		var n_index = Math.floor(Math.random()*squads.length);
	}
	while(n_index==n_home);
	
	away_team = squads[n_index];
	var game_settings = {
		home:{
			team_id:home_team.uid,
			name:home_team.name,
			goals:getGoals(),
			bookings:getBookings(),
			substitutions:getSubstitutions(),
			lineup:getLineUp(home_team.players),
			officials:home_team.officials
		},
		away:{
			team_id:away_team.uid,
			name:away_team.name,
			goals:getGoals(),
			bookings:getBookings(),
			substitutions:getSubstitutions(),
			lineup:getLineUp(away_team.players),
			officials:away_team.officials
		}
	}
	game_settings = generateStats(game_settings);
	
	var winner = 't1';
	if(game_settings.home.goals>game_settings.away.goals){
		winner = 't1';
	}else{
		winner = game_settings.away.team_id;
	}


	var toTemplateData = {
		game_id: 'f'+(Math.ceil(Math.random()*10000)+10),
		attendance: Math.ceil(75000 - (((Math.random()*30)/100)*75000)),
		team_data: game_settings,
		winner : winner
	}
	var handlebars = require('handlebars'); // notice the "2" which matches the npm repo, sorry..

	handlebars.root = __dirname + '/views/templates';

	
	//mu.clearCache();
	var strOut = "";
	var file_output = "sample.xml";
	var raw = fs.readFileSync(path.resolve(__dirname + '/views/templates/matchresults-freekick-goals.xml'), 'utf8');
	var template = handlebars.compile(raw.toString());
	strOut = template(toTemplateData);
	
	var filepath = path.resolve('./data/'+file_output);
	fs.writeFile(filepath, strOut, function(err) {
	    if(err) {
	        console.log(err);
	    } else {
	        console.log("The file was saved!");
	    }
	}); 
	/*
	template.render(toTemplateData)
	  .on('data',function(data){
	  		strOut+=data;/
	  })
	  .on('end', function (data){
	  		
	  		var filepath = path.resolve('./data/'+file_output);
			fs.writeFile(filepath, strOut, function(err) {
			    if(err) {
			        console.log(err);
			    } else {
			        console.log("The file was saved!");
			    }
			}); 
	    	
	  });
	*/
}
/**
simulate goals, using 24-dice
**/
function getGoals(){
	var n_roll = roll(24);
	console.log('roll for goals -> '+n_roll);
	if(n_roll>10 && n_roll<16){
		return 1;
	}else if(n_roll>16 && n_roll<20){
		return 2;
	}else if(n_roll>20 && n_roll<23){
		return 3;
	}else if(n_roll>23){
		return Math.round(Math.random()*8);
	}else{
		return 0;
	}
}
/** simulate bookings
**/
function getBookings(){
	return roll(20);
}
/** simulate substitution
**/
function getSubstitutions(){
	return roll(3);
}
function roll(n){
	var t = Math.round(Math.random()*n);
	return t;
}
/**
* get 11 lineups both starter and substitution
* the rule is simple.
* we assume that each team using 4411 formation
* so they need 2 forward, 3 middle, and 4 defs and 1 goalie
* plus 5 substitution, 1 for goalie, 4 random except goalie
*/
function getLineUp(players){
	//console.log(players);
	var forward = [];
	var midfielder = [];
	var defender = [];
	var goalkeeper = [];
	var subs = [];
	for(var i in players){
		if(players[i].position=='Forward'){
			forward.push(players[i]);
		}
		if(players[i].position=='Midfielder'){
			midfielder.push(players[i]);
		}
		if(players[i].position=='Defender'){
			defender.push(players[i]);
		}
		if(players[i].position=='Goalkeeper'){
			goalkeeper.push(players[i]);
		}
	}
	forward = shuffle(forward);
	midfielder = shuffle(midfielder);
	defender = shuffle(defender);
	goalkeeper = shuffle(goalkeeper);

	//console.log(goalkeeper);
	//forward,midfielder,defender,goalkeeper
	var starters = [];

	//goalie
	starters.push({player:goalkeeper[0],stats:{}});
	
	//defs
	for(var i=0;i<4;i++){
		starters.push({player:defender[i],stats:{}});
	}
	subs.push({player:defender[4],stats:{}});
	//midfields
	for(var i=0;i<4;i++){
		starters.push({player:midfielder[i],stats:{}});
	}
	subs.push({player:midfielder[4],stats:{}});
	subs.push({player:midfielder[5],stats:{}});
	//forward
	for(var i=0;i<2;i++){
		starters.push({player:forward[i],stats:{}});
	}
	subs.push({player:forward[2],stats:{}});
	subs.push({player:forward[3],stats:{}});
	return {starters:starters,
			substitutions:subs};
}

function generateStats(settings){

	//home

	var players = shuffle(settings.home.lineup.starters);
	//bookings
	var home_bookings = [];
	var home_goals = [];
	for(var i=0; i<settings.home.bookings;i++){
		var n_player = Math.floor(Math.random()*players.length);
		for(var j=0;j<players.length;j++){
			if(settings.home.lineup.starters[j].player.uid==players[n_player].player.uid){
				home_bookings.push(addBook(settings.home.lineup.starters[j].player.uid));
				break;
			}
		}
	}
	settings.home.booking_data = home_bookings;
	//goals
	players = shuffle(players);
	for(var i=0;i < settings.home.goals;i++){
		var book_time = Math.floor(Math.random()*90);
		var period = (book_time<=45) ? 'FirstHalf' : 'SecondHalf';
		home_goals.push(
			{player_id: players[i].player.uid,
			 event_id: Math.ceil(Math.random()*100),
			 event_number: Math.ceil(Math.random()*10000),
			 time: book_time,
			 period: period
			}
		);
	}
	settings.home.goal_data = home_goals;

	//away
	var players = shuffle(settings.away.lineup.starters);
	var away_bookings = [];
	var away_goals = [];
	//bookings
	for(var i=0; i<settings.away.bookings;i++){
		var n_player = Math.floor(Math.random()*players.length);
		for(var j=0;j<players.length;j++){
			if(settings.away.lineup.starters[j].player.uid==players[n_player].player.uid){
				away_bookings.push(addBook(settings.away.lineup.starters[j].player.uid));
				break;
			}
		}
	}
	settings.away.booking_data = away_bookings;
	//goal
	players = shuffle(players);
	for(var i=0;i < settings.away.goals;i++){
		var book_time = Math.floor(Math.random()*90);
		var period = (book_time<=45) ? 'FirstHalf' : 'SecondHalf';
		away_goals.push(
			{player_id: players[i].player.uid,
			 event_id: Math.ceil(Math.random()*100),
			 event_number: Math.ceil(Math.random()*10000),
			 time: book_time,
			 period: period
			}
		);
	}
	settings.away.goal_data = away_goals;

	//player stats
	//home
	for(var i in settings.home.lineup.starters){
		var person = settings.home.lineup.starters[i];
	

		if(person.player.position=='Goalkeeper'){

			for(var s in keeper_stats){
				person.stats[keeper_stats[s]] = Math.floor(Math.random()*11);
			}
			person.stats['goals'] = 0;
			for(var t in home_goals){
				if(home_goals[t].player_id==person.player.uid){
					person.stats['goals']++;
				}
			}
		}else{
			for(var s in non_keeper_stats){
				person.stats[non_keeper_stats[s]] = Math.floor(Math.random()*11);
			}
			person.stats['goals'] = 0;
			for(var t in home_goals){
				if(home_goals[t].player_id==person.player.uid){
					person.stats['goals']++;
				}
			}
		}
		person.stats_data = [];
		for(var t in person.stats){
			person.stats_data.push({name:t,value:person.stats[t]});
		}
		settings.home.lineup.starters[i] = person;
	}

	//away
	for(var i in settings.away.lineup.starters){
		var person = settings.away.lineup.starters[i];
		if(person.player.position=='Goalkeeper'){
			for(var s in keeper_stats){
				person.stats[keeper_stats[s]] = Math.floor(Math.random()*11);
			}
			person.stats['goals'] = 0;
			for(var t in home_goals){
				if(home_goals[t].player_id==person.player.uid){
					person.stats['goals']++;
				}
			}
		}else{
			for(var s in non_keeper_stats){
				person.stats[non_keeper_stats[s]] = Math.floor(Math.random()*11);
			}
			person.stats['goals'] = 0;
			for(var t in away_goals){
				if(away_goals[t].player_id==person.player.uid){
					person.stats['goals']++;
				}
			}
		}
		person.stats_data = [];
		for(var t in person.stats){
			person.stats_data.push({name:t,value:person.stats[t]});
		}
		settings.away.lineup.starters[i] = person;

	}
	//substitute
	//home
	settings.home.lineup.substitutions = shuffle(settings.home.lineup.substitutions);
	settings.away.lineup.substitutions = shuffle(settings.away.lineup.substitutions);
	settings.home.substitute_data = [];
	for(var i=0; i< settings.home.substitutions; i++){
		//settings.home.substitutions[i].player
		var p = settings.home.lineup.substitutions[i];
		
		settings.home.substitute_data.push({player_id:p.player.uid,
											 event_id: Math.ceil(Math.random()*100),
											 event_number: Math.ceil(Math.random()*10000),
											 time: Math.ceil(Math.random()*90),
											 suboff: settings.home.lineup.starters[Math.ceil(Math.random()*9)+1].player.uid,
											 reason:'Tactical'
											}
		);

		for(var s in non_keeper_stats){
			p.stats[non_keeper_stats[s]] = Math.floor(Math.random()*11);
		}
		p.stats['goals'] = 0;
		p.stats_data = [];
		for(var t in p.stats){
			p.stats_data.push({name:t,value:p.stats[t]});
		}
		settings.home.lineup.substitutions[i] = p;
	}
	//console.log(settings.home.substitute_data);

	settings.away.substitute_data = [];
	for(var i=0; i< settings.away.substitutions; i++){
		
		var p = settings.away.lineup.substitutions[i];
		
		settings.away.substitute_data.push({player_id:p.player.uid,
											 event_id: Math.ceil(Math.random()*100),
											 event_number: Math.ceil(Math.random()*10000),
											 time: Math.ceil(Math.random()*90),
											 suboff: settings.away.lineup.starters[Math.ceil(Math.random()*9)+1].player.uid,
											 reason:'Tactical'
											}
		);

		for(var s in non_keeper_stats){
			p.stats[non_keeper_stats[s]] = Math.floor(Math.random()*11);
		}
		p.stats['goals'] = 0;
		p.stats_data = [];
		for(var t in p.stats){
			p.stats_data.push({name:t,value:p.stats[t]});
		}
		settings.away.lineup.substitutions[i] = p;
	}
	

	return settings;
}

function shuffle(o){ //v1.0
    for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};

function addBook(player_id){
	var reason = ['Foul','Hand','Referee abuse','Crowd Interaction','Fight','Time Wasting','Argument','Excessive Celebration','Dive','Other'];
	var cards = ['Yellow','Red'];
	var n_roll = roll(24);
	var booking_type = "";
	var card = "";
	if(n_roll<18){
		booking_type = 'Foul';
	}else{
		booking_type = reason[Math.floor(Math.random()*(reason.length))];
	}
	var card_roll = roll(24);
	if(n_roll<20){
		card = cards[0];
	}else{
		card = cards[1];
	}
	var book_time = Math.floor(Math.random()*90);
	var period = (book_time<=45) ? 'FirstHalf' : 'SecondHalf';
	return {
		event_number:Math.ceil(Math.random()*10000),
		event_id:Math.ceil(Math.random()*10000),
		time:book_time,
		player_id:player_id,
		reason: booking_type,
		card_type: card,
		period:period
	};
}