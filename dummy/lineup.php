<?php
/**
 * setup a lineup example
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$fb_id = "123123123";


if($_REQUEST['set_lineup']==1):

pr($_REQUEST);
$players = array();
foreach($_REQUEST as $n=>$v){
	if(eregi('player-',$n)&&$v!=0){
		$players[] = array('player_id'=>str_replace('player-','',$n),'no'=>intval($v));
	}
}
$s_players = json_encode($players);
$response = curlPost($api_url.'/team/lineup/save',array(
						'team_id'=>$_SESSION['team']['id'],
						'players'=>$s_players,
						'formation'=>$_REQUEST['formation'],
						'access_token'=>$access_token
					));
$o = json_decode($response,true);
pr($o);
if($o['status']==1){
	print "lineup saved !<br/>";
}else{
	print "error saving the lineup :(<br/>";
}
endif;


//cache the team info
$response = curlGet($api_url.'/team/get/'.$fb_id.'?access_token='.$access_token);
$o = json_decode($response,true);
if(!$o['error']){
	$team = $o;
	$_SESSION['team'] = $team;
}

//get current lineups
$response = curlGet($api_url.'/team/lineup/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$current_lineups = json_decode($response,true);
pr($current_lineups);
//get player list
$response = curlGet($api_url.'/team/list/'.$_SESSION['team']['id'].'?access_token='.$access_token);
$player_list = json_decode($response,true);

?>
<html>
<head><title>Setup Lineup example</title></head>
<body>
<?php
if(sizeof($current_lineups)>0):
?>
<div>
<h3>Current Lineup</h3>
<div>
	<?php 
		foreach($current_lineups as $lineup):
	?>
	<div>
		<span style="padding:5px;"><?=$lineup['name']?></span>
		<span style="padding:5px">( <?=$lineup['position']?> )</span>
	</div>
	<?php endforeach; ?>
</div>
</div>
<?php endif;?>
<div>
	<form method="post" enctype="application/x-www-form-urlencoded">
	<h3>Choose Formation</h3>
	<div>
		<select name="formation">
			<option value="4-4-2">4-4-2</option>
			<option value="4-4-1-1">4-4-1-1</option>
			<option value="4-3-3">4-3-3</option>
			<option value="4-3-2-1">4-3-2-1</option>
			<option value="4-3-1-2">4-3-1-2</option>
			<option value="5-3-2">5-3-2</option>
			<option value="5-3-1-1">5-3-1-1</option>
			<option value="5-2-2-1">5-2-2-1</option>
			<option value="4-2-4">4-2-4</option>
			<option value="3-4-3">3-4-3</option>
			<option value="3-4-2-1">3-4-2-1</option>
		</select>
	</div>
	<h3>Choose Player</h3>
	
		<table>
			<tr>
				<td>Name</td><td>Role</td><td>Position</td>
			</tr>
			<?php foreach($player_list as $player):?>
			<tr>
				<td><?=$player['name']?></td><td><?=$player['position']?></td>
				<td><select name="player-<?=$player['uid']?>">
				<option value="0">--</option>
				<?php for($i=0;$i<11;$i++):?>
				<option value="<?=($i+1)?>"><?=($i+1)?></option>
				<?php endfor;?>
				</select>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
		<input type="hidden" name="set_lineup" value="1"/>
		<input type="submit" name="set" value="Set Lineup"/>
	</form>
</div>

</body>
</html>
