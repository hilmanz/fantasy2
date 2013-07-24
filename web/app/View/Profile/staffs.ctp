<div id="fillDetailsPage">
	
    <div id="thecontent">
        <div id="content">
            <div class="content">
                <div class="row-2">
                    <h1 class="red">Choose Your Staff</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <form class="theForm">
                    <div class="row-2">
                        <div class="col2">
                            <select class="styled">
                                <option>Marketing</option>
                            </select>
                        </div><!-- end .col2 -->
                        <div class="col2">
                            <div class="searchBox">
                                <input type="text" value="Search" />
                                <input type="submit" value="&nbsp;" />
                            </div>
                        </div>
                    </div><!-- end .row-2 -->
                    <div class="row-2 titles">
                        <div class="col2">
                            <h2>Select Staff Member</h2>
                        </div><!-- end .col2 -->
                        <div class="col2">
                            <h2>Selected</h2>
                        </div>
                    </div><!-- end .row-2 -->
                    <div class="row-2">
                        <div class="col2 staff-list">
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="content/thumb/default_avatar.png" />
                                </div><!-- end .avatar-big -->
                                <h3>Marketing Manager</h3>
                            </div><!-- end .thumbStaff -->
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="content/thumb/default_avatar.png" />
                                </div><!-- end .avatar-big -->
                                <h3>Marketing Staff</h3>
                            </div><!-- end .thumbStaff -->
                        </div><!-- end .col2 -->
                        <div class="col2 staff-list">
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="content/thumb/default_avatar.png" />
                                </div><!-- end .avatar-big -->
                                <h3>Marketing Staff</h3>
                            </div><!-- end .thumbStaff -->
                        </div>
                    </div><!-- end .row-2 -->
                    <div class="row-2">
                        <input type="submit" value="Save &amp; Continue" class="button" />
                    </div><!-- end .row-2 -->
                </form>
            </div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
	               <li><a href="details">Fill in Your Detail</a></li>
	               <li><a href="teams">Fill in Your Team</a></li>
	               <li><a href="players">Fill in Your Players</a></li>
	               <li class="current"><a href="staffs">Fill in Your Staff</a></li>
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