<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<?php echo $this->element('meta'); ?>
</head>
<body>
	<div id="fb-root"></div>
	<div id="effect"></div>
   	<div id="flag"></div>
 	<div id="body">
        <div id="universal">
           <div id="header">
               <a id="logo" href="<?=$this->Html->url('/')?>" title="SUPER SOCCER - FANTASY FOOTBALL LEAGUE">&nbsp;</a>
            </div><!-- end #header -->
			<script>
				$(document).ready(function() {
					$('ul#topnav').superfish({ 
						delay:       600,
						animation:   {opacity:'show',height:'show'},
						speed:       'fast',
						autoArrows:  true,
						dropShadows: false
					});
				});
			</script>
            <?php
                if($USER_IS_LOGIN):
            ?>
                <div id="navigation">
                	<ul id="topnav">
                    	<li>
                        	<a href="<?=$this->Html->url('/')?>">Dashboard</a>
                        </li>
                    	<li><a href="#">GAME</a>
							<ul>
								<li>
									<a href="<?=$this->Html->url('/schedule')?>">Schedule</a>
								</li>
                                <li>
                                    <a href="<?=$this->Html->url('/schedule/matchday')?>">Matchday Setup</a>
                                </li>
								<li>
									<a href="<?=$this->Html->url('/players/overall')?>">Players</a>
								</li>
								<li>
									<a href="<?=$this->Html->url('/players/playerstats')?>">Master Player</a>
								</li>
								<li>
									<a href="<?=$this->Html->url('/sponsors')?>">Sponsors</a>
								</li>
                                <li>
                                    <a href="<?=$this->Html->url('/events')?>">Events</a>
                                </li>
                                <li>
                                    <a href="<?=$this->Html->url('/merchandises')?>">Merchandise</a>
                                </li>
							</ul>
                        </li>
                    	<li><a href="#">OPTA</a>
							<ul>
                            	<li><a href="<?=$this->Html->url('/pushlogs')?>">Push Logs</a></li>
                            	<li><a href="<?=$this->Html->url('/matches')?>">Match Results</a></li>
                            	<li><a href="<?=$this->Html->url('/stats')?>">Statistics</a></li>
							</ul>
                        </li>
                        <li>
                            <a href="<?=$this->Html->url('/banners')?>">Banners</a>
                        </li>
                    	<li><a href="<?=$this->Html->url('/login/logout')?>">Logout</a></li>
                    </ul>
                </div>
           
            <?php endif;?>
            <div id="container">
            	<?php echo $this->Session->flash();?>
				<?php echo $this->fetch('content'); ?>
            </div><!-- end #container -->
            <div id="footer">
                <div id="footNav">
                  
                    
                </div>
                
            </div>
        </div><!-- end #universal -->

    </div><!-- end #body -->
	<?php echo $this->element('js'); ?>
    
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
