<div class="row">
	<a href="<?=$this->Html->url('/merchandises')?>" class="button">Catalog List</a>
</div>
<form action="<?=$this->Html->url('/merchandises/create')?>" method="post" enctype="multipart/form-data">
	<h3>Add Merchandise</h3>
	<table width="100%">
		<tr>
			<td valign="top">
				Name
			</td>
			<td>
				<input type="text" name="name" value=""/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Category
			</td>
			<td>
				<select name="merchandise_category_id">
					<?php foreach($categories as $category):?>
					<option value="<?=$category['MerchandiseCategory']['id']?>">
						<?=h($category['MerchandiseCategory']['name'])?>
					</option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Item Type
			</td>
			<td>
				<select name="merchandise_type">
					<option value="0">
						Non-Digital Item
					</option>
					<option value="1">
						Digital In-Game Item
					</option>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Perks
			</td>
			<td>
				<select name="perk_id">
					<option value="0">
						N/A
					</option>
					<?php foreach($perks as $perk):?>
					<option value="<?=$perk['MasterPerk']['id']?>">
						<?=h($perk['MasterPerk']['id'])?> - <?=h($perk['MasterPerk']['perk_name'])?> - <?=h($perk['MasterPerk']['name'])?>
					</option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Description
			</td>
			<td>
				<textarea name='description' cols="100" rows="10"></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top">
				In-game price
			</td>
			<td>
				ss$ <input type="text" name="price_currency" value="0"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				FM Credits Value
			</td>
			<td>
				<input type="text" name="price_credit" value="0"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Real price
			</td>
			<td>
				IDR <input type="text" name="price_money" value="0"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Stock
			</td>
			<td>
				<input type="text" name="stock" value="0"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Picture
			</td>
			<td>
				<input type="file" name="pic"/>
			</td>
		</tr>
		<tr>
			<td valign="top" colspan="2">
				<h3>Create Perk <input type="checkbox" value="1" name="chx"/></h3>
			</td>
			
		</tr>
		<tr class="attributes">
			<td>
				Category
			</td>
			<td valign="top">
				<select name="perk_name">
					<option value="ACCESSORIES">ACCESSORIES</option>
					<option value="IMMEDIATE_MONEY">IMMEDIATE MONEY</option>
					<option value="EXTRA_POINTS_PERCENTAGE">EXTRA POINTS PERCENTAGE</option>
					<option value="EXTRA_POINTS_VALUE">EXTRA POINTS VALUE</option>
					<option value="INCOME_MODIFIER">INCOME MODIFIER</option>
					<option value="FREE_PLAYER">FREE PLAYER</option>
					<option value="TRANSFER_DISCOUNT">TRANSFER DISCOUNT</option>
					<option value="POINTS_MODIFIER_PER_CATEGORY">POINTS MODIFIER PER_CATEGORY</option>
				</select>
			</td>
		</tr>
		<tr class="attributes">
			<td valign="top">
				Name
			</td>
			<td>
				<input type="text" name="name" value=""/>
			</td>
		</tr>
		<tr class="attributes">
			<td valign="top">
				Description
			</td>
			<td>
				<textarea name="description"></textarea>
			</td>
		</tr>
		<tr class="attributes">
			<td valign="top">
				Amount
			</td>
			<td valign="top">
				<input type="text" name="amount" value="1"/>
			</td>
		</tr>
		<tr class="attributes">
			<td valign="top">
				Attributes
			</td>
			<td>
				<div>
				<input type="text" name="attributes[]" value="type" placeholder="name" style="width:300px;" 
				class="txt-attr"/> &nbsp;
				
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				</div>
				<div>
				<input type="text" name="attributes[]" value="category" placeholder="name" style="width:300px;"
				class="txt-attr"/> &nbsp;
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				
				</div>
				<div>
				<input type="text" name="attributes[]" value="point_percentage" placeholder="name" style="width:300px;"
				class="txt-attr"/> &nbsp;
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				</div>
				<div>
				<input type="text" name="attributes[]" value="point_value" placeholder="name" style="width:300px;"
				class="txt-attr"/> &nbsp;
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				</div>
				<div>
				<input type="text" name="attributes[]" value="duration" placeholder="name" style="width:300px;"
				class="txt-attr"/> &nbsp;
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				</div>
				<div>
				<input type="text" name="attributes[]" value="" placeholder="name" style="width:300px;"
				class="txt-attr"/> &nbsp;
				<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				
				<input type="submit" name="btn" value="UPLOAD"/>
			</td>
		</tr>
	</table>
</form>
<script>
var options = {
	'type':[
		'booster','jersey'
	],
	'category':[
		'passing_and_attacking','defending','goalkeeping','mistakes_and_errors'
	]
};
$(document).ready(function(e){
	$(".attributes").hide();
	$(".txt-attr").keyup(function(e){
		addValueField($(this).val(),this);
	});
	$("input[name=chx]").change(function(e){
		console.log($(this).is(':checked'));
		if($(this).is(':checked')){
			$('.attributes').show();
		}else{
			$('.attributes').hide();
		}
	});
});
function addValueField(name,obj){
	console.log(name);
	$(obj).next().remove();
	if(typeof options[name] !== 'undefined'){
		var el = options[name];
		var str = "<select name='attribute_values[]'>";
		for(var i in el){
			str += "<option value='"+el[i]+"'>"+el[i]+"</option>";
		}
		str += "</select>";
		$(obj).after("&nbsp;"+str);
	}else{
		$(obj).after('&nbsp;<input type="text" name="attribute_values[]" value="" placeholder="value" style="width:300px;"/>');
	}
}
</script>