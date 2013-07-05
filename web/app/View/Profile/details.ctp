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
                <form class="theForm" action="details" method="post">
                    <div class="tr avatarBox">
                        <div class="avatar-big">
                            <img src="content/thumb/default_avatar.png" />
                        </div>
                        <a href="#" class="button">Change Avatar</a>
                    </div>
                    <div class="row">
                        <label>Name</label>
                        <input type="text" name="name" />
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Email</label>
                        <input type="text" name="email" />
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Location</label>
                        <select class="styled" name="city">
                            <option>City</option>
                            <option>Jakarta</option>
                            <option>Bandung</option>
                        </select>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Mobile</label>
                        <input type="text" name="phone_number" />
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Where Did You Hear About FFL?</label>
                        <input type="radio" class="styled" name="hearffl"/><span>Supersoccer</span>
                        <input type="radio" class="styled" name="hearffl"/><span>TV</span>
                        <input type="radio" class="styled" name="hearffl"/><span>Radio</span>
                        <input type="radio" class="styled" name="hearffl"/><span>Facebook</span>
                        <input type="radio" class="styled" name="hearffl"/><span>Twitter</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Would You Like to Recieve Daily Stats via Email?</label>
                        <input type="radio" class="styled" name="daylyemail"/><span>Yes</span>
                        <input type="radio" class="styled" name="daylyemail"/><span>No</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Would You Like to Recieve Daily Stats via SMS*?</label>
                        <input type="radio" class="styled" name="daylysms"/><span>Yes</span>
                        <input type="radio" class="styled" name="daylysms"//><span>No</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Is this Your First Time Playing a Fantasy League?</label>
                        <input type="radio" class="styled" name="firstime"/><span>Yes</span>
                        <input type="radio" class="styled" name="firstime"/><span>No</span>
                    </div><!-- end .row -->
                    <div class="row">
                        <input type="hidden" name="save" value="1"/>
                        <input type="submit" value="Save &amp; Continue" class="button" />
                    </div><!-- end .row -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li class="current"><a href="details">Fill in Your Detail</a></li>
	               <li><a href="teams">Fill in Your Team</a></li>
	               <li><a href="players">Fill in Your Players</a></li>
	               <li><a href="staffs">Fill in Your Staff</a></li>
	               <li><a href="clubs">Fill in Your Club</a></li>
	               <li><a href="formations">Set Your First Formation</a></li>
	               <li><a href="invite_friends">Invite Friends</a></li>
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>EUR <?=number_format($team_bugdet)?></h1>
	            <h3 class="red">Est. Monthly Expenses</h3>
	            <h1>EUR 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->