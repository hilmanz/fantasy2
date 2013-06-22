var path = require('path');
var gameplay = require(path.resolve('./libs/api/gameplay'));


exports.getLineup = function(req,res){
	gameplay.getLineup(req.params.id,
		function(err,rs){
			if(rs!=null){
				res.send(200,rs);
			}else{
				res.send(200,[]);
			}
	});
}
exports.getPlayers = function(req,res){
	gameplay.getPlayers(req.params.id,function(err,rs){
		if(rs!=null){
			res.json(200,rs);
		}else{
			res.send(200,[]);
		}
	});
}
exports.setLineup = function(req,res){
	
	gameplay.setLineup(req.body.team_id,
						JSON.parse(req.body.players),
						req.body.formation,
		function(err,rs){

			if(err){
				console.log(err.message);
				handleError(res);
			}else{
				if(rs!=null){
					res.json(200,{status:1,lineup:rs});
				}else{
					res.send(200,{status:0});
				}
			}
	});
	
}
exports.fixtures = function(req,res){
	gameplay.match.fixtures(function(err,rs){
		if(err){
			
			handleError(res);
		}else{
			if(rs!=null){
				res.json(200,{status:1,matches:rs});
			}else{
				res.send(200,{status:0});
			}
		}
	});
}
function handleError(res){
	res.send(501,{error:'no data available'});
}