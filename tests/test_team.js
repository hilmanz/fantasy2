/**
* test script for /libs/api/team.js
*/
var assert = require('assert');
var should = require('should');
var path = require('path');
var team = require(path.resolve('./libs/api/team'));
var mysql = require('mysql');
var config = require(path.resolve('./config')).config;

describe('testing team',function(){
	
		it('call getTeams',function(done){
			team.getTeams(function(err,rs){
				
				should.equal(rs.length,20);
				done();
			});
		});

		it('call getPlayers',function(done){
			team.getPlayers('t1',function(err,rs){
				should.notEqual(rs.length,0);
				done();
			});
		});
		
	
});