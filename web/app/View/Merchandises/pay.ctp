
<div id="catalogPage">
      <div class="rowd">
     	 <?php echo $this->element('infobar'); ?>
      </div>
    <div id="thecontent">
        <div class="content pad20">
        	<div class="titlePage">
				<h1 class="red">
					Online Catalog
				</h1>
				<h4>Detil Pemesanan
				</h4>
            </div>
            <div class="rowd">
				<div class="col-content">
					<div class="tr widgets">
						<h1>Kode Transaksi : <?=h($rs['MerchandiseOrder']['po_number'])?></h1>
						
					</div>
					<div class="tr widgets">
						<?php
							$shopping_cart = unserialize($rs['MerchandiseOrder']['data']);
						?>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="theTable footable">
							<thead>
                                <tr>
                                   
                                    <th>Item</th>
                                    <th>Kota Tujuan</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ongkos Kirim</td>
                                    <td><?=h($city['city'])?></td>
                                    <td>
                                        Rp. <?=number_format($total_ongkir)?>
                                    </td>
                                    <td>
                                        <a class="button" href="<?=$ecash_url?>">
                                            Bayar dengan ECash Mandiri
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
		                    
		                </table>
 
					</div><!-- end .widget -->
					<div class="tr widgets">
						<p>
							
							<a class="button2" href="<?=$this->Html->url('/merchandises/history')?>">
								<span class="ico icon-undo-2">&nbsp;</span> Kembali ke Daftar Transaksi
							</a>
						</p>
					</div><!-- end .widget -->
				</div><!-- end .col-content -->
				
           
				<div class="box4 fr">
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