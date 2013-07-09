<div id="fillDetailsPage">
	<div id="info-bar" class="tr2">
	    <h4 class="date-now fl">14 june 2013</h4>
	    <div id="newsticker">
	          <ul class="slides">
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Lorem ipsum FC VS Dolor</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">2 Goals Sit amet, consectetuer</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Sdipiscing elit VS Rincidunt Team 3-0,</a></h3>
	            </li><!-- end .newsticker-entry -->
	            <li class="newsticker-entry">
	                <h3><a href="#n1">Sed diam nonummy nibh euismod tincidunt ut</a></h3>
	            </li><!-- end .newsticker-entry -->
	          </ul><!-- end #newsticker -->
	    </div>
	    <h4 class="fr"><span class="yellow">6</span> DAYS <span class="yellow">0</span> HOUR <span class="yellow">0</span> MINUTE to close</h4>
	</div><!-- end #info-bar -->
    <div id="thecontent">
        <div id="content">
        	<div class="content">
            	<div class="row-2">
                    <h1 class="red">Fill in Your Details</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
    			</div><!-- end .row-2 -->
                <form class="theForm" action="<?=$this->Html->url('/profile/register')?>" method="post" enctype="multipart/form-data">
                    
                    <div class="row">
                        <label>Name</label>
                        <input type="text" name="name" value="<?=h($user['name'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Email</label>
                        <input type="text" name="email" value="<?=h(@$user['username'])?>@facebook.com"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Location</label>
                        <select class="styled" name="city">
                            <option>City</option>
                            <option value="Jakarta" selected="selected">Jakarta</option>
                            <option value="Bandung">Bandung</option>
                        </select>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Mobile</label>
                        <input type="text" name="phone_number" />
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Where Did You Hear About FFL?</label>
                        <input type="radio" class="styled" name="hearffl" checked="checked" value="1"/><span>Supersoccer</span>
                        <input type="radio" class="styled" name="hearffl" value="2"/><span>TV</span>
                        <input type="radio" class="styled" name="hearffl" value="3"/><span>Radio</span>
                        <input type="radio" class="styled" name="hearffl" value="4"/><span>Facebook</span>
                        <input type="radio" class="styled" name="hearffl" value="5"/><span>Twitter</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Would You Like to Recieve Daily Stats via Email?</label>
                        <input type="radio" class="styled" name="daylyemail" checked="checked" value="1"/><span>Yes</span>
                        <input type="radio" class="styled" name="daylyemail" value="0"/><span>No</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Would You Like to Recieve Daily Stats via SMS*?</label>
                        <input type="radio" class="styled" name="daylysms" checked="checked" value="1"/><span>Yes</span>
                        <input type="radio" class="styled" name="daylysms" value="0"/><span>No</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Is this Your First Time Playing a Fantasy League?</label>
                        <input type="radio" class="styled" name="firstime" checked="checked" value="1"/><span>Yes</span>
                        <input type="radio" class="styled" name="firstime" value="0"/><span>No</span>
                    </div><!-- end .row -->
                    <div class="row">
                        <input type="hidden" name="save" value="1"/>
                        <input type="hidden" name="step" value="1"/>
                        <input type="submit" value="Continue" class="button" />
                    </div><!-- end .row -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li class="current"><a href="#/detail">Fill in Your Detail</a></li>
	               <li><a href="#/team">Fill in Your Team</a></li>
	               <li><a href="#/player">Fill in Your Players</a></li>
	               <li><a href="#/staff">Fill in Your Staff</a></li>
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>EUR <?=@number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. Monthly Expenses</h3>
	            <h1>EUR 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->