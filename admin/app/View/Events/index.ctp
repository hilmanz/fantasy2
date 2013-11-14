<h3>
	Event Manager
</h3>
<div class="row">
	<a href="<?=$this->Html->url('/events/create')?>" class="button">Create Event</a>
</div>
<div class="row">
	<table width="100%">
		<tr>
			<td>Event</td>
			<td>Type</td>
			<td>Target</td>
			<td>Affected Item</td>
			<td>Amount</td>
			<td>Schedule</td>
			<td>Applied</td>
			<td>Status</td>
		</tr>
		<?php if(isset($rs)): foreach($rs as $r):
			$data = $r['Events'];
			
		?>
		<tr>
			<td><a href="#"><?=h($data['event_name'])?></a></td>
			<td>
				<?php
					if($data['event_type']==1){
						echo "Player Data";
					}else{
						echo "Master Data";
					}
				?>
			</td>
			<td>
				<?php
					switch($data['target_type']){
						case 1:
							echo 'Individual Team';
						break;
						case 2:
							echo 'All Teams';
						break;
						case 3:
							echo 'By Tier';
						break;
						case 4:
							echo 'Individual Player';
						break;
					}
				?>
			</td>
			<td>
				<?php
					if($data['affected_item']==1){
						echo "Money";
					}else{
						echo "Points";
					}
				?>
			</td>
			<td><?=number_format($data['amount'])?></td>
			<td><?=date("d/m/Y",strtotime($data['schedule_dt']))?></td>
			<td>
				<?php
					if($data['apply_dt']!=null){
						echo date("d/m/Y",strtotime($data['apply_dt']));
					}else{
						echo '-';
					}
					
				?>
			</td>
			<td>
				<?php
					if($data['n_status']==1){
						echo "Applied";
					}else if($data['n_status']==2){
						echo "Finished";
					}else{
						echo "Pending";
					}
				?>
			</td>
		</tr>
		<?php endforeach;endif;?>
	</table>
</div>