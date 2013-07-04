<?php
/**
 * setup a lineup example
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";


$response = curlGet($api_url.'/match/list?access_token='.$access_token);
$o = json_decode($response,true);
if($o['status']==1){
$matches = $o['matches'];
}


?>
<html>
<head><title>Match Fixtures Example</title></head>
<body>

<div>
<h3>Match Fixtures</h3>
	<table border="1" width="720">
		<tr><td>Day</td><td>Home</td><td>Score</td><td>Away</td><td>Attendance</td><td>Period</td><td>Match Result</td></tr>
		<?php 
			foreach($matches as $match):
		?>
		<tr><td><?=$match['matchday']?></td>
			<td><?=$match['home_name']?></td>
			<?php if($match['period']=='PreMatch'):?>
			<td>? - ?</td>
			<?php else:?>
			<td><?=intval($match['home_score'])?> - <?=intval($match['away_score'])?></td>
			<?php endif;?>
			<td><?=$match['away_name']?></td>
			<td><?=number_format($match['attendance'])?></td>
			<td><?=$match['period']?></td>
			<td><a href="match_result.php?game_id=<?=$match['game_id']?>">View</a></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>


</body>
</html>
