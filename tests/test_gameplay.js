/**
* test script for /libs/api/gameplay.js
*/
var assert = require('assert');
var should = require('should');
var path = require('path');
var gameplay = require(path.resolve('./libs/api/gameplay'));
var mysql = require('mysql');
var config = require(path.resolve('./config')).config;

var dummy = {
	name: 'foo',
	fb_id: '111111',
	email:'foo@bar.com',
	phone:'123123123',
	game_team_id:378,
	player_id:'p43250',
}
describe('gameplay',function(){
		it('get the current lineup',function(done){
			gameplay.getLineup(dummy.game_team_id,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				rs.lineup.should.have.length(16);
				rs.formation.should.equal('4-4-2');
				done();
			});
		});
		
		it('can setup the lineup',function(done){
			gameplay.setLineup(dummy.game_team_id,[
					{player_id:"p3",no:9},
					{player_id:"p13017",no:10},
					{player_id:"p14965",no:3},
					{player_id:"p2034",no:4},
					{player_id:"p20695",no:6},
					{player_id:"p54771",no:5},
					{player_id:"p2404",no:7},
					{player_id:"p38530",no:8},
					{player_id:"p51940",no:1},
					{player_id:"p14075",no:2},
					{player_id:"p12297",no:11},
					{player_id:"p50175",no:12},
					{player_id:"p82403",no:13},
					{player_id:"p18892",no:14},
					{player_id:"p43250",no:15},
					{player_id:"p55909",no:16}
				],'4-4-2',function(err,rs){
				should.not.exist(err);
				//should.equal(rs.total,11);
				done();
			});
		});
		
		it('can retrieve team players list',function(done){
			gameplay.getPlayers(1,function(err,players){
				should.not.exist(err);
				should.exist(players);
				//should.not.eql(players.length,0);
				done();
			});
		});

		it('should return the team\'s current budget',function(done){
			gameplay.getBudget(1,function(err,budget){
				should.not.exist(err);
				should.exist(budget);

				done();
			});
		});

		it('retrieves player master statistics',function(done){
			gameplay.getPlayerStats('p19557',
			function(err,stats){
				should.not.exist(err);
				should.exist(stats);
				
				done();
			});
		});

		it('retrieves player game_team statistics',function(done){
			gameplay.getPlayerTeamStats(296,'p19557',
			function(err,stats){
				should.not.exist(err);
				should.exist(stats);
				
				done();
			});
		});
		
		it('retrieves player master detail',function(done){
			gameplay.getPlayerDetail(dummy.player_id,
			function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				done();
			});
		});

		it('can retrieve financial statements',function(done){
			gameplay.getFinancialStatement(286,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				console.log(rs);
				done();
			});
		});

		it('can retrieve the next match',function(done){
			gameplay.next_match('t1',function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				should.exist(rs[0].home_id);
				should.exist(rs[0].away_id);
				console.log(rs);
				done();
			});
		});
		it('can retrieve venue data',function(done){
			gameplay.getVenue('t1',function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				should.exist(rs.capacity);
				should.exist(rs.name);
				console.log(rs);
				done();
			});
		});
		it('can retrieve the best match',function(done){
			gameplay.best_match(dummy.game_team_id,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				console.log(rs);
				done();
			});

		});

		it('can retrieve the team last revenue',function(done){
			gameplay.last_earning(dummy.game_team_id,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				console.log(rs);
				done();
			});

		});

		it('can retrieve the team best player',function(done){
			gameplay.best_player(dummy.game_team_id,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				console.log(rs);
				done();
			});

		});


		it('can sale a player',function(done){
			gameplay.sale(378,'p43250',function(err,result){
				console.log(result);
				should.exist(result);
				done();
			});
		});

		it('can buy a player',function(done){
			gameplay.buy(378,'p43250',function(err,result){
				console.log(result);
				should.exist(result);
				done();
			});
		});
	
});