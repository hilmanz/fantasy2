var path = require('path');
var team = require(path.resolve('./libs/api/team'));

exports.getTeams = function(req,res){
	team.getTeams(function(err,team){
		if(err) handleError(res);
		else{
			res.send(200,team);
		}
	});
}
exports.getPlayers = function(req,res){
	team.getPlayers(req.params.team_id,
	function(err,players){
		if(err) handleError(res);
		else{
			res.send(200,players);
		}
	});
}
function handleError(res){
	res.send(501,{error:'no data available'});
}