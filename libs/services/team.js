var path = require('path');
var team = require(path.resolve('./libs/api/team'));

exports.getTeams = function(req,res,callback){
	team.getTeams(function(err,team){
		if(err) handleError(res);
		else{
			res.send(200,team);
		}
	});
}
function handleError(res){
	res.send(501,'No Data Available');
}