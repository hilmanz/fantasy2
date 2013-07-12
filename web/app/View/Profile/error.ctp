<div>
	<?php echo $this->Session->flash();?>
	<?php
		$error_type = ($error_type!=null)?$error_type : '';
		if($error_type=='team'):
	?>
	<a href="<?=$this->Html->url('/profile/register_team')?>">Kembali ke halaman sebelumnya</a>
	<?php else:?>
	<a href="<?=$this->Html->url('/')?>">Kembali ke halaman utama</a>
	<?php endif;?>
</div>