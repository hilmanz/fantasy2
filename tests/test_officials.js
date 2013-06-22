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
	game_team_id: 1
}
describe('gameplay-officials',function(){
	it('get the officials hiring window',function(done){
		gameplay.officials.official_list(dummy.game_team_id,function(err,rs){
			should.not.exist(err);
			should.exist(rs);
			done();
		});
	});
});