<div class="titleBox">
	<h1>Create Sponsor</h1>
</div>
<div class="row">
<form action="<?=$this->Html->url('/sponsors/create')?>" method="POST" enctype="application/x-www-form-urlencoded">
	<table width="100%">
		<tr>
			<td>Name</td><td><input type="text" name="name" value=""></td>
		</tr>
		<tr>
			<td>Expiry Time</td><td><input type="text" name="expiry_time" value="38"></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="btn" value="Save"/></td>
		</tr>
	</table>
</form>
</div>