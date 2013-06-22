/**
* an App for generating dummy matches
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

async.waterfall(
	[],
	function(err,result){

	}
);