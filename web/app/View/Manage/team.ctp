<?php
$can_update_formation = true;

if(date_default_timezone_get()=='Asia/Jakarta'){
    $match_date_ts += 6*60*60;
}
if(time() > $match_date_ts-(4*60*60) && Configure::read('debug') == 0){
    $can_update_formation = false;
}
if(isset($first_time) && $first_time==true):
?>
<div id="bgPopup"></div>
<div id="popupWelcome">
	<a href="#" class="closebtn"><span class="icon-close"></span></a>
	<div class="popup-content">
    	<h3>Selamat datang di<br /><span class="red">SuperSoccer Fantasy League</span></h3>
       
        <img src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$club['team_id'])?>.png"/>
        <h4><?=h($club['team_name'])?></h4>
        <h5>Pilih formasi, pemain starter dan cadangan untuk mulai berkompetisi </h5>
    </div>
</div>
<?php
endif;
?>
<div id="fillDetailsPage">
    <?php echo $this->element('infobar'); ?>
    <div id="thecontent">
        <div class="box4 fl">
            <div class="widget tr match-date">
                <h2>PERTANDINGAN BERIKUTNYA</h2>
                <span class="date yellow">
                    <?=date("d/m/Y",strtotime($next_match['match_date']))?><br/>
                    Minggu ke: <?=$next_match['matchday']?>
                </span>
            </div><!-- end .widget -->
            <div class="widget tr match-team">
                <div class="col3 home-team">
                    <a href="#" class="team-logo">
                        <img style="height:46px;" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$next_match['home_id'])?>.png"/>
                    </a>
                    <h3><?=h($next_match['home_name'])?></h3>
                </div><!-- end .col3 -->
                <div class="col3 vs">
                    <h2>Vs</h2>
                </div><!-- end .col3 -->
                <div class="col3 away-team">
                    <a href="#" class="team-logo">
                         <img style="height:46px;" src="http://widgets-images.s3.amazonaws.com/football/team/badges_65/<?=str_replace('t','',$next_match['away_id'])?>.png"/>
                    </a>
                    <h3><?=h($next_match['away_name'])?></h3>
                </div><!-- end .col3 -->
            </div><!-- end .widget -->
            <div class="widget tr match-place">
                <p class="stadion"><?=h($venue['name'])?></p>
                <p class="attendance">Kapasitas : <?=number_format($venue['capacity'])?></p>
              
                <a class="view-more" href="<?=$this->Html->url('/match')?>">Lihat Semua Pertandingan</a>
            </div><!-- end .widget -->
            <div class="widget tr perform-team">
                <h2>Performa Anda</h2>
                <h3><span class="span1">Peringkat Liga</span>:<span class="span2"><?=number_format($USER_RANK)?></span></h3>
                <h3><span class="span1">Perolehan Terakhir</span>:<span class="span2">SS$ <?=number_format($last_earning)?></span></h3>
                <h3><span class="span1">Kekayaan</span>:<span class="span2">SS$ <?=number_format($team_bugdet)?></span></h3>
                <h4><span class="span1">Pemain Terbaik</span><span class="span2">
                    <?php
                        if(isset($best_player)):
                    ?>
                    <?=h($best_player['name'])?>
                    <?php
                        else:
                    ?>
                    N/A
                    <?php
                    endif;
                    ?>
                </span></h4>
                
                 <h4><span class="span1">Pertandingan Terbaik</span><span class="span2"><?=$best_match?></span></h4>
                <a class="view-more" href="<?=$this->Html->url('/leaderboard')?>">Lihat Papan Peringkat</a>
            </div><!-- end .widget -->
            <div class="widget tr downloadapp">
                    <a href="#" class="download-googleplay">&nbsp;</a>
                    <a href="#" class="download-appstore">&nbsp;</a>
            </div><!-- end .widget -->
           
        </div><!-- end .box4 -->
        <div class="box3 tr fl drop">
            <div class="field-container">
                <div class="selectFormation">
                <select name="formations" id="formation-select" class="styled">
                    <option>Pilih Formasi</option>
                    <option>4-4-2</option>
                    <option>4-4-2-A</option>
                    <option>4-3-3</option>
                    <option>4-2-3-1</option>
                    <option>3-5-2</option>
                    <option>4-4-1-1</option>
                    <option>4-3-2-1</option>
                    <option>4-3-1-2</option>
                    <option>5-3-2</option>
                    <option>5-3-1-1</option>
                    <option>5-2-2-1</option>
                    <option>4-2-4</option>
                    <option>3-4-3</option>
                    <option>3-4-2-1</option>
                </select>
                <?php if($can_update_formation):?>
                <a id="btn_save" class="showPopup button" href="#popup-messages">Simpan Formasi</a>
                <?php endif;?>
                </div>
                <div id="droppable" class="field-formation">
                    <div id="the-formation">
                        
                    </div><!-- end .my-formation -->
                </div><!-- end .field-formation -->
            </div><!-- end .field-container -->
        </div><!-- end .box3 -->
        <div class="box4 fr">
            <div class="widget tr squad-team-name">
                <h2><?=h($club['team_name'])?></h2>
                
            </div><!-- end .widget -->
            <div id="rooster" class="widget tr squad-team">
            	<div class="starters">
                    <h4>Daftar Pemain</h4>
                    <div id="jp-container" class="jp-container">
                    <?php
                    foreach($players as $n=>$player):
                        switch($player['position']){
                          case 'Goalkeeper':
                            $player_pos = "G";
                            $color = "grey";
                          break;
                          case 'Midfielder':
                            $player_pos = "M";
                            $color = "yellow";
                          break;
                          case 'Forward':
                            $player_pos = "F";
                            $color  = "red";
                          break;
                          default:
                            $player_pos = "D";
                            $color = "blue";
                          break;
                        }
                        $last_page = floor($n/16);
                       // $page = 'page-'.$last_page;
                        $page = 'page-0';
                        if($player['known_name']!=null){
                            $player['name'] = $player['known_name'];
                        }
                    ?>
                    <div class="bench jersey-player <?=$page?>">
                        <a href="javascript:void(0);" no="<?=h($player['uid'])?>">
                            <div class="jersey num j-<?=$color?>"><?=$player_pos?></div>
                            <div class="player-info">
                                <span class="player-name"><?=h($player['name'])?></span>
                                <span class="player-status">Playable</span>       
                            </div><!-- end .player-info -->
                        </a>
                    </div><!-- end .jersey-player -->
                    <?php endforeach;?>
                    </div><!-- end #jp-container -->
                </div><!-- end .starter -->
            	<div class="substitutions drop">
                    <h4>Pemain Cadangan</h4>
                </div><!-- end .substitutions -->
            </div><!-- end .widget -->
            <!--
            <div class="widget tr action-button">
                <a class="prev" href="javascript:;">PREV</a>
                <a class="next" href="javascript:;">NEXT</a>
            </div>-->
            <!-- end .widget -->
        </div><!-- end .box4 -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<div id="draggable" class="jersey-player" style="display:none;position:absolute;">
