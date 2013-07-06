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
                <a id="logo" href="index.php" title="SUPER SOCCER - FANTASY FOOTBALL LEAGUE">&nbsp;</a>
            	
                <div id="user-info">
        			<a href="#" class="thumb40 fl"><img src="content/thumb/1_40.jpg" /></a>
                    <div class="entry fl">
                        <h3 class="username"><a href="#">Jason</a></h3>
                        <span class="points red">1400 Pts</span>
                        <span class="user-exp">Rookie</span>
                    </div><!-- end .entry -->
                </div>
          		
            </div><!-- end #header -->
            
            <div id="navigation">
            	<ul>
                	<li>
                    	<a href="index.php?menu=team">MANAGE TEAM</a>
                    </li>
                	<li>
                    	<a href="index.php?menu=my-club">MY CLUB</a>
                    </li>
                	<li>
                    	<a href="profile">MY PROFILE</a>
                    </li>
                	<li>
                    	<a href="index.php?menu=leaderboard">LEADERBOARD</a>
                    </li>
                	<li>
                    	<a href="index.php?menu=transfer-market">TRANSFER MARKET</a>
                    </li>
                	<li><a href="index.php?menu=faq">HELP & FAQ</a></li>
                </ul>
            </div>
          
            <div id="container">
				<?php echo $this->fetch('content'); ?>
            </div><!-- end #container -->
            <div id="footer">
                <div id="footNav">
                    <ul>
                        <li><a href="index.php?menu=about">ABOUT FANTASY FOOTBALL LEAGUE</a></li>
                        <li><a href="index.php?menu=tos">term & conditions</a></li>
                        <li><a href="index.php?menu=contact">contact</a></li>
                    </ul>
                </div>
            </div>
        </div><!-- end #universal -->
    </div><!-- end #body -->
	<?php echo $this->element('js'); ?>

	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
