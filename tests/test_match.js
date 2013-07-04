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
	game_id:'f2895'
}
describe('gameplay-match',function(){
	it('get the current fixtures',function(done){
		gameplay.match.fixtures(function(err,rs){
			should.not.exist(err);
			should.exist(rs);
			done();
		});
	});

	it('able to show match results',function(done){
		gameplay.match.results(dummy.game_id,function(err,rs){
			should.not.exist(err);
			should.exist(rs);
			done();
		});
	});
});