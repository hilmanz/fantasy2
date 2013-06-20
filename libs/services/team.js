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
exports.getTeamById = function(req,res){
	team.getTeamById(req.params.id,
	function(err,team){
		if(err) handleError(res);
		else{
			res.send(200,team);
		}
	});
}
exports.create = function(req,res){
	team.create({
		fb_id: req.body.fb_id,
		team_id: req.body.team_id,
		players: JSON.parse(req.body.players),
	},
	function(err,result){
		if(err) handleError(res);
		if(result!=null){
			res.send(200,{status:1,message:'Your team has been successfully created !'});
		}else{
			res.send(200,{status:0,message:'Oops, cannot create the team.'});
		}
	});
}
function handleError(res){
	res.send(501,{error:'no data available'});
}