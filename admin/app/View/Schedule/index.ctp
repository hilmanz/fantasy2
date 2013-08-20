<h3>Game Schedule</h3>

<div class="row-2">
<table width="100%">
	<tr>
		<td>No</td>
		<td>Game_id</td>
		<td>Date</td>
		<td>Home</td>
		<td>Score</td>
		<td>Away</td>
		<td>Period</td>
		<td>Attendance</td>
		<td>Processed</td>
	</tr>
	<?php
	foreach($match as $n=>$m):
	?>
	<tr>
		<td><?=$n+1?></td>
		<td><?=h($m['Fixture']['game_id'])?></td>
		<td><?=date("d-m-Y H:i:s",strtotime($m['Fixture']['match_date']))?></td>
		<td><?=h($m['Home']['name'])?></td>
		<td><?=h($m['Fixture']['home_score'].' - '.$m['Fixture']['away_score'])?></td>
		<td><?=h($m['Away']['name'])?></td>
		<td><?=h($m['Fixture']['period'])?></td>
		<td><?=number_format($m['Fixture']['attendance'])?></td>
		<td><?=h($m['Fixture']['is_processed'])?></td>
	</tr>
	<?php endforeach;?>
</table>
</div>