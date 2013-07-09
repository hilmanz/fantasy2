/**
* test script for /libs/api/team.js
*/
var assert = require('assert');
var should = require('should');
var path = require('path');
var users = require(path.resolve('./libs/api/users'));
var mysql = require('mysql');
var config = require(path.resolve('./config')).config;

var dummy = {
	name: 'foo',
	fb_id: '111222111',
	email:'foo@bar.com',
	phone:'123123123'
}
describe('Users',function(){
		it('register a user',function(done){
			users.register(dummy,function(err,rs){
				should.exist(rs);
				done();
			});
		});
		
		it('should fail cos the user already exists',function(done){
			users.register(dummy,function(err,rs){
				should.not.exist(rs);
				done();
			});
		});

		it('should remove the user perfectly',function(done){
			users.removeByFbId(dummy.fb_id,function(err,rs){
				should.not.exist(err);
				done();
			});
		});
	
});