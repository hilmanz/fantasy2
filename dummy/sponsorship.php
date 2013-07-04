<?php
/**
 * Sponsorship Window Example
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";


//cache the team info
$response = curlGet($api_url.'/team/get/'.$fb_id.'?access_token='.$access_token);
$o = json_decode($response,true);
if(!$o['error']){
	$team = $o;
	$_SESSION['team'] = $team;
}



if($_REQUEST['apply']==1){
	$response = curlPost($api_url.'/sponsorship/apply',array(
							'team_id'=>$_SESSION['team']['id'],
							'sponsor_id'=>$_REQUEST['sponsor_id'],
							'access_token'=>$access_token
						));
	$o = json_decode($response,true);
	
	if($o['status']==1){
		print "You win the sponsorship !";
	}else{
		print "Your sponsorship request has been declined !";
	}
}


$response = curlGet($api_url.'/team/sponsors/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$o = json_decode($response,true);

if($o['status']==1){
$current_sponsors = $o['sponsors'];
}

$response = curlGet($api_url.'/sponsorship/list/'.$_SESSION['team']['id'].'?access_token='.$access_token);

$o = json_decode($response,true);

if($o['status']==1){
$sponsorships = $o['sponsorships'];
}

?>
<html>
<head><title>Sponsorship Example</title></head>
<body>

<div>
<h3>Active Contract(s)</h3>
<table border="1" width="720">
		<tr><td>Name</td><td>Amount</td><td>Contract Time</td></tr>
		<?php 
			foreach($current_sponsors as $current):
		?>
		<tr><td><?=$current['name']?></td>
			<td>$ <?=number_format($current['value'])?> / Match</td>
			<td> <?=number_format($current['valid_for'])?> Matches</td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
<div>
<h3>Available Sponsorships</h3>

	<table border="1" width="720">
		<tr><td>Name</td><td>Amount</td><td>Contract Time</td><td>Action</td></tr>
		<?php 
			foreach($sponsorships as $sponsors):
		?>
		<tr><td><?=$sponsors['name']?></td>
			<td>$ <?=number_format($sponsors['value'])?> / Match</td>
			<td> <?=number_format($sponsors['expiry_time'])?> Matches</td>
			<td>
			<a href="sponsorship.php?apply=1&sponsor_id=<?=$sponsors['id']?>">Apply</a>
			</td></tr>
		<?php endforeach; ?>
	</table>
</div>

</body>
</html>
