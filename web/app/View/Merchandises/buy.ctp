<?php
$pic = Configure::read('avatar_web_url').
				"merchandise/thumbs/0_".
				$item['pic'];
?>
<div id="catalogPage">
    <?php echo $this->element('infobar'); ?>
    <div id="thecontent">
        <div class="content">
        	<div class="titlePage">
				<h1 class="red">Online Catalog</h1>
				<h4>Where shall we ship your merchandise?</h4>
            </div>
            <div class="rowd">
				<div class="col-content">
					<div class="tr widget">
					<form class="shipaddress" 
						action="<?=$this->Html->url('/merchandises/order')?>" 
						method="post" 
						enctype="application/x-www-form-urlencoded">
						<h3>Personal Details</h3>
						<div class="row">
							<label>First Name</label>
							<input type="text" name="first_name" value="<?=h($first_name)?>"/>
						</div><!-- end .row -->
						<div class="row">
							<label>Last Name</label>
							<input type="text" name="last_name" value="<?=h($last_name)?>"/>
						</div><!-- end .row -->
						<div class="row">
							<label>Mobile Phone</label>
							<input type="text" name="phone" value="<?=h($phone_number)?>"/>
						</div><!-- end .row -->
						<h3>Shipping Address</h3>
						<div class="row">
							<label>Street</label>
							<input type="text" name="address" value=""/>
						</div><!-- end .row -->
						<div class="row">
							<label>City</label>
							<input type="text" name="city" value=""/>
						</div><!-- end .row -->
						<div class="row">
							<label>Province</label>
							<input type="text" name="province" value=""/>
						</div><!-- end .row -->
						<div class="row">
							<label>Country</label>
							<input type="text" name="country" value=""/>
						</div><!-- end .row -->
						<div class="row">
							<label>Post Code</label>
							<input type="text" name="zip" value=""/>
						</div><!-- end .row -->
						<div class="row">
							<input type="hidden" name="ct" value="<?=$csrf_token?>"/>
							<input type="button" value="Cancel" class="button" onclick="cancel();"/>
							<input type="submit" value="Confirm" class="button"/>
						</div><!-- end .row -->
					</form>
					</div><!-- end .widget -->
				</div><!-- end .col-content -->
				<div class="box4 fr">
					<div class="tr widget order-detail">
						<h2>Your Order</h2>
						<h4><?=h($item['name'])?></h4>
						<p class="price"><?=number_format($item['price_credit'])?> Coins</p>
						<div class="imagesCatalog tr widget">
							  <img src="<?=$pic?>" />
						</div>
					</div><!-- end .widget -->
				</div><!-- end .box4 -->
            </div><!-- end .row-3 -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #catalogPage -->

<script>
function cancel(){
	document.location="<?=$this->Html->url('/merchandises')?>";
}
</script>