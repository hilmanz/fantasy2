<h3>
	Game Perks
</h3>
<div class="row">
<a href="<?=$this->Html->url('/perks/create')?>" class="button">
	Create New Perk
</a>
</div>
<div class="row">
	<form action="<?=$this->Html->url('/perks/view/'.$perk['MasterPerk']['id'])?>" 
			method="post" 
			enctype="application/x-www-form-urlencoded">
		<table width="100%" class="table">
			<tr>
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
					<script>
					$("select[name=perk_name]").val('<?=$perk['MasterPerk']['perk_name']?>');
					</script>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Name
				</td>
				<td>
					<input type="text" name="name" value="<?=$perk['MasterPerk']['name']?>"/>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Description
				</td>
				<td>
					<textarea name="description"><?=$perk['MasterPerk']['description']?></textarea>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Amount
				</td>
				<td valign="top">
					<input type="text" name="amount" value="<?=$perk['MasterPerk']['amount']?>"/>
				</td>
			</tr>
			<tr>
				<td valign="top">
					Attributes
				</td>
				<td>
					<?php
						$attributes = unserialize($perk['MasterPerk']['data']);
					?>
					<?php foreach($attributes as $name=>$val):?>
					<div>
					<input type="text" name="attributes[]" value="<?=$name?>" placeholder="name" style="width:300px;"/> &nbsp;
					<input type="text" name="attribute_values[]" value="<?=$val?>" placeholder="value" style="width:300px;"/>
					</div>
					<?php endforeach;?>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<input type="submit" name="btn" value="Save"/>
				</td>
			</tr>
		</table>
	</form>
</div>
<?php if(isset($data)):?>
<?php echo $this->Paginator->numbers();?>
<?php endif;?>