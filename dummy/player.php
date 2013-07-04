<?php
/**
 * show player info (master data)
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";
$player_id = "p19557";


//cache the team info
$response = curlGet($api_url.'/team/get/'.$fb_id.'?access_token='.$access_token);
$o = json_decode($response,true);
if(!$o['error']){
	$team = $o;
	$_SESSION['team'] = $team;
}

//get player detail
$response = curlGet($api_url.'/player/'.$player_id.'?access_token='.$access_token);
$player_detail = json_decode($response,true);



?>
<html>
<head><title>Player Master Detail Example</title></head>
<body>

<div>
<h3>Player Info</h3>
<table width="700" border="1" cellpadding="10">
	<tr>
		<td>Name</td><td><?=$player_detail['data']['player']['name']?></td>
	</tr>
	<tr><td>Age</td><td><?=floor((time()-intval(strtotime($player_detail['data']['player']['birth_date'])))/(365*24*60*60))?></td></tr>
	<tr><td>Position</td><td><?=$player_detail['data']['player']['position']?></td></tr>
	<tr><td>Nationality</td><td><?=$player_detail['data']['player']['country']?></td></tr>
	<tr><td>Club</td><td><?=$player_detail['data']['player']['original_team_name']?></td></tr>
	</tr>
</table>
<h3>Performance</h3>
<table width="700" border="1" cellpadding="10">
	<tr>
		<td>Match Day</td><td>Points</td><td>Overall Performance</td>
	</tr>
	<?php
	if(is_array($player_detail['data']['stats'])):
		foreach($player_detail['data']['stats'] as $stats):
	?>
	<tr>
		<td><?=$stats['matchday']?></td>
		<td><?=$stats['points']?></td>
		<td><?=$stats['performance']?></td>
	</tr>
	<?php
		endforeach;
	endif;
	?>
	</tr>
</table>
</div>

</body>
</html>
