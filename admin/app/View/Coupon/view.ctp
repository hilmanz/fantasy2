<div class="titleBox">
	<h1>Coupon</h1>
</div>
<div class="theContainer">
	<div class="title">
		<h3></h3>
	</div>
	<div class="row">
		<?php
			echo $this->Form->create('Coupon',array('type' => 'file'));
		?>
		<?php
		echo $this->Form->input('vendor_name',array('default'=>$coupon['Coupon']['vendor_name']));   
		echo $this->Form->input('service_name',array('default'=>$coupon['Coupon']['service_name']));   
		echo $this->Form->input('description',array('default'=>$coupon['Coupon']['description'])); 
		echo $this->Form->input('coin_amount',array('default'=>intval($coupon['Coupon']['coin_amount']))); 
		echo $this->Form->input('ss_dollar',array('default'=>intval($coupon['Coupon']['ss_dollar']))); 
		?>
		<div>
			<input type="file" name="img"/>
		</div>
		<div>
			<?php
				$options = array('1'=>'Enabled','0'=>'Pending');
				echo $this->Form->input('n_status',

										array('label'=>false,'type'=>'select','options'=>$options,
											  'default'=>$coupon['Coupon']['n_status']));
			?>
		</div>
		<?php 
			echo $this->Form->end('Create');
		?>
		<a href="<?=$this->Html->url('/coupon')?>" class="button">Back to Coupon List</a>
		<a href="<?=$this->Html->url('/coupon/generate/'.$coupon['Coupon']['id'])?>" 
				class="button">
				Generate Codes
		</a>
	</div>

	<div class="row">
		<h3>Generated Codes (<?=number_format($coupon_count)?>)</h3>
		<table width="100%" class="table">
			<tr>
				<td>
					Code
				</td>
				<td>
					Used
				</td>
			</tr>
		</table>
	</div>
	<div class="row">
		<h3>Used Codes</h3>
		<table width="100%" class="table">
			<tr>
				<td>
					Redeem Date
				</td>
				<td>
					User
				</td>
				<td>
					Code
				</td>
				<td>
					Validity
				</td>
				<td>
					Status
				</td>
			</tr>
		</table>
	</div>
	<div class="row">
		<a href="#" class="button">Upload Data</a><br/>
		<a href="<?=$this->Html->url('/coupon/download/'.$coupon['Coupon']['id'])?>" class="button">Download Unused Codes</a>
	</div>
</div>

