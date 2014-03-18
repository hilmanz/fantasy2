<form action="<?=$this->Html->url('/merchandises/edit/'.$rs['MerchandiseItem']['id'])?>" method="post" enctype="multipart/form-data">
	<h3>Edit Merchandise</h3>
	<table width="100%">
		<tr>
			<td valign="top">
				Name
			</td>
			<td>
				<input type="text" name="name" value="<?=$rs['MerchandiseItem']['name']?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Category
			</td>
			<td>
				<select name="merchandise_category_id">
					<?php foreach($categories as $category):
						if($category['MerchandiseCategory']['id'] == $rs['MerchandiseItem']['merchandise_category_id']){
							$opt = ' selected';
						}else{
							$opt = '';
						}
					?>
					<option value="<?=$category['MerchandiseCategory']['id']?>"<?=$opt?>>
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
				<script>
				$("select[name=merchandise_type]").val(<?=$rs['MerchandiseItem']['merchandise_type']?>);
				</script>
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
				<script>
				$("select[name=perk_id]").val(<?=$rs['MerchandiseItem']['perk_id']?>);
				</script>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Description
			</td>
			<td>
				<textarea name='description' cols="100" rows="10"><?=$rs['MerchandiseItem']['description']?></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top">
				In-game price
			</td>
			<td>
				ss$ <input type="text" name="price_currency" value="<?=$rs['MerchandiseItem']['price_currency']?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				FM Credits Value
			</td>
			<td>
				<input type="text" name="price_credit" value="<?=$rs['MerchandiseItem']['price_credit']?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Real price
			</td>
			<td>
				IDR <input type="text" name="price_money" value="<?=$rs['MerchandiseItem']['price_money']?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Stock
			</td>
			<td>
				<h3><?=intval($rs['MerchandiseItem']['stock'])?></h3>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Add New Stock
			</td>
			<td>
				<input type="text" name="new_stock" value="0"/>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php
				$pic = Configure::read('avatar_web_url')."merchandise/thumbs/1_".$rs['MerchandiseItem']['pic'];
				?>
				<img src="<?=$pic?>"/>
				<div>Upload New Picture</div>
			</td>
			<td>
				<input type="file" name="pic"/>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="btn" value="UPDATE"/>
			</td>
		</tr>
	</table>
</form>