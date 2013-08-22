<h3>Matches</h3>
<div>The list of ongoing and finished matches based on Opta.</div>
<div>
<table width="100%">
	<tr>
		<td>GameID</td>
		<td>Week</td>
		<td>Date</td>
		<td>Home</td>
		<td>Score</td>
		<td>Away</td>
		<td>Period</td>
		<td>Attendance</td>
		<td>Ref</td>
		<td>Venue</td>
		<td>Last Update</td>
	</tr>
	<?php
	if(isset($data)):foreach($data as $d):
	?>
	<tr>
		<td><a href="<?=$this->Html->url('/matches/view/'.$d['Matches']['game_id'])?>"><?=$d['Matches']['game_id']?></a></td>
		<td><?=$d['Matches']['matchday']?></td>
		<td><?=date("d/m/Y H:i:s",strtotime($d['Matches']['matchdate']))?></td>
		<td><?=$d['home']['name']?></td>
		<td><?=intval($d['Matches']['home_score'])?> - <?=intval($d['Matches']['away_score'])?></td>
		<td><?=$d['away']['name']?></td>
		<td><?=$d['Matches']['period']?></td>
		<td><?=$d['Matches']['attendance']?></td>
		<td><?=$d['Matches']['referee']?></td>
		<td><?=$d['Matches']['venue_name']?></td>
		<td><?=$d['Matches']['last_update']?></td>
	</tr>
	<?php endforeach;endif;?>
</table>
</div>
<div class="row">
<?php if(isset($data)):?>
<?php echo $this->Paginator->numbers();?>
<?php endif;?>
</div>