</div>
<!--popups-->
<div class="popup">
    <div class="popupContainer popup-small" id="popup-messages">
        <div class="popupHeader">
        </div><!-- END .popupHeader -->
        <div class="popupContent">
            <div class="entry-popup">
                
            </div><!--END .entry-popup-->
        </div><!-- END .popupContent -->
    </div><!-- END .popupContainer -->
</div><!-- END .popup --> 
<script>
var selected = null;
var page = 0;
var last_page = <?=intval($last_page)?>;
var drag_busy = false;

var formation = {
    '4-4-2' : ['','G','D','D','D','D','M','M','M','M','F','F'],
    '4-4-2-A' : ['','G','D','D','D','D','M','M','M','M','F','F'],
    '4-3-3' : ['','G','D','D','D','D','M','M','M','F','F','F'],
    '4-2-3-1' : ['','G','D','D','D','D','M','M/F','M','M/F','F','M/F'],
    '3-5-2' : ['','G','D','D','M','D','M','M','M','M','F','F'],
    '4-4-1-1' : ['','G','D','D','D','D','M','M','M','M','F','F'],
    '4-3-2-1' : ['','G','D','D','D','D','M','M','M','M/F','M/F','F'],
    '4-3-1-2' : ['','G','D','D','D','D','M','M','M','M/F','F','F'],
    '5-3-2' : ['','G','D','D','D','D','D','M','M','M','F','F'],
    '5-3-1-1' : ['','G','D','D','D','D','D','M','M','M','F','F'],
    '5-2-2-1' : ['','G','D','D','D','D','D','M','M','M/F','M/F','F'],
    '4-2-4' : ['','G','D','D','D','D','M','M','F','F','F','F'],
    '3-4-3' : ['','G','D','D','D','M','M','M','M','F','F','F'],
    '3-4-2-1' : ['','G','D','D','D','M','M','M','M','M/F','M/F','F']
};
$(document).ready(function(){
		$("a.closebtn").click(function(){
			$("#bgPopup").fadeOut();
			$("#popupWelcome").fadeOut();
		});
        $("#btn_save").fancybox({
            beforeLoad : function(){
                render_view(tplsave,"#popup-messages .popupContent .entry-popup",[]);
            },
           
        });

        $('.prev').hide();
        if(last_page==0){
            $('.next').hide();
        }
        $('.prev').click(function(){prev();});
        $('.next').click(function(){next();});

        $("#rooster").scroll(function(e){
            $("#draggable").hide();
        });

        createPaging();
        $("#draggable").draggable({
            drag:function( event, ui ) {
               // $(this).css('border','');
                drag_busy = true;
                var pos = $(this).find('div.num').html();
                hide_slots();
                show_slots(pos);
            },
            stop: function( event, ui ) {
                 drag_busy = false;
                 $("div.bench").removeClass('playerBoxSelected');
                // $(this).css('border','1px solid #333');
                hide_slots();
            },
        });

        
        $(".bench").mouseover(function(e){
            if(!drag_busy){
                var target = $(this);
              
                player_in_lineup($(this).find('a').attr('no'),function(is_exist){
                    if(!is_exist){
                        $("#draggable").html(target.html());
                       
                        var nx = 0;
                       
                        if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                            //only for firefox
                            nx = target.offset().left - ($("#universal").offset().left + 13);
                        }else if(navigator.userAgent.toLowerCase().indexOf('msie') > -1){
                            //for msie
                            nx = target.offset().left - ($("#universal").offset().left + 13);
                        }else{
                            //for chrome
                            nx = target.offset().left - ($("#universal").offset().left+11);
                        }
                        
                        var ny = target.offset().top - $("#universal").offset().top;

                       
                        $("#draggable").css('top',ny);
                        $("#draggable").css('left',nx);
                        $("#draggable").find('.player-name').hide();
                        $("#draggable").find('.player-status').remove();
                        $("#draggable").show();
                        $("div.bench").removeClass('playerBoxSelected');
                        target.addClass('playerBoxSelected');
                          //pas di klik, langsung munculin slotnya.
                            var pos = target.find('div.num').html();
                            hide_slots();
                            show_slots(pos);


                    }else{
                        $("#draggable").hide();
                        $("div.bench").removeClass('playerBoxSelected');
                    }
                });
            }
        });
        /*$(".bench").mouseout(function(e){
            var target = $(this);
            target.removeClass('playerBoxSelected');
        });*/
        $(".drop").droppable({
            greedy: true,
            
            drop: function( event, ui ){
                var dropX = event.pageX-$("#universal").position().left-30;
                var dropY = event.pageY - $("#universal").position().top-30;
                replaceLineup(dropX,dropY);
                flag_players();
                $("#draggable").hide();
                $("div.bench").removeClass('playerBoxSelected');
                hide_slots();
              },
            
        });

        function show_slots(pos){
            var positioning = formation[selectedVal['formations'].value];
            for(var i in positioning){
                var n_pos = positioning[i];

                if(n_pos=="M/F"){
                    if(pos == 'M' || pos == 'F'){
                        $("#p"+i+".slot").show();
                    }
                }else{
                    if(n_pos == pos){
                        $("#p"+i+".slot").show();
                    }   
                }
            }
            //show slots for subs
            for(var i=12;i<17;i++){
                $("#p"+i+".slot").show();
            }
        }
        function hide_slots(){
            $(".slot").hide();
        }
        function createPaging(){
            for(var i=1;i<=last_page;i++){
                $(".page-"+i).hide();
            }
        }
        function prev(){
            if(page>0){
                page-=1;
            }
            for(var i=0;i<=last_page;i++){
                $(".page-"+i).hide();
            }
            $(".page-"+page).fadeIn();
            if(page==0&&last_page>0){
                 $(".next").show();
            }
            $("#draggable").hide();
        }
        function next(){
            page+=1;
            if(page==last_page){
                $(".next").hide();
            }
            $(".prev").show();
            for(var i=0;i<=last_page;i++){
                $(".page-"+i).hide();
            }
            $(".page-"+page).fadeIn();
            $("#draggable").hide();
        }
        function player_in_lineup(id,callback){
            var is_exist = false;
            $.each($("#the-formation").children(),function(k,item){
               if($(item).find('a').attr('no')==id){
                    is_exist = true;
               }
               if(k>=10){
                    callback(is_exist);
               }
            });
        }
        function flag_players(){
            var current_lineup = [];
            var n=1;
            $('div.bench').removeClass('playerBoxChoosed');
            $.each($("#the-formation").children(),function(t,l){
                    current_lineup.push({player_id:$(l).find('a').attr('no')});
                    if(n==$("#the-formation").children().length){
                        onDone();
                    }
                    n++;
                });
            function onDone(){
                $.each($('div.bench'),function(k,player){
                    var player_id = $(player).find('a').attr('no');
                     for(var i in current_lineup){
                            if(player_id == current_lineup[i].player_id){
                                $(player).addClass('playerBoxChoosed');
                                break;
                            }
                        }
                });
            }
        }
        function getLineUp(){
            var n_player = 0;
            api_call('<?=$this->Html->url("/game/lineup")?>',function(data){
                $("#formation-select option").filter(function() {
                    return $(this).text() == data.formation; 
                }).prop('selected', true);
                $("#formation-select option").trigger('change');
                $("#the-formation").removeClass().addClass('formation-'+data.formation);
                selectedVal['formations'] = {label:data.formation,
                                            value:data.formation};

                render_view(defaultformation,'#the-formation',{});
                append_view(defaultsubs,'#the-formation',{});
                n_player = data.lineup.length;
                if(n_player==0){
                    initLineupEvents();
                    show_slots();
                }else{
                    //$("#the-formation").html('');
                    for(var i in data.lineup){
                        append_view(tpllineup,'#the-formation',data.lineup[i]);    
                    }
                   
                    initLineupEvents();
                }
                flag_players();
                if(n_player>0){
                    hide_slots();
                }
            });

        }
        function initLineupEvents(){
             $(".starter").click(function(){
                if(selected==null){
                    selected = $(this);
                    selected.addClass('playerSelected');
                }else{
                    selected.removeClass('playerSelected');
                    var h = selected.html();
                    selected.html($(this).html());
                    $(this).html(h);
                    selected = null;
                }
            });
        }
        function getRealPosition(p){
            switch(p){
                case 'G': 
                    return 'Goalkeeper';
                break;
                case 'D': 
                    return 'Defender';
                break;
                case 'M': 
                    return 'Midfielder';
                break;
                case 'F': 
                    return 'Forward';
                break;
                default:
                    return '';
                break;
            }
        }
        function replaceLineup(x,y){
            <?php if($can_update_formation):?>
            var  curr_item = {
                            item:null,
                            left:0,
                            top:0,
                            distance:{x:9999,
                                      y:9999}
                        };
            var dx = 0;
            var dy = 0;
            var ux = 0;
            var uy = $("#universal").offset().top;
            
            if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                //only for firefox
                ux = ($("#universal").offset().left + 13);
            }
            /*else if(navigator.userAgent.toLowerCase().indexOf('msie') > -1){
                //for msie
                ux = ($("#universal").offset().left + 13);
            }else{
                //for chrome
                ux = ($("#universal").offset().left);
            }
            */
            $.each($("#the-formation").find('.slot'),function(k,item){

                
                console.log("#universal.offset",$("#universal").offset());
                console.log("#universal.position",$("#universal").position());
                console.log("ux","uy",ux,uy);
                console.log($(item).attr('id')," - item.offset",$(item).offset());
                console.log('droppoint',x,y);

                if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                //only for firefox
                    dx = Math.abs(($(item).offset().left - ux)-x);
                    dy = Math.abs(($(item).offset().top - uy)-y);
                }else if(navigator.userAgent.toLowerCase().indexOf('msie') > -1){
                    //for msie
                    dx = Math.abs(($(item).offset().left - ux)-x);
                    dy = Math.abs(($(item).offset().top - uy)-y);
                }else{
                    //for chrome
                    dx = Math.abs(($(item).offset().left)-x);
                    dy = Math.abs(($(item).offset().top)-y);
                }

               
                console.log($(item).attr('id'),dx,dy);
                if(curr_item!=null){
                    if((curr_item.distance.x > dx && dy < 50)){
                            curr_item = {
                                item:item,
                                left:$(item).offset().left,
                                top:$(item).offset().top,
                                distance:{x:dx,
                                          y:dy}
                            };
                              
                    }

                    if(k>=15){
                        if(typeof $(curr_item.item).attr('id') !== 'undefined'){
                           var player_data = {
                                player_id: $("#draggable").find('a').attr('no'),
                                name: $("#draggable").find('.player-name').html(),
                                position: getRealPosition($("#draggable").find('div.num').html()),
                                position_no : parseInt($(curr_item.item).attr('id').replace('p',''))
                           };
                           
                           //replace the existing slot if necessary
                           $("#p"+player_data.position_no+".starter").remove();
                           
                           //then add the new one
                           append_view(tpllineup,'#the-formation',player_data);
                        }
                       curr_item = null;
                    }
                }
                
            });
            <?php endif;?>
        }

    getLineUp(); 


});
</script>
<script type="text/template" id="tplsave">
    <div class="confirm">
        <h1>Konfirmasi</h1>
        <h3>Apakah Anda yakin ingin menyimpan formasi pemain yang baru?</h3>
        <p><a href="#/save_formation" class="button">Ya</a>
            <a href="#" class="button" onclick="$.fancybox.close();return false;">Batal</a></p>
    </div>
    <div class="saving" style="display:none;">
        <h1>Menyimpan formasi pemain</h1>
        <h3>Harap tunggu sebentar..</h3>
        <p><img src="<?=$this->Html->url('/css/fancybox/fancybox_loading@2x.gif')?>"/></p>
    </div>
</script>
<script type="text/template" id="tplmsg">
    <h1><%=title%></h1>
    <p><%=result%></p>
</script>

<script type="text/template" id="tpllineup">
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
        if(typeof known_name === 'string'){
            name = known_name;
        }
        //use last name only
        var arr = name.split(' ');
        if(arr.length > 1){
            name = arr[arr.length-1];
        }else{
            name = arr[0];
        }
    %>
    <div id="p<%=position_no%>" class="starter jersey-player p<%=position_no%>">
    <a href="javascript:void(0);" no="<%=player_id%>">
        <div class="jersey num <%=jersey_color%>"><%=pos_code%></div>
        <span class="player-name"><%=name%></span>
    </a>
    </div><!-- end .jersey-player -->
</script>

<script type="text/template" id="defaultformation">
    <div id="p11" class="jersey-player p11 slot">
        
        <span class="player-name">11</span>
    </div><!-- end .jersey-player -->
    <div id="p10" class="jersey-player p10 slot">
        
        <span class="player-name">10</span>
    </div><!-- end .jersey-player -->
    <div id="p9" class="jersey-player p9 slot">
        
        <span class="player-name">9</span>
    </div><!-- end .jersey-player -->
    <div id="p8" class="jersey-player p8 slot">
        
        <span class="player-name">8</span>
    </div><!-- end .jersey-player -->
    <div id="p7" class="jersey-player p7 slot">
        
        <span class="player-name">7</span>
    </div><!-- end .jersey-player -->
    <div id="p6" class="jersey-player p6 slot">
        
        <span class="player-name">6</span>
    </div><!-- end .jersey-player -->
    <div id="p5" class="jersey-player p5 slot">
        
        <span class="player-name">5</span>
    </div><!-- end .jersey-player -->
    <div id="p4" class="jersey-player p4 slot">
       
        <span class="player-name">4</span>
    </div><!-- end .jersey-player -->
    <div id="p3" class="jersey-player p3 slot">
       
        <span class="player-name">3</span>
    </div><!-- end .jersey-player -->
    <div id="p2" class="jersey-player p2 slot">
        
        <span class="player-name">2</span>
    </div><!-- end .jersey-player -->
    <div id="p1" class="jersey-player p1 slot">
        
        <span class="player-name">1</span>
    </div><!-- end .jersey-player -->
</script>

<script type="text/template" id="defaultsubs">
    <div id="p12" class="jersey-player p12 slot">
        
        <span class="player-name"></span>
    </div><!-- end .jersey-player -->
    <div id="p13" class="jersey-player p13 slot">
        
        <span class="player-name"></span>
    </div><!-- end .jersey-player -->
    <div id="p14" class="jersey-player p14 slot">
        
        <span class="player-name"></span>
    </div><!-- end .jersey-player -->
    <div id="p15" class="jersey-player p15 slot">
      
        <span class="player-name"></span>
    </div><!-- end .jersey-player -->
    <div id="p16" class="jersey-player p16 slot">
        
        <span class="player-name"></span>
    </div><!-- end .jersey-player -->
</script>
