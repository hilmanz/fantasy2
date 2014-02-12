<div class="titleBox">
	<h1>Coupon</h1>
</div>
<div class="theContainer">
	<div class="title">
		<h3>Create Coupon / Voucher</h3>
	</div>
	<div class="row">
		<?php
			echo $this->Form->create('Coupon',array('type' => 'file'));
		?>
		<?php
		echo $this->Form->input('vendor_name');   
		echo $this->Form->input('service_name');   
		echo $this->Form->input('description'); 
		echo $this->Form->input('coin_amount'); 
		echo $this->Form->input('ss_dollar'); 
		?>
		<div>
			<input type="file" name="img"/>
		</div>
		<div>
			<?php
				$options = array('1'=>'Enabled','0'=>'Pending');
				echo $this->Form->select('n_status', $options);
			?>
		</div>
		<?php 
			echo $this->Form->end('Create');
		?>
		<a href="<?=$this->Html->url('/coupon')?>" class="button">Back to Coupon List</a>
	</div>
</div>
