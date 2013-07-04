<?php
/**
 * official hiring window example
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

$budget = 0;
$response = curlGet($api_url.'/team/budget/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$o = json_decode($response,true);

if(!$o['error']){
	$budget = $o['budget'];
}

//HIRE A STAFF
if($_POST['hire']==1){
		$response = curlPost($api_url.'/official/hire',array(
			'team_id'=>intval($_POST['team_id']),
			'official_id'=>intval($_POST['official_id']),
			'access_token'=>$access_token
		));
		print $response;
		die();
}

//FIRE A STAFF
if($_POST['fire']==1){
		$response = curlPost($api_url.'/official/fire',array(
			'team_id'=>intval($_POST['team_id']),
			'official_id'=>intval($_POST['official_id']),
			'access_token'=>$access_token
		));
		print $response;
		die();
}


$response = curlGet($api_url.'/official/list/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$o = json_decode($response,true);
if($o['status']==1){
$officials = $o['officials'];
}

?>
<html>
<head><title>Match Fixtures Example</title></head>
<body>

<div>
<h3>Official for Hire</h3>
<div>Current Balance: $ <?=number_format($budget)?></div>
	<table border="1" width="720">
		<tr><td>Name</td><td>Bonuses</td><td>Salary</td><td>Action</td></tr>
		<?php 
			foreach($officials as $official):
		?>
		<tr><td><?=$official['name']?></td>
			<td>
				<table border="1">
					<tr>
						<td>Sponsorship Bonus</td><td>Income from Attendance</td><td>Operational Cost Bonus</td><td>Transfer Bonus</td>
					</tr>
					<tr>
						<td><?=$official['sponsor_bonus']*100?>%</td>
						<td><?=$official['attendance_bonus']*100?>%</td>
						<td><?=$official['op_cost_bonus']*100?>%</td>
						<td><?=$official['transfer_bonus']*100?>%</td>
					</tr>
				</table>
			</td>
			
			<td>$ <?=number_format($official['salary'])?> / Week</td>
			<td>
			<?php
			if($official['hired']):
			?>
			Hired (<a href="javascript:fire_staff(<?=$official['id']?>)"">Fire</a>)
			<?php else: ?>
				<a href="javascript:hire_staff(<?=$official['id']?>)">Hire</a>
			<?php endif;?>
			</td></tr>
		<?php endforeach; ?>
	</table>
</div>
<script src="js/jquery.js"></script>
<script>
function hire_staff(id){
	var tId = <?=intval($_SESSION['team']['id'])?>;
	var params = {
			team_id: tId,
			official_id:id,
			hire:1
	};
	$.ajax({
		  url: 'official_hiring_window.php',
		  dataType: 'json',
		  data:params,
		  type:'POST',
		  success: function( data ) {
		  	if(data){
				console.log(data);
				document.location.reload();
			}
		  }
		});
}
function fire_staff(id){
	var tId = <?=intval($_SESSION['team']['id'])?>;
	var params = {
			team_id: tId,
			official_id:id,
			fire:1
	};
	$.ajax({
		  url: 'official_hiring_window.php',
		  dataType: 'json',
		  data:params,
		  type:'POST',
		  success: function( data ) {
		  	if(data){
				console.log(data);
				document.location.reload();
			}
		  }
		});
}
</script>
</body>
</html>
