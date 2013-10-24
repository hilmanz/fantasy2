<h3>Player Info</h3>
<table>
	<tr>
		<td><strong>Name</strong></td><td><?=h($user['User']['name'])?></td>
		<td><strong>Team</strong></td><td><?=h($user['Team']['team_name'])?> (<?=$team_data['c']['name']?>)</td>
		<td><strong>Budget</strong></td><td>SS$ <?=number_format($budget)?></td>
	</tr>
	<tr>
		<td><strong>FBID</strong></td><td><?=h($user['User']['fb_id'])?></td><td><strong>Joined</strong></td>
		<td><?=date("d-m-Y H:i:s",strtotime($user['User']['register_date']))?></td>
		<td><strong>Points</strong></td><td><?=@number_format($point['points'])?></td>
	</tr>
	<tr>
		<td><strong>Email</strong></td><td><?=h($user['User']['email'])?></td>
		<td><strong>Reg.Status</strong></td>
		<td>
			<?php if($user['User']['register_completed']==1):?>
				Completed
			<?php else:?>
				In Progress
			<?php endif;?>
		</td>
		<td><strong>Rank</strong></td><td><?=@number_format($point['rank'])?></td>
	</tr>
	<tr>
		<td><strong>Mobile</strong></td><td><?=h($user['User']['phone_number'])?></td>
		<td></td><td></td>
		<td><strong>Total Matches</strong></td><td><?=number_format($total_matches)?></td>
	</tr>
	<tr>
		<td><strong>Location</strong></td><td><?=h($user['User']['location'])?></td>
		<td></td><td></td><td></td><td></td>
	</tr>
</table>
<h3>Squad</h3>
<table width="100">
<tr>
	<td>No.</td>
	<td>Name</td>
	<td>Position</td>
	<td>Plays</td>
	<td>Current Points</td>
	<!--<td>Last Week Performance (compare to overall team points)</td>-->
	<td>Basic Value</td>
	<td>Current Value</td>
</tr>
<?php
	foreach($squad as $n=>$s):
		if($s['points']!=0){
	      $last_performance = floatval($s['last_performance']);
	      $performance_bonus = getTransferValueBonus($last_performance,intval($s['transfer_value']));
	    }else{
	      $performance_bonus = 0;
	    }
		$transfer_value = $s['transfer_value'] + $performance_bonus;
		
?>
<tr>
	<td><?=$n+1?></td>
	<td><?=h($s['name'])?></td>
	<td><?=$s['position']?></td>
	<td><?=number_format(@$s['total_plays'])?></td>
	<td><?=number_format(@$s['points'])?></td>
	<!--<td><?=number_format(@$s['last_performance'])?></td>-->
	<td><?=number_format($s['transfer_value'])?></td>
	<td><?=number_format($transfer_value)?></td>
</tr>
<?php
endforeach;
?>
</table>