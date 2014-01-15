<h3>
	Give Funds
</h3>

<h4>Search Users</h4>
<div class="row">
Enter the list of user's IDs.
</div>
<div class="row">
	<form id="frm1" action="<?=$this->Html->url('/funds/add')?>"
		  method="POST"
		  enctype="application/x-www-form-urlencoded">

	<div>
		<textarea name="id_list" rows="20"></textarea>
	</div>
	<div>
		<a href="javascript:;" class="button" id="btn-search">Search</a> 
	</div>
	</form>

</div>
<div class="row">
	<form action="<?=$this->Html->url('/funds/bulk_send')?>"
		 method="POST"
		  enctype="application/x-www-form-urlencoded">
			<table class="table">
				<tr>
					<td>User Id</td>
					<td>Player Name</td>
					<td>Team</td>
					<td>Original Team</td>
					<td>Email</td>
					<td>Amount</td>
				</tr>
				<?php foreach($teams as $team):?>
				<tr>
					<td>
						<?=intval($team['game_team']['id']) + intval(Configure::read('RANK_RANDOM_NUM'))?>
					</td>
					<td><?=h($team['user']['name'])?></td>
					<td><?=h($team['teams']['team_name'])?></td>
					<td><?=h($team['master_team']['name'])?></td>
					<td><?=h($team['user']['email'])?></td>
					<td>ss$ <input type="text" name="amount" value="0"/></td>
				</tr>
				<?php endforeach;?>
			</table>
			<input type="text" placeholder="Type a reason here..." value=""/>
			<a href="#" class="button">Send Funds</a>
	</form>
</div>
<script>
$("#btn-search").on('click',function(e){
	$("#frm1").submit();
});
</script>