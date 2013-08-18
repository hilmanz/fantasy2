<div id="info-bar" class="tr2">
    <h4 class="date-now fl"><?=date("d/m/Y")?></h4>
    <div id="newsticker">
          <ul class="slides">
          	<?php 
          	if(isset($tickers)):
          		foreach($tickers as $ticker):
          	?>
            <li class="newsticker-entry">
                <h3><a href="#n1"><?=__($ticker['Ticker']['content'])?></a></h3>
            </li><!-- end .newsticker-entry -->
            
            <?php endforeach;endif;?>
          </ul><!-- end #newsticker -->
    </div>
    <h4 class="fr countdown"><span class="yellow ctday">6</span> HARI  <span class="yellow cthour">0</span> JAM  <span class="yellow ctminute">0</span> MENIT ke penutupan</h4>
</div><!-- end #info-bar -->
<script>
var match_date_ts = <?=$match_date_ts-(24*60*60)?>;
function countdown(){
	var date = new Date();
	var td = date.getTime();
	var d = (match_date_ts*1000) - td;
	//get days
	var days = Math.floor((d/1000)/(24*60*60));
	var d0 = (d/1000)/(24*60*60);

	//get hours
	var d1 = d0-days;
	var h0 = d1*24;
	var hour = Math.floor(h0);
	

	//get minutes
	var h1 = h0 - hour;
	
	var m0 = h1 * 60;
	var minute = Math.round(m0);
	

	if(days >= 0){
		$('.ctday').html(days);
		$('.cthour').html(hour);
		$('.ctminute').html(minute);
		$('.countdown').show();
	}else{
		$('.countdown').hide();
	}
	setTimeout(function(){
		countdown();
	}, 30000);
}
countdown();
</script>