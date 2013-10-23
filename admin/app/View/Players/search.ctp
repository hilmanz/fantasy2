<h3>Players</h3>
<div style="overflow:auto;">

<form action="<?=$this->Html->url('/players/search')?>" method="get" 
	enctype="application/x-www-form-urlencoded">
	<input type="text" name="q" value=""/><input type="submit" name="btn" value="Search"/>
</form>
<table width="100%">
	<tr>
		<td>No</td>
		<td></td>
		<td><?php echo $this->Paginator->sort('user_id','User');?></td>
		<td>Original Team</td>
		<td>Phone Number</td>
		<td>Joined</td>
		<td>Registration</td>
		<td>Team Name</td>
		<td>Rank</td>
		<td>Import Player Counts</td>
		<td>Points</td>
		<td>Money</td>
		<td>Games</td>
		<td>Passing and Attacking</td>
		<td>Defending</td>
		<td>Goalkeeping</td>
		<td>Mistakes and errors</td>
	</tr>
	<?php

	foreach($rs as $n=>$m):
		
	?>
	<tr>
		<td><?=$m['no']?></td>
		<td>
			<?php if(strlen($m['User']['avatar_img'])>1):?>
				<img width="50"
					src="<?=Configure::read('AVATAR_URL').'120x120_'.$m['User']['avatar_img']?>"/>
			<?php else:?>
				<img src="http://graph.facebook.com/<?=$m['User']['fb_id']?>/picture"/>
			<?php endif;?>
		</td>
		
		<td><a href="<?=$this->Html->url('/players/view/'.$m['User']['id'])?>">
			<?=h($m['User']['name'])?><br/>
			<?=h($m['User']['fb_id'])?><br/>
			<?=h($m['User']['email'])?>
			</a>
		</td>
		<td><?=h($m['MasterTeam']['name'])?></td>
		
		<td><?=h($m['User']['phone_number'])?></td>
		<td><?=h($m['User']['register_date'])?></td>
		<td>
			<?php if($m['User']['register_completed']==1):?>
				Completed
			<?php else:?>
				In Progress
			<?php endif;?>
		</td>
		<td>
			<a href="<?=$this->Html->url('/players/view/'.$m['User']['id'])?>">
				<?=h($m['Team']['team_name'])?>
			</a>
		</td>
		<td><?=number_format(@$m['Point']['rank'])?></td>
		<td><?=number_format(@$m['ImportPlayerCounts'])?></td>
		
		<td><?=number_format(@$m['Point']['points'])?></td>
		<td><?=number_format(@$m['Money'])?></td>
		<td><?=number_format(@$m['Summary']['games'])?></td>
		<td><?=number_format(@$m['Summary']['passing_and_attacking'])?></td>
		<td><?=number_format(@$m['Summary']['defending'])?></td>
		<td><?=number_format(@$m['Summary']['goalkeeping'])?></td>
		<td><?=number_format(@$m['Summary']['mistakes_and_errors'])?></td>
	</tr>
	<?php endforeach;?>
</table>
</div>
<div class="row-2">
<?php echo $this->Paginator->numbers();?>
</div>