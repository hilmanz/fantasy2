<?php
$pic = Configure::read('avatar_web_url').
				"merchandise/thumbs/0_".
				$item['pic'];
?>
<div id="catalogPage">
      <div class="rowd">
     	 <?php echo $this->element('infobar'); ?>
      </div>
    <div id="thecontent">
        <div class="content">
        	<div class="titlePage">
				      <h1 class="red">Online Catalog</h1>
				
            </div>
            <div class="rowd">
      				<div class="col-content">
      					`<?=h($item['name'])?>` telah dimasukkan ke dalam keranjang belanja
                <div>
                  <a href="<?=$this->Html->url('/merchandises')?>" class="button">Kembali Belanja</a>
                  <a href="<?=$this->Html->url('/merchandises/cart')?>" class="button">Lihat Keranjang Belanja</a>
                </div>
      				</div><!-- end .col-content -->
            </div><!-- end .row-3 -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #catalogPage -->

<script>
function cancel(){
	document.location="<?=$this->Html->url('/merchandises')?>";
}
</script>