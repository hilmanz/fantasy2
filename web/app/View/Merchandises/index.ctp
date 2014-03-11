<?php
$browse_url = $this->Html->url('/merchandises/?cid=');
$subtitle = "";
if(isset($category_name)){
	$subtitle = "- ".$category_name;
}
?>
<div id="catalogPage">
      <div class="rowd">
     	 <?php echo $this->element('infobar'); ?>
      </div>
    <div id="thecontent">
        <div class="content">
        	<div class="titlePage">
				<h1 class="red">
					Online Catalog <?=$subtitle?>
				</h1>
				<h4>Tukarkan coin elo dan dapatkan merchandise seru dari Supersoccer Football Manager</h4>
				<h4>Ongkos Kirim Ditanggung Pemenang</h4>
            </div>
            <div class="rowd">
				<div class="col-content fr tr widget">
					<?php if($has_merchandise):?>
					<?php 
							foreach($rs as $r):
						$pic = Configure::read('avatar_web_url').
										"merchandise/thumbs/0_".
										$r['MerchandiseItem']['pic'];
						$buy_url = $this->Html->url('/merchandises/buy/'.$r['MerchandiseItem']['id']);
						$view_url = $this->Html->url('/merchandises/view/'.$r['MerchandiseItem']['id']);
					?>
					<div class="catalog-item">
						<div class="imagesCatalog tr widget">
							<?php if($r['MerchandiseItem']['available'] > 0):?>
							<a href="<?=$buy_url?>">
							<?php endif;?>
							  <img src="<?=$this->Html->url($pic)?>" />
							<?php if($r['MerchandiseItem']['available'] > 0):?>
							</a>
							<?php endif;?>
						</div>
						<div class="detailCatalog">
							<h4><?=h($r['MerchandiseItem']['name'])?></h4>
							<p class="price"><?=number_format($r['MerchandiseItem']['price_credit'])?>
								Coins
							</p>
							<p class="idrprice">
								Rp. <?=number_format($r['MerchandiseItem']['price_money'])?>
							</p>

							<?php if($r['MerchandiseItem']['available'] > 0):?>
								<a class="buyBtn button" href="<?=$view_url?>">VIEW</a>
							<?php else:?>
								SOLD OUT
							<?php endif;?>
						</div>
						
					</div>
					<?php endforeach;?>
					<div class="pagings tr">
						<?php
			              echo $this->Paginator->prev(__('Sebelumnya'), array(), null, 
			                                          array('class' => 'prev'));
			              ?>
			              <?php
			              echo $this->Paginator->next(__('Berikutnya'), array(), null, 
			                                      array('class' => 'next'));
			              ?>
					</div><!-- end .col-content -->
					<?php else:?>
					<div class="desc">
						Saat ini belum ada merchandise yang dapat di beli
					</div>
					<?php endif;?>
				</div><!-- end .box4 -->
				<div class="box4 fr">
					<div class="widget tr catalog-categories">
						<form>
							<select name="cid" class="styled">
								<option value="0">Categories</option>
								<?php
								foreach($categories as $category):
								?>
								<option value="<?=$category['MerchandiseCategory']['id']?>">
									<?=h($category['MerchandiseCategory']['name'])?>
								</option>
								<?php
								endforeach;
								?>
							</select>
						</form>
					</div><!-- end .widget -->
					<?php if(isset($sub_categories) && sizeof($sub_categories) > 0 ):?>
					<div class="widget tr catalog-list">
						<h2>Browse the Catalog</h2>
						<ul>
							<?php
								foreach($sub_categories as $subcat):
									$jump_url = $browse_url.$subcat['MerchandiseCategory']['id'];

							?>
							<li>
								<a href="<?=$jump_url?>">
									<?=h($subcat['MerchandiseCategory']['name'])?>
								</a>
							</li>
							<?php endforeach;?>
						</ul>
					</div><!-- end .widget -->
					<?php endif;?>
					<div class="widget tr catalog-list">
						<a href="<?=$this->Html->url('/merchandises/cart')?>" class="button">
							Keranjang Belanja
						</a>
						<a href="<?=$this->Html->url('/merchandises/history')?>" class="button">
							Order Tracking
						</a>
					</div>
					
                    <?php for($i=0;$i<sizeof($sidebar_banner);$i++):?>
			        	<div class="banner300x250">
						     <a href="javascript:banner_click(<?=$sidebar_banner[$i]['Banners']['id']?>,'<?=$sidebar_banner[$i]['Banners']['url']?>');" target="_blank">
			                    <img src="<?=$this->Html->url(Configure::read('avatar_web_url').
			                                $sidebar_banner[$i]['Banners']['banner_file'])?>" />
			                </a>
			            </div>
		            <?php endfor;?>
				</div><!-- end .box4 -->
            </div><!-- end .row-3 -->
        </div><!-- end .content -->
    </div><!-- end #thecontent -->
</div><!-- end #catalogPage -->

<script>
var browse_url = "<?=$browse_url?>";
$("select[name=cid]").change(function(e){
	document.location = browse_url+''+parseInt($(this).val());
});
</script>