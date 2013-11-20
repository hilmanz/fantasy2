<h3>
	Create Triggered Event
</h3>
<div class="row">
	<a href="<?=$this->Html->url('/events/')?>" class="button">Back to Events</a>
</div>

<div class="row">
<form action="<?=$this->Html->url('/events/create2')?>" method="post" enctype="multipart/form-data">
<?php
if($data['event_type']==1 || $data['event_type']==2):
?>
<table width="100%">
	<tr>
		<td valign="top">
			Choose Reward (pick one)
		</td>
		<td>
			<div>
			<input type='radio' name='reward_type' value='1' checked/>
			Money SS$<input type="text" name='money_reward' value="0" 
						style="width:200px;margin-left:5px"/>
			</div>
			<div>
			<input type='radio' name='reward_type' value='2'/>
			Bonus Points <input type="text" name='points_reward' value="0" 
						style="width:200px;margin-left:5px"/>pts
			</div>
			<div>
			<input type='radio' name='reward_type' value='3'/>
			Bonus Overall Point's Modifier (in percent) 
			<input type="text" name='point_mod_reward' value="0" 
						style="width:200px;margin-left:5px"/> %
			</div>
		</td>
	</tr>
	
	
	<tr>
		<td colspan="2">
			<input type="hidden" name="step" value="3"/>
			<input type="submit" name="btn" value="NEXT"/>
		</td>
	</tr>
</table>
<?php else:?>
<div class="progress">Loading </div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataTable dataTableTeam" id="tbl">

</table>
<table width="100%">
	
	<tr>
		<td colspan="2">
			<input type="hidden" name="step" value="3"/>
			<input type="submit" name="btn" value="NEXT"/>
		</td>
	</tr>
</table>
<?php echo $this->Html->script('jquery.dataTables.min');?>
<script>
	var start = 0;
	var data = [];
	function getdata(){
		api_call("<?=$this->Html->url('/events/master_players/?start=')?>"+start,
			function(response){
				if(response.status==1){
					if(response.data.length > 0){
						for(var i in response.data){
							data.push([
									response.data[i].name,
									response.data[i].known_name,
									response.data[i].position,
									response.data[i].team_name,
									response.data[i].transfer_value,
									'<input class="selectbox" type="radio" name="offered_player_id" value="'+
											response.data[i].id+'"/>'
								]);
						}
						start = response.next_offset;
						$(".progress").html($(".progress").html()+'.');
						getdata();
					}else{
						//draw table
						draw_table();
						$(".progress").hide();
						
					}
				}
			});
	}
	function draw_table(){
		$('#tbl').dataTable( {
			"fnDrawCallback":function(){
				//initClickEvents();
			},

			"aaData": data,
			"aoColumns": [
				{ "sTitle": "Name" },
				{ "sTitle": "Known Name" },
				{ "sTitle": "Position" },
				{ "sTitle": "Club" },
				{ "sTitle": "Base Transfer" },
				{ "sTitle": "Select"}
			]
		} );
	}
	getdata();
</script>
<?php endif;?>
</form>
</div>
 <script>
  $(document).ready(function() {
  	$( "#datepicker" ).datepicker();
    $( "#datepicker" ).datepicker("option", "dateFormat", "dd/mm/yy");
     $( "#datepicker" ).datepicker("hide");
  });
  </script>