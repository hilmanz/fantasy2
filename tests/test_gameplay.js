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
	phone:'123123123'
}
describe('gameplay',function(){
		it('get the current lineup',function(done){
			gameplay.getLineup(1,function(err,rs){
				should.not.exist(err);
				should.exist(rs);
				rs.should.have.length(11);
				done();
			});
		});
		
		it('can setup the lineup',function(done){
			gameplay.setLineup(1,[
					{player_id:"p12882",no:9},
					{player_id:"p13017",no:10},
					{player_id:"p14075",no:3},
					{player_id:"p14965",no:4},
					{player_id:"p18892",no:6},
					{player_id:"p2034",no:5},
					{player_id:"p20695",no:7},
					{player_id:"p3",no:8},
					{player_id:"p51940",no:1},
					{player_id:"p54772",no:2},
					{player_id:"p8595",no:11}
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

		
	
});