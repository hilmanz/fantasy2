<?php
/**
 * register a user example.
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";
?>
<html>
<head><title>Create Team Demo</title></head>
<body>
<?php
if($_REQUEST['select_team']):
?>
	<?php
	$response = curlGet($api_url.'/teams/'.$_REQUEST['uid'].'?access_token='.$access_token);
	$team = json_decode($response,true);
	$response = curlGet($api_url.'/players/'.$_REQUEST['uid'].'?access_token='.$access_token);
	$players = json_decode($response,true);
	?>
	<h3>Team : <?=$team['name']?></h3>
	<h4>Choose your players</h4>
	<div>
	<form action="create_team.php" method="post" enctype="application/x-www-form-urlencoded">
	<?php 
	foreach($players as $player):

	?>
		<div>
			
			<input type="checkbox" name="players[]" value="<?=$player['uid']?>"/>
			<?=$player['name']?>  (<?=$player['position']?>)
		</div>
	<?php endforeach;?>
	<input type="hidden" name="team_id" value="<?=$team['uid']?>"/>
	<input type="hidden" name="fb_id" value="<?=$fb_id?>"/>
	<input type="hidden" name="create_team" value="1"/>
	<input type="submit" name="btn" value="CREATE TEAM"/>
	</form>
	</div>
<?php 
elseif($_REQUEST['create_team']):
	$data = array(
		'team_id'=>$_REQUEST['team_id'],
		'fb_id'=>$_REQUEST['fb_id'],
		'access_token'=>$access_token
	);
	
	foreach($_POST['players'] as $p){
			$data['players'] = json_encode($_POST['players']);
	}
	$response = curlPost($api_url.'/create_team',$data);
	$o = json_decode($response,true);
	if($o['status']==1){
		print "Your team has been created successfully !<br/>";
	}else{
		print "Cannot create your team, please try again later !<br/>";
	}
?>

<?php
//team listing
else:?>
	<h3>Pilih Team</h3>
	<div>
	<?php
	$response = curlGet($api_url.'/teams?access_token='.$access_token);
	$o = json_decode($response,true);
	for($i=0;$i < sizeof($o);$i++):
	?>
		<a href="?select_team=1&uid=<?=$o[$i]['uid']?>"><?=$o[$i]['name']?></a></br/>
	<?php endfor;?>
	</div>
<?php
endif;
?>
</body>
</html>
