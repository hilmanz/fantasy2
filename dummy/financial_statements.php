<?php
/**
 * show financial statements
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://198.199.110.39:3002";
$fb_id = "123123123";
$player_id = "p19557";
$team_id = "296";

//cache the team info
$response = curlGet($api_url.'/team/get/'.$fb_id.'?access_token='.$access_token);
$o = json_decode($response,true);
if(!$o['error']){
	$team = $o;
	$_SESSION['team'] = $team;
}

//get player detail
$response = curlGet($api_url.'/finance/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$finance = json_decode($response,true);


?>
<html>
<head><title>Setup Lineup example</title></head>
<body>

<div>
<h3>Financial Statement</h3>
<?php pr($finance);?>
</div>

</body>
</html>
