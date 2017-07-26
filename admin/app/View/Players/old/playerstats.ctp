<h3>Master Player Stats</h3>

<div class="row-2">
<div class="msg alert">Loading player data....</div>
<table width="100%" id="tbl">
	
</table>
</div>
<?php echo $this->Html->script('jquery.dataTables.min');?>
<script>
var start = 0;
var data = [];

function getdata(){
	api_call("<?=$this->Html->url('/players/player_performances/?start=')?>"+start,
		function(response){
			if(response.status==1){
				if(response.data.length > 0){
					for(var i in response.data){
						var uid = response.data[i].uid.split('p').join('');
						var team_id = response.data[i].team_id.split('t').join('');
						data.push([
								' <img src="http://omo.akamai.opta.net/image.php?custID=c8bb60c8f6d0184c33a87e6f3041b9cc&sport=football&entity=player&description='+team_id+'&dimensions=103x155&id='+uid+'"/>',
								response.data[i].name,
								response.data[i].position,
								response.data[i].team_name,
								response.data[i].stats.games,
								response.data[i].stats.passing_and_attacking,
								response.data[i].stats.defending,
								response.data[i].stats.goalkeeping,
								response.data[i].stats.mistakes_and_errors,
							]);
					}
					start += 20;
					$(".msg").html($(".msg").html()+'.');
					getdata();
				}else{
					//draw table
					draw_table();
					$(".msg").hide();
				}
			}
		});
}

function draw_table(){
	$('#tbl').dataTable( {
		"aaData": data,
		"aoColumns": [
			{ "sTitle": "" },
			{ "sTitle": "Name" },
			{ "sTitle": "Position" },
			{ "sTitle": "Team" },
			{ "sTitle": "Games", "sClass": "center" },
			{ "sTitle": "Pass/Atk", "sClass": "center" },
			{ "sTitle": "Def", "sClass": "center" },
			{ "sTitle": "GK", "sClass": "center" },
			{ "sTitle": "Mistakes/Errors", "sClass": "center" },
		]
	} );
}

getdata();
</script>