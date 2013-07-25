/**
* test script for /libs/api/team.js
*/
var assert = require('assert');
var should = require('should');
var path = require('path');
var team = require(path.resolve('./libs/api/team'));
var mysql = require('mysql');
var config = require(path.resolve('./config')).config;

var team_id = 123;
var fb_id = '100001023465395';
describe('team',function(){
	
		it('getTeams',function(done){
			team.getTeams(function(err,rs){
				
				should.equal(rs.length,20);
				done();
			});
		});

		it('getPlayers',function(done){
			team.getPlayers('t1',function(err,rs){
				should.notEqual(rs.length,0);
				done();
			});
		});
		
		it('getTeamById',function(done){
			team.getTeamById('t1',function(err,rs){
				should.notEqual(rs.length,0);
				should.equal(rs.name,'Manchester United');
				done();
			});
		});

		it('create team',function(done){
			team.create({fb_id:'111222111',team_id:'t8',players:[
					"p59936",
					"p14422",
					"p37096",
					"p20487",
					"p98980",
					"p77818",
					"p45638",
					"p37748",
					"p17127",
					"p15943",
					"p51507",
					"p57214",
					"p13439",
					"p19160",
					"p80254",
					"p42427",
					"p59940",
					"p59939",
					"p88496",
					"p41243",
					"p111169",
					"p90801",
					"p86364",
					"p40564",
					"p117216",
					"p28566",
					"p8597",
					"p8758",
					"p54102",
					"p20467",
					"p81880",
					"p41792",
					"p21091",
					"p56864",
					"p13227",
					"p56861",
					"p88497",
					"p66587",
					"p111571",
					"p59937",
					"p90802",
					"p108012",
					"p88498",
					"p92984",
					"p99127",
					"p12297",
					"p38401",
					"p43274"
				]},function(err,rs){
					should.exist(rs);
					should.not.exist(err);
					team_id = rs;
					done();
			});
		});

		it('should return the user\'s team',function(done){
			team.getUserTeam('111222111',function(err,team){
				should.equal(team.team_id,'t8');
				should.equal(team.user_id,57);
				done();
			});
		});

		it('should remove successfully',function(done){
			team.remove_team(
				team_id,function(err,rs){
					should.not.exist(err);
					done();		
				}
			);
		});

		it('should returns game points',function(done){
			team.getUserTeamPoints(
				fb_id,function(err,rs){
					should.not.exist(err);
					should.exist(rs.points);
					done();		
				}
			);
		});
});