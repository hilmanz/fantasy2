var path = require('path');
var users = require(path.resolve('./libs/api/users'));
exports.setPool = function(pool){
	users.setPool(pool);
}
exports.register = function(req,res){
	users.register({
			name:req.body.name,
			email:req.body.email,
			fb_id:req.body.fb_id,
			phone:req.body.phone
		},
		function(err,rs){
			if(rs!=null){
				res.send(200,{status:1,message:'the user is successfully registered !'});
			}else{
				res.send(200,{status:0,err:"the user cannot be registered of it's already exists"});
			}
	});
}

function handleError(res){
	res.send(501,{error:'no data available'});
}
