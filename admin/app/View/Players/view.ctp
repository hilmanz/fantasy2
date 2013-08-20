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
	<td>Points</td>
	<td>Last Performance</td>
	<td>Value</td>
</tr>
<?php
	foreach($squad as $n=>$s):
		$transfer_value = $s['b']['transfer_value'];
		$performance_value = round(($s['stats']['last_performance']['total_performance']/100)
								* $transfer_value);
		$transfer_value += $performance_value;
?>
<tr>
	<td><?=$n+1?></td>
	<td><?=$s['b']['name']?></td>
	<td><?=$s['b']['position']?></td>
	<td><?=number_format(@$s['stats']['total_plays'])?></td>
	<td><?=number_format(@$s['stats']['total_points'])?></td>
	<td><?=number_format(@$s['stats']['last_performance']['total_performance'])?></td>
	<td><?=number_format($transfer_value)?></td>
</tr>
<?php
endforeach;
?>
</table>