<?php
if(isset($item)){
$pic = Configure::read('avatar_web_url').
				"merchandise/thumbs/0_".
				$item['pic'];	
}

?>
<div id="catalogPage">
      <div class="rowd">
     	 <?php echo $this->element('infobar'); ?>
      </div>
   
    <div id="thecontent">
        <div class="content">
        	<div class="titlePage">
				<h1 class="red">Online Catalog</h1>
				<h4>Order Complete!</h4>
            </div>
            <div class="rowd">
				<div class="col-content">
					<div class="tr widget">
						<h1>Your order has been successfully processed.</h1>
						<h3>Thanks for your purchase!</h3>
						<p><a class="button" href="<?=$this->Html->url('/manage/team')?>">Back to the Game</a></p>
					</div><!-- end .widget -->
				</div><!-- end .col-content -->
				<div class="box4 fr">
					
				</div><!-- end .box4 -->
            </div><!-- end .row-3 -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
	
</div><!-- end #catalogPage -->