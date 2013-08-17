<div id="fillPlayersPage">
	
    <div id="thecontent">
        <div id="content">
        	<div class="content">
            	<div class="row-2">
                    <div class="col2">
                        <h1 class="red lh50">Jajaran Pemain Starter dan Pemain Cadangan </h1>
                    </div>
                    <div class="col2">
						  <img class="teamlogo" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$original['uid'])?>.png"/>
                          
                          <h3 class="teamname"> <?=htmlspecialchars(stripslashes($selected_team['team_name']))?></h3>
                    </div>
    			</div><!-- end .row-2 -->
                <form class="theForm" method="post" action="<?=$this->Html->url('/profile/create_team')?>" enctype="multipart/form-data">
                    
                    <div class="row-2">
						<div class="playerlistbox last">
							<div class="titles">
								<h2>Pemain-Pemain Anda</h2>
							</div><!-- end .titles -->
                       		 <!-- available players -->
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<thead>
							  <tr>
								<th>Nama</th>
								<th>Posisi</th>
							  </tr>
							 </thead>
							 <tbody  id="available">
							 </tbody>
							</table>
                        </div>
						
                    </div><!-- end .row-2 -->
                    
                    <div class="row-2">
                        <input type="hidden" name="players" value="">
                        <a class="button fl" href="javascript:void();" onclick="create_team();return false;">Simpan & Lanjutkan</a>
                        <a class="button fr" href="<?=$this->Html->url('/profile/register_team')?>">Pilih Tim Lain</a>
                    </div><!-- end .row-2 -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
		<div class="widget tr videotutorial">
			<h2>BINGUNG?</h2>
			<span class="yellow">Mainkan video dibawah untuk petunjuk mengisi form ini</span>
			<div id="vidbox">
				<iframe width="100%" height="200" src="//www.youtube.com/embed/EE_V-mSnH3M" frameborder="0" allowfullscreen></iframe>
			</div><!-- end #vidbox -->
		</div><!-- end .videotutorial -->
	    <div class="widget">
	        <div class="nav-side">
	            <ul>
                   <li><span>Profile Anda</span></li>
                   <li><span>Pilih Tim</span></li>
                   <li class="current"><span>Pilih Pemain</span></li>
                   <li><span>Pilih Staff</span></li>
	              
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">SISA UANG</h3>
	            <h1>SS$ <?=number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. PENGELUARAN MINGGUAN</h3>
	            <h1>SS$ <span class="expense">0</span></h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<script>
function create_team(){
    populate_selected(function(s){
        $("input[name='players']").val(s);
        $("form.theForm").submit();
    });
}
//EVENTS
$("select[name='select_team']").on('custom_select', function(event, data) {
   getPlayers(data.value);
});
function getPlayers(team_id){
      $("#available").html('Please wait...');
     api_call("<?=$this->Html->url('/game/players/')?>"+team_id,function(response){
        $("#available").html('');
        if(response.length>0){
            tmp['available_teams'] = response;
            var n = response.length;
            $.each(response,function(k,v){
                if(v!=null){
                    est_expenses += v.salary;
                    append_view(player,'#available',v);    
                }
                if(k==(n-1)){
                    $('.expense').html(number_format(est_expenses));
                }
            });
        }
    });
}
$(document).ready(function(){
  getPlayers("<?=$selected_team['team_id']?>");  
});
</script>
<script type="text/template" id="player">
  <%
        var jersey_color = 'j-red';
        var pos_code = 'F';
        switch(position){
            case 'Goalkeeper':
                pos_code = 'Goalkeeper';
                jersey_color = 'j-grey';
            break;
            case 'Defender':
                pos_code = 'Defender';
                jersey_color = 'j-blue';
            break;
            case 'Midfielder':
                pos_code = 'Midfielder';
                jersey_color = 'j-yellow';
            break;
            case 'Forward':
                pos_code = 'Forward';
                jersey_color = 'j-red';
            break;
            default:
                pos_code = 'Forward';
                jersey_color = 'j-red';
            break;
        }
		$("table tbody tr:nth-child(odd)").addClass("odd");
		$("table tbody tr:nth-child(even)").addClass("even");
    %>
	
  <tr id="<%=uid%>">
    <td><%=name%></td>
    <td><%=pos_code%></td>
  </tr>
</script>
<script type="text/template" id="player_selected">
<div class="jersey-player ">
<a href="#/unselect_player/<%=uid%>" id="<%=uid%>">
    <%
        var jersey_color = 'j-red';
        var pos_code = 'F';
        switch(position){
            case 'Goalkeeper':
                pos_code = 'G';
                jersey_color = 'j-grey';
            break;
            case 'Defender':
                pos_code = 'D';
                jersey_color = 'j-blue';
            break;
            case 'Midfielder':
                pos_code = 'M';
                jersey_color = 'j-yellow';
            break;
            case 'Forward':
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
            default:
                pos_code = 'F';
                jersey_color = 'j-red';
            break;
        }
    %>
    <div class="jersey <%=jersey_color%>"><%=pos_code%></div>
    <span class="player-name"><%=name%></span>
</a>
</div>
</script>