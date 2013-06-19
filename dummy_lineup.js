/**
* an App for adding dummy lineups on dummy teams
* we identify the dummy teams from ffgame.game_teams where user_id is not exists in ffgame.game_users
*/

var fs = require('fs');
var path = require('path');
var mysql = require('mysql');
var dateformat = require('dateformat');
var config = require('./config').config;
var async = require('async');
var pool = mysql.createPool({
	host: config.database.host,
	user: config.database.username,
	password: config.database.password,
	multipleStatements: true
});

var user_id = 10;

async.waterfall(
	[
		getAllDummyTeams,
		generateTeamLineups,
	],
	function(err,result){
		pool.end(function(err){
			console.log('done');
		});
	}
);	
function getAllDummyTeams(callback){
	pool.getConnection(function(err,conn){
		conn.query("SELECT id FROM ffgame.game_teams a\
					WHERE NOT EXISTS (SELECT 1 FROM ffgame.game_users WHERE id = a.user_id LIMIT 1);",
					[],
				function(err,teams){
					conn.end(function(err){
						callback(null,teams);
					});
				});
	});
}
function generateTeamLineups(teams,callback){
	async.each(teams,function(team,done){
		async.waterfall(
			[ 
				function(callback){
					getTeamPlayers(team,function(err,players){
						callback(null,team,players);
					});
				},
				function(team,players,callback){
					resetLineups(team.id,function(err){
						callback(null,team,players);
					});
				},
				function(team,players,callback){
					createLineups(team,players,function(err,lineups){
						callback(err,lineups);	
					});
					
				}

			],
			function(err,result){
				done();		
			}
		);
	},function(err){
		callback(null,teams);	
	});
	

}
/**
assume every team using 4-4-2 formations
**/
function createLineups(team,players,done){
	var f = [];
	var m = [];
	var d = [];
	var g = [];
	for(var i in players){
		if(players[i].position=='Forward'){
			f.push(players[i]);
		}else if(players[i].position=='Midfielder'){
			m.push(players[i]);
		}else if(players[i].position=='Defender'){
			d.push(players[i]);
		}else{
			g.push(players[i]);
		}

	}
	g = shuffle(g);
	m = shuffle(m);
	d = shuffle(d);
	f = shuffle(f);
	var lineups = [];
	lineups.push(g[0]);
	for(var i=0;i<4;i++){
		lineups.push(d[i]);
		lineups.push(m[i]);
	}
	lineups.push(f[0]);
	lineups.push(f[1]);
	var sql = "INSERT INTO ffgame.game_team_lineups\
				(game_team_id,player_id,position_no)\
				VALUES \
				";
	var data = [];
	for(var i in lineups){
		if(i>0){
			sql+=",";
		}
		sql+="(?,?,?)";
		data.push(team.id);
		data.push(lineups[i].player_id);
		data.push(parseInt(i)+1);
	}
	pool.getConnection(function(err,conn){
		conn.query(sql,data,function(err,teams){
					console.log(this.sql);
					conn.end(function(err){
						done(null,[lineups]);
					});
				});
	});


	
}
function shuffle(o){ //v1.0
    for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};
function resetLineups(team_id,done){
	console.log(team_id,'reset lineups');
	pool.getConnection(function(err,conn){
		conn.query("DELETE FROM ffgame.game_team_lineups WHERE game_team_id = ?",
					[team_id],
				function(err,rs){
					conn.end(function(err){
						done(err);
					});
				});
	});
}
function getTeamPlayers(team,done){
	pool.getConnection(function(err,conn){
		conn.query("SELECT a.player_id,b.position FROM ffgame.game_team_players a\
					INNER JOIN ffgame.master_player b\
					ON a.player_id = b.uid \
					WHERE a.game_team_id=? ORDER BY b.position ASC;",
					[team.id],
				function(err,players){
					conn.end(function(err){
						done(err,players);
					});
				});
	});
}