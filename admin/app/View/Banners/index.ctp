<h3>Ads Banners</h3>
<table width="100%">
<tr>
<td>ID</td><td>Name</td><td>File</td><td>URL</td><td>Action</td>
</tr>
<?php
if(isset($results)):
	foreach($results as $rs):
?>
<tr>
<td>ID</td>
<td><?=h($rs['Banners']['banner_name'])?></td>
<td><img src='<?=$this->Html->url(Configure::read('avatar_web_url').$rs['Banners']['banner_file'])?>' width="400"/></td>
<td><?=h($rs['Banners']['url'])?></td>
<td><a href="<?=$this->Html->url('/banners/remove/?id='.$rs['Banners']['id'])?>" class="button">Remove</a></td>
</tr>
<?php endforeach;endif;?>
</table>


<h3>Upload new Banner</h3>
<form action="<?=$this->Html->url('/banners/upload')?>" method="post" enctype="multipart/form-data">
<table width="400">
	<tr>
		<td>File</td><td><input type="file" name="file"/></td>
	</tr>
	<tr>
		<td>Name</td><td><input type="text" name="name"/></td>
	</tr>
	<tr>
		<td>URL</td><td><input type="text" name="url" placeholder="http://"/></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="float:left;width:300px;">File Max : 1 MB</span><input type="submit" name="btn" value="Upload" style="float:right"/></td>
	</tr>
</table>
</form>
