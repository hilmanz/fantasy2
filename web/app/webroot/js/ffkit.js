//ROUTER
var tmp = {};
var App = Backbone.Router.extend({
  routes:{
    "selectTeam/:team_id":"selectTeam",    
    "select_player/:player_id":"select_player",
    "unselect_player/:player_id":"unselect_player",
  },
  selectTeam:selectTeam,
  select_player:select_player,
  unselect_player:unselect_player
});

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