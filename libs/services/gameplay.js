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
			res.send(200,rs);
		}else{
			res.send(200,[]);
		}
	});
}
function handleError(res){
	res.send(501,{error:'no data available'});
}