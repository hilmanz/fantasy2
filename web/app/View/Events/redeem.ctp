<?php if($offer_valid):?>

<div id="maintenancePage">
	<h1 class="yellow">Penawaran Khusus </h1>
	<h2>
		<?=h($offer['email_body_txt'])?>
	</h2>
	<p>
		Penawaran ini hanya berlaku sebelum pertandingan minggu <?=$week?> dimulai.
	</p>
	<a class="backtohome button" href="<?=$this->Html->url('/events/confirm/1?osign='.$osign)?>">
		TERIMA PENAWARAN <?php if(strlen($offer['yes_txt'])>0):?>(<?=h($offer['yes_txt'])?>)<?php endif;?>
	</a>
	<a class="backtohome button" href="<?=$this->Html->url('/events/confirm/0?osign='.$osign)?>">
		TOLAK PENAWARAN <?php if(strlen($offer['no_txt'])>0):?>(<?=h($offer['no_txt'])?>)<?php endif;?>
	</a>
</div><!-- end #logoutpage -->
<?php else:?>
<div id="maintenancePage">
	<?php if($can_apply):?>
		<h1 class="yellow">OFFER IS EXPIRED </h1>
		<h2>Penawaran ini hanya berlaku sebelum pertandingan minggu <?=$week?> dimulai.</h2>
	<?php else:?>
		<?php if(isset($tier_fault)):?>
		<h1 class="yellow">ACCESS DENIED !</h1>
		<h2>Maaf, penawaran ini hanya berlaku untuk tim yang berada di tier <?=$tier?></h2>
		<?php else:?>
		<h1 class="yellow">OFFER IS EXPIRED </h1>
		<h2>Lo sudah pernah menjawab penawaran ini.</h2>
		<?php endif;?>
	<?php endif;?>
	<a class="backtohome button" href="<?=$this->Html->url('/manage/club')?>">Kembali ke Klab Saya</a>

</div><!-- end #logoutpage -->
<?php endif;?>