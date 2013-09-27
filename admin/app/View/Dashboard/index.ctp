<h3>Welcome to Administration Page</h3>

<div class="row-2">
<h3>News Ticker</h3>

<table width="100%">
<?php 
if(isset($tickers)):
foreach($tickers as $ticker):
?>
<tr>
<td><?=$ticker['Ticker']['content']?></td>
<td><?=$ticker['Ticker']['url']?></td>
<td><a href="<?=$this->Html->url('/dashboard/delete_tickers?id='.$ticker['Ticker']['id'])?>" class="button">Delete</a></td>
</tr>
<?php endforeach;endif;?>
</table>
<form action="<?=$this->Html->url('/dashboard/add_ticker')?>" method="post" enctype="application/x-www-form-urlencoded">
<div>
<input type="text" name='content' value="" placeholder="Type New Info"/>
</div>
<div>
<input type="text" name='url' value="" placeholder="http://"/>
</div>
<div class="row-2">
<input type="submit" name="btnTickerSaved" value="Save Tickers"/>
</div>
</div>
</form>
</div>