<div class="theContainer">
<h3 class="titles">Player Info</h3>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataTable">
		<tbody>
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
		</tbody>
</table>
<h3 class="titles">Squad</h3>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataTable">
		<thead>
			<tr>
				<tr>
					<th>No.</th>
					<th>Name</th>
					<th>Position</th>
					<th>Plays</th>
					<th>Current Points</th>
					<!--<th>Last Week Performance (compare to overall team points)</th>-->
					<th>Basic Value</th>
					<th>Current Value</th>
				</tr>
		</thead>
		<tbody>
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
		</tbody>
</table>
<h3 class="titles">Previous Matches</h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dataTable">
		<thead>
			<tr>
				<tr>
					<th>Against</th>
					<th>Points</th>
					<th>Earnings</th>
				</tr>
		</thead>
		<tbody>
			<?php
				foreach($previous_matches as $n=>$s):
					
			?>
				<tr>
					<td>
						<a href="<?=$this->Html->url('/players/view_match/'.$user['User']['id'].'/'.$s['game_id'])?>">
							<?=$s['against']?>
						</a>
					</td>
					<td><?=number_format(@$s['points'])?></td>
					<td>
						<?=number_format(@$s['ticket_sold'])?>
						<?php if($s['ticket_sold_penalty']>0):?>
							(<?=number_format(@$s['ticket_sold_penalty'])?>)
						<?php endif;?>
					</td>
				</tr>
				<?php
				endforeach;
				?>
		</tbody>
</table>