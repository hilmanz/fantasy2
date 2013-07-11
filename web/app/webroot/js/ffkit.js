//ROUTER
var tmp = {};
var App = Backbone.Router.extend({
  routes:{
    "selectTeam/:team_id":"selectTeam",    
    "select_player/:player_id":"select_player",
    "unselect_player/:player_id":"unselect_player",
    "save_formation":"save_formation",
  },
  selectTeam:selectTeam,
  select_player:select_player,
  unselect_player:unselect_player,
  save_formation:save_formation
});
function save_formation(){
	if(typeof selectedVal['formations'] !== 'undefined'){
		var formation = selectedVal['formations'].value;
		console.log(formation);
		//make sure that all the lineup is consist of 11 players.
		getLineups(function(total,lineup){
			if(total==11){
				//save the lineup
				var data = {};
				data.formation = formation;
				for(var i in lineup){
					data[lineup[i].name] = parseInt(lineup[i].value);
				}
				console.log(data);
				if(typeof api_url !== 'undefined'){
					api_post(api_url+'game/save_lineup',data,function(response){
						console.log(response);
					});
				}
			}
		});
	}
	document.location="#";
}
function getLineups(callback){
	var n_player = 0;
	var players = [];
	$.each($("#the-formation").children(),function(k,player){
	    //console.log(player);
	    console.log($(player).attr('id'));
	    console.log($(player).find('a').attr('no'));
	    if($(player).find('a').attr('no')!=undefined){
	    	players.push({
	    		name:'player-'+$(player).find('a').attr('no'),
	    		value: $(player).attr('id').replace('p','')
	    	});
	        n_player++;
	    }
	    if(k>=10){
	        callback(n_player,players);
	    }
	});
}
function selectTeam(team_id,teams){
	$("input[name='team_id']").val(team_id);
	var team_name = "";
	$.each(team_list,function(k,v){
		if(v.uid==team_id){
			$("input[name='team_name']").val(v.name);
			return true;
		}
	});
}
function select_player(player_id){
	isPlayerSelected(player_id,function(check){
		if(!check){
			$.each(tmp['available_teams'],function(k,v){
			 	if(v.uid == player_id){
			 		append_view(player_selected,'#selected',v);
			 	}
			 });
		}
	});
	 
}
function populate_selected(callback){
	var s = "";
	var n = $("#selected").children().length;
	$.each($("#selected").children(),function(k,v){
			if(k>0){
				s += ",";
			}
			s += $(v).find('a').attr('id');
			if(n-1 == k){
				callback(s);
			}
	});
}
function isPlayerSelected(player_id,callback){
	var n = $("#selected").children().length;
	var isSelected = false;
	if(n>0){
		$.each($("#selected").children(),function(k,v){
			if($(v).find('a').attr('id') == player_id){
				isSelected = true;
			}
			if(n == k+1){
				callback(isSelected);
			}
		});
	}else{
		callback(isSelected);
	}
}
function unselect_player(player_id){
	//$.each($("#selected").children(),function(k,v))
	$.each($("#selected").children(),function(k,v){
		if($(v).find('a').attr('id') == player_id){
			$(v).remove();
			return true;
		}
	});
}
$(document).ready(function(){
  var app = new App();
  Backbone.history.start();
});



//other functions
function api_call(u,c){
	$.ajax({
		  url: u,
		  dataType: 'json',
		  success: c
		}
	);
}
function api_post(u,d,c){
	$.ajax({
	  url: u,
	  dataType: 'json',
	  type:'POST',
	  data:d,
	  success: c});	
}
function render_view(tpl_source,target,data){
	try{
		var View = Backbone.View.extend({
	        initialize: function(){
	            this.render();
	        },
	        render: function(){
	            var variables = data;
	            var template = _.template($(tpl_source).html(),variables);
	            this.$el.html(template);
	        }
	    });
	    var view = new View({el:$(target)});
	    
   }catch(error){
   		console.log(error.message);
   }
}
function prepend_view(tpl_source,target,data){
	try{
		var View = Backbone.View.extend({
	        initialize: function(){
	            this.render();
	        },
	        render: function(){
	            var variables = data;
	            var template = _.template($(tpl_source).html(),variables);
	            this.$el.prepend(template);
	        }
	    });
	    var view = new View({el:$(target)});
   }catch(error){
   	 	
   }
}
function append_view(tpl_source,target,data){
	try{
		var View = Backbone.View.extend({
	        initialize: function(){
	            this.render();
	        },
	        render: function(){
	            var variables = data;
	            var template = _.template($(tpl_source).html(),variables);
	            this.$el.append(template);
	            this.$el.css('display','none');
	            this.$el.fadeIn();
	        }
	    });
	    var view = new View({el:$(target)});
	    
   }catch(error){
   	 
   }
}