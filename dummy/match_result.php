<?php
/**
 * setup a lineup example
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";


$response = curlGet($api_url.'/match/results/'.$_REQUEST['game_id'].'?access_token='.$access_token);
$o = json_decode($response,true);



?>
<html>
<head><title>Match Results Example</title></head>
<body>
<a href="match_list.php">Back to Fixtures</a>
<div>
<?php if($o['status']==1):?>
<h3>Match Results</h3>
	<table border="1" width="720">
		<tr>
			<td><?=$o['data'][0]['name']?></td>
			<td><?=$o['data'][0]['score']?> - <?=$o['data'][1]['score']?></td>
			<td><?=$o['data'][1]['name']?></td>
		</tr>
		<tr>
			<td valign="top">
				<table width="100%">
					<?php foreach($o['data'][0]['overall_stats'] as $stats=>$val):?>
					<tr>
						<td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
					</tr>
					<?php endforeach;?>
				</table>
				<table width="100%">
					<?php foreach($o['data'][0]['player_stats'] as $player=>$data):?>
					<tr style="background-color:#353535;color:white;padding:5px">
						<td><?=$data['name']?></td><td><?=$data['position']?></td>
					</tr>
						<?php
							foreach($data['stats'] as $stats=>$val):
						?>
						<tr>
							<td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
						</tr>
						<?php endforeach;?>
					<?php endforeach;?>
				</table>
			</td>
			<td>
				
			</td>
			<td valign="top">
				<table width="100%">
					<?php foreach($o['data'][1]['overall_stats'] as $stats=>$val):?>
					<tr>
						<td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
					</tr>
					<?php endforeach;?>
				</table>
				<table width="100%">
					
					<?php foreach($o['data'][1]['player_stats'] as $player=>$data):?>
					<tr style="background-color:#353535;color:white;padding:5px">
						<td><?=$data['name']?></td><td><?=$data['position']?></td>
					</tr>
						<?php
							foreach($data['stats'] as $stats=>$val):
						?>
						<tr>
							<td><?=ucfirst(str_replace("_"," ",$stats))?></td><td><?=$val?></td>
						</tr>
						<?php endforeach;?>
					<?php endforeach;?>
				</table>
			</td>
		</tr>
	</table>
</div>
<?php else:?>
No game found.
<?php endif;?>

</body>
</html>
