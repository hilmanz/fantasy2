/**
* test script for /libs/api/sponsorship.js
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
	game_team_id: 1,
	official_id:1
}
describe('gameplay-sponsorship',function(){
	it('get the available sponsorships',function(done){
		gameplay.sponsorship.getAvailableSponsorship(2,function(err,rs){
			should.not.exist(err);
			should.exist(rs);
			done();
		});
	});
	it('it can apply the sponsorship',function(done){
		gameplay.sponsorship.applySponsorship(2,1,function(err,rs){
			console.log('result : ',rs);
			should.not.exist(err);
			should.exist(rs);
			done();
		});
	});
});