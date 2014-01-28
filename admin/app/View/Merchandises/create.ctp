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
			<td colspan="2">
				
				<input type="submit" name="btn" value="UPLOAD"/>
			</td>
		</tr>
	</table>
</form>