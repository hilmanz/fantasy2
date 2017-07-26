<h3>Players</h3>
<h4>Total Players : <?=number_format($total_users)?></h4>
<div class="row-2">
<table width="100%">
	<tr>
		<td>No</td>
		<td></td>
		<td>User</td>
		<td>Phone Number</td>
		<td>Joined</td>
		<td>Registration</td>
		<td>Team Name</td>
		<td>Rank</td>
		<td>Points</td>
	</tr>
	<?php
	foreach($rs as $n=>$m):
	?>
	<tr>
		<td><?=$n+1?></td>
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
		<td><?=number_format(@$m['Point']['points'])?></td>
	</tr>
	<?php endforeach;?>
</table>
</div>
<div class="row-2">
<?php echo $this->Paginator->numbers();?>
</div>