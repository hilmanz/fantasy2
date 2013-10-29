/**
mailer.js
these app will process all email queue and sends it (for now) using Amazon's SES
these app will be act as worker. each worker will process 1 queue at a time.
we only process 4000 email queues, after that, the worker will quit.
we utilize foreverjs to spawn it again to process another 4000 queues.
**/
/////THE MODULES/////////
var fs = require('fs');
var path = require('path');
var config = require('./config').config;
var xmlparser = require('xml2json');
var master = require('./libs/master');
var async = require('async');
var mysql = require('mysql');
var nodemailer = require('nodemailer');
var transport = nodemailer.createTransport("SMTP",{
										    	host: "email-smtp.us-east-1.amazonaws.com", // hostname
										    	secureConnection: true, // use SSL
										    	port: 465, // port for secure SMTP
										    	auth: {
										        	user: "AKIAJYPEIMSEIVGQHNTA",
										        	pass: "AqkTdt3g+a6jKvD6zYNUkLDnNwjskCkBQ4Joe7tpo9tP"
										    	}
										    });
/////DECLARATIONS/////////


/////THE LOGICS///////////////
//mysql pool
var pool  = mysql.createPool({
   host     : config.database.host,
   user     : config.database.username,
   password : config.database.password,
});
var no = 0;
runLoop();

function runLoop(){
	pool.getConnection(function(err,conn){
		async.waterfall([
			function(callback){
				conn.query("SELECT * FROM ffgame.email_queue WHERE n_status=0 LIMIT 1;"
							,[],
				function(err,rs){
					try{
						callback(err,rs[0]);
					}catch(e){
						callback(err,null);
					}
				});
			},
			function(queue,callback){
				if(queue!=null){
					console.log('processing #',queue.id);
					//set sending status
					conn.query("UPDATE ffgame.email_queue SET n_status=1 WHERE id = ?",
							[queue.id],
							function(err,rs){
								callback(err,queue);
							});
				}else{
					callback(err,queue);
				}
				
			},
			function(queue,callback){
				//sending the email
				if(queue!=null){
					var mailOptions = {
					    from: "Supersoccer Football Manager <footballmanager@supersoccer.co.id>",
					    to: queue.email,
					    subject: queue.subject,
					    generateTextFromHTML:true,
					    html: queue.html_text
					}

					transport.sendMail(mailOptions,function(error, responseStatus){
						if(!error){
							callback(null,error,queue,responseStatus);	
						}else{
							console.log('error : ',error.message);
							callback(null,null,queue,null);
						}
					    
					});
				}else{
					callback(null,null,queue,null);
				}
				
			},
			function(errorStatus,queue,responseStatus,callback){
				if(errorStatus!=null){
					console.log('error : ',errorStatus.message);
				}
				if(queue!=null && responseStatus!=null){
					if(responseStatus.message.search('250 OK')){
						console.log('SENT !');
						conn.query("UPDATE ffgame.email_queue SET n_status=2,send_dt=NOW() WHERE id=?",
									[queue.id],function(err,rs){
							callback(err,queue);	
						});
					}else{
						console.log('Failed:',responseStatus.message);
						conn.query("UPDATE ffgame.email_queue SET n_status=3 WHERE id=?",
									[queue.id],function(err,rs){
							callback(err,queue);	
						});
					}	
				}else{
					callback(err,queue);	
				}
			}
		],
		function(err,rs){
			conn.end(function(err){
				if(rs==null){
					pool.end(function(err){
						console.log('no more data, pool closed');
						console.log('closing smtp transport');
						transport.close();
					});
				}else{
					runLoop();	
				}
			});
		});
	});
}

function processQueue(cb){
	console.log('process queue');
	cb();
}