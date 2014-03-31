/*
* a mailer proxy.
* we setup these proxy in fm-en (singapore node)
* the mailer.js instead of directly send request to Amazon SES
*/

/////THE MODULES/////////
var express = require('express')
  , routes = require('./routes')
  , user = require('./routes/user')
  , http = require('http')
  , path = require('path');
var fs = require('fs');

var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');
var mysql = require('mysql');
var nodemailer = require('nodemailer');
var validator = require('validator');
var crypto = require('crypto');
var sha1sum = crypto.createHash('sha1');
//email setup
var transport = nodemailer.createTransport("SMTP",{
			    	host: "email-smtp.us-east-1.amazonaws.com", // hostname
			    	secureConnection: true, // use SSL
			    	port: 465, // port for secure SMTP
			    	auth: {
			        	user: "AKIAJYPEIMSEIVGQHNTA",
			        	pass: "AqkTdt3g+a6jKvD6zYNUkLDnNwjskCkBQ4Joe7tpo9tP"
			    	}
			    });

var secret = 'x4asd1!234@!42b4b00n5';
var app = express();
var RedisStore = require('connect-redis')(express);

app.set('port', 3101);
app.set('views', __dirname + '/views');
app.set('view engine', 'jade');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser('i die, you die, everybody die'));
app.use(express.session({ store: new RedisStore(config.redis) }));

app.use(app.router);

app.use(express.static(path.join(__dirname, 'public')));

// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', routes.index);

app.post('/send', [],function(req,res){
	sha1sum = crypto.createHash('sha1');
	var mailOptions = {
	    from: req.body.from,
	    to: req.body.to,
	    subject: req.body.subject,
	    generateTextFromHTML:true,
	    html: req.body.html
	};
	console.log(req.body);
	var the_hash = sha1sum.update(mailOptions.to+mailOptions.subject+mailOptions.html+secret).digest('hex');
	console.log(the_hash);
	if(req.body.hash.length > 20 && the_hash == req.body.hash){
		sendMail(mailOptions,function(err,responseStatus){
			res.send(200,{status:1,responseStatus:responseStatus,error:err});
		});
	}else{
		res.send(200,{status:1,responseStatus:{message:'500 WRONG HASH'}});
	}
	
});

http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});


function sendMail(mailOptions,callback){

	transport.sendMail(mailOptions,function(error, responseStatus){
		console.log('sending ',mailOptions,responseStatus);
							callback(error,responseStatus);
	});
}