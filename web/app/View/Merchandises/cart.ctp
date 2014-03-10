<?php
$pic = Configure::read('avatar_web_url').
				"merchandise/thumbs/0_".
				$item['pic'];
?>
<script>
var base_price = {};
var base_coin = {};
</script>
<div id="catalogPage">
      <div class="rowd">
     	 <?php echo $this->element('infobar'); ?>
      </div>
    <div id="thecontent">
        <div class="content">
        	<div class="titlePage">
				<h1 class="red">Keranjang Belanja</h1>
				<h4></h4>
            </div>
            <div class="rowd">
				<div class="col-content">
					<div class="tr widget">
			<form 
                id="frm" method="post" 
                enctype="application/x-www-form-urlencoded"
				action="<?=$this->Html->url('/merchandises/cart')?>">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
                    <tr>
                       
                        <td>Item</td>
                        <td>Harga Satuan</td>
                        <td>Jumlah</td>
                        <td>Total</td>
                        <td>Hapus</td>
                    </tr>
					<?php
					for($i=0;$i<sizeof($shopping_cart);$i++):
                        $item = $shopping_cart[$i]['data']['MerchandiseItem'];
                    ?>
                    <tr class="tr-<?=intval($item['id'])?>">
                        
                        <td> #<?=h($item['id'])?> -
                            <?=h($item['name'])?>
                        </td>
                        <td>
                            <?php if($item['price_money']>0):?>
                            <p class="price">   
                                Rp. <?=number_format(intval($item['price_money']))?> 
                            </p> 
                            <?php endif;?>
                            <?php if($item['price_credit']>0):?>
                            <p class="price">   
                                <?php if($item['price_money'] > 0):?>
                                    (<?=number_format(intval($item['price_credit']))?> Coins)
                                <?php else:?>
                                    <?=number_format(intval($item['price_credit']))?> Coins
                                <?php endif;?>

                            </p> 
                            <?php endif;?>
                            
                        </td>
                        <td>
                            <input type="hidden" name="item_id[]"  
                                value="<?=intval($item['id'])?>"

                            />
                            <?php if($item['merchandise_type']==0):?>
                            <input style="width:30px;" type="text" name="qty[]" class="qty" data-id="<?=intval($item['id'])?>" 
                                value="<?=intval($shopping_cart[$i]['qty'])?>"/>
                            <?php else: ?>
                            <input style="width:30px;" type="text" name="qty[]" class="qty" data-id="<?=intval($item['id'])?>" 
                                value="1" readonly="readonly"/>
                            <?php endif;?>
                        </td>
                        <td>
                            Rp. <span class="prices price-<?=$item['id']?>">
                                <?=number_format(intval($item['price_money']))?>
                            </span>
                            <?php if($item['price_credit']>0):?>
                                <?php if($item['price_money']>0):?>
                                    (<span class="coins coin-<?=$item['id']?>">
                                        <?=number_format(intval($item['price_credit']))?>
                                    </span> Coins)
                                <?php else:?>
                                    <span class="coins coin-<?=$item['id']?>">
                                        <?=number_format(intval($item['price_credit']))?>
                                    </span> Coins
                                <?php endif;?>
                            <?php endif;?>
                            <script>
                                 base_price['<?=$item['id']?>'] = <?=intval($item['price_money'])?>;
                                 base_coin['<?=$item['id']?>'] = <?=intval($item['price_credit'])?>;
                            </script>
                        </td>
                        <td>
                            <a class="btnDelete button" href="javascript:;" data-id="<?=intval($item['id'])?>">Hapus</a>
                        </td>
                    </tr>
                    <?php endfor;?>
                    <tr>
                        <td colspan="4">Belanja Total</td>
                        <td colspan="2">
                            <span class="total-price">0</span>
                        </td>
                    </tr>

                </table>
                <input type="hidden" name="update_type" value="0"/>
                <a href="<?=$this->Html->url('/merchandises')?>" class="button">Kembali Belanja</a>
                <a href="javascript:;" id="btnUpdate"class="button">Update Keranjang Belanja</a>
                <a href="javascript:;" id="btnCheckout"class="button">Checkout</a>
               
                </form>
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
function cancel(){
	document.location="<?=$this->Html->url('/merchandises')?>";
}
function updateCart(){
    $("input[name=update_type]").val(0);
    $("#frm").submit();

}
function checkout(){
    $("input[name=update_type]").val(1);
    $("#frm").submit();
}
function totalCost(callback){
    var total_coins = 0;
    var total_price = 0;
    var n_total = $(".qty").length;
    $(".qty").each(function(i,item){
        var id = $(item).attr('data-id');
        total_coins += parseInt(base_coin[id]) * parseInt($(item).val());
        total_price += parseInt(base_price[id]) * parseInt($(item).val());
        if(parseInt(i) == (n_total - 1)){
            callback(total_price,total_coins);
        }
    });
}

$(".qty").on('keyup',function(e){
    updateCost();
});

$("#btnUpdate").on('click',function(e){
    updateCart();
});
$("#btnCheckout").on('click',function(e){
    checkout();
});
$(".btnDelete").on('click',function(e){
    var id = $(this).attr('data-id');
    $(".tr-"+id).remove();
});
$(document).ready(function(){
    updateCost();
});

function updateCost(){
    totalCost(function(total_price,total_coins){
        console.log(total_price,total_coins);
        var str = "";
        if(total_price > 0){
            str += "Rp. " + number_format(total_price);
        }
        if(total_coins >0){
            if(total_price > 0){
                str += " ("+number_format(total_coins) + " Coins)";
            }else{
                str += number_format(total_coins) + " Coins";    
            }
            
        }
        console.log('->',str);
        $(".total-price").html(str);
    });
}

</script>