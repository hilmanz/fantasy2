//ROUTER
var tmp = {};
var App = Backbone.Router.extend({
  routes:{
    "selectTeam/:team_id":"selectTeam",    
    "select_player/:player_id":"select_player",
    "unselect_player/:player_id":"unselect_player",
  },
  selectTeam:selectTeam,
  select_player:select_player
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
	 $.each(tmp['available_teams'],function(k,v){
	 	if(v.uid == player_id){
	 		append_view(player_selected,'#selected',v);
	 	}
	 });
}
function unselect_player(player_id){
	 $.each(tmp['available_teams'],function(k,v){
	 	if(v.uid == player_id){
	 		append_view(player_selected,'#selected',v);
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