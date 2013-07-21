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
        <div class="box4 fl">
            <div class="widget tr match-date">
                <h2>next match</h2>
                <span class="date yellow"><?=date("d/m/Y",strtotime($next_match['match_date']))?></span>
            </div><!-- end .widget -->
            <div class="widget tr match-team">
                <div class="col3 home-team">
                    <a href="#" class="team-logo"><img src="<?=$this->Html->url('/images/team/logo1.png')?>" /></a>
                    <h3><?=h($next_match['home_name'])?></h3>
                </div><!-- end .col3 -->
                <div class="col3 vs">
                    <h2>Vs</h2>
                </div><!-- end .col3 -->
                <div class="col3 away-team">
                    <a href="#" class="team-logo"><img src="<?=$this->Html->url('/images/team/logo2.png')?>" /></a>
                    <h3><?=h($next_match['away_name'])?></h3>
                </div><!-- end .col3 -->
            </div><!-- end .widget -->
            <div class="widget tr match-place">
                <p class="stadion"><?=h($venue['name'])?></p>
                <p class="attendance">Capacity : <?=number_format($venue['capacity'])?></p>
              
                <a class="view-more" href="<?=$this->Html->url('/match')?>">See All Matches</a>
            </div><!-- end .widget -->
            <div class="widget tr perform-team">
                <h2>your perfomance</h2>
                <h3><span class="span1">League Rank</span>:<span class="span2">0</span></h3>
                <h3><span class="span1">Last Earning</span>:<span class="span2">0</span></h3>
                <h3><span class="span1">Best PLayer</span>:<span class="span2">0</span></h3>
                <h3><span class="span1">Best Match</span>:<span class="span2">0</span></h3>
                <h3><span class="span1">Club Value</span>:<span class="span2">0</span></h3>
                <a class="view-more" href="<?=$this->Html->url('/leaderboard')?>">View Leaderboard</a>
            </div><!-- end .widget -->
           
        </div><!-- end .box4 -->
        <div class="box3 tr fl">
            <div class="field-container">
                <div class="selectFormation">
                <select name="formations" id="formation-select" class="styled">
                    <option>Select Formation</option>
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
                <a id="btn_save" class="showPopup button" href="#popup-messages">Save Formations</a>
                </div>
                <div id="droppable" class="drop field-formation">
                    <div id="the-formation" class="drop">
                        
                    </div><!-- end .my-formation -->
                </div><!-- end .field-formation -->
            </div><!-- end .field-container -->
        </div><!-- end .box3 -->
        <div class="box4 fr">
            <div class="widget tr squad-team-name">
                <h2><?=h($club['team_name'])?></h2>
                
            </div><!-- end .widget -->
            <div class="widget tr squad-team">
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
                    $page = 'page-'.$last_page;
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
            </div><!-- end .widget -->
            <div class="widget tr action-button">
                <a class="prev" href="javascript:;">PREV</a>
                <a class="next" href="javascript:;">NEXT</a>
            </div><!-- end .widget -->
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
    '4-4-1-1' : ['','G','D','D','D','D','M','M','M','M','F','F']
};
$(document).ready(function(){
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
        createPaging();
        $("#draggable").draggable({
            drag:function( event, ui ) {
               // $(this).css('border','');
                drag_busy = true;
                var pos = $(this).find('div.num').html();
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
                        console.log($(target).html());
                        console.log($(target).offset().left+","+$(target).offset().top);
                        console.log($('#universal').offset().left+","+$('#universal').offset().top);
                        console.log($("#fillDetailsPage").offset().left+","+$("#fillDetailsPage").offset().top);
                        var nx = 0;
                        console.log(navigator.userAgent);
                        if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                            //only for firefox
                            nx = target.offset().left - ($("#universal").offset().left + 13);
                        }else if(navigator.userAgent.toLowerCase().indexOf('msie') > -1){
                            //for msie
                            nx = target.offset().left - ($("#universal").offset().left + 13);
                        }else{
                            //for chrome
                            nx = target.offset().left - ($("#universal").offset().left - $('#draggable').width());
                        }
                        
                        var ny = target.offset().top - $("#universal").offset().top;

                        console.log('->'+nx+","+ny);
                        $("#draggable").css('top',ny);
                        $("#draggable").css('left',nx);
                        $("#draggable").find('.player-name').hide();
                        $("#draggable").find('.player-status').remove();
                        $("#draggable").show();
                        $("div.bench").removeClass('playerBoxSelected');
                        target.addClass('playerBoxSelected');

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
                if(positioning[i]==pos){
                    $("#p"+i+".slot").show();
                }
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
            api_call('<?=$this->Html->url("/game/lineup")?>',function(data){
                $("#formation-select option").filter(function() {
                    return $(this).text() == data.formation; 
                }).prop('selected', true);
                $("#the-formation").removeClass().addClass('formation-'+data.formation);
                selectedVal['formations'] = {label:data.formation,
                                            value:data.formation};

                render_view(defaultformation,'#the-formation',{});

                if(data.lineup.length==0){
                    initLineupEvents();
                }else{
                    //$("#the-formation").html('');
                    for(var i in data.lineup){
                        append_view(tpllineup,'#the-formation',data.lineup[i]);    
                    }
                   
                    initLineupEvents();
                }
                flag_players();
                hide_slots();
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
            var  curr_item = {
                            item:null,
                            left:0,
                            top:0,
                            distance:{x:9999,
                                      y:9999}
                        };
            var dx = 0;
            var dy = 0;

            $.each($("#the-formation").children(),function(k,item){
               
                dx = Math.abs(($(item).offset().left-$("#universal").position().left-30)-x);
                dy = Math.abs(($(item).offset().top-$("#universal").position().top-30)-y);
              
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

                    if(k>=10){
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
        }

    getLineUp();  
});
</script>
<script type="text/template" id="tplsave">
    <div class="confirm">
        <h1>Confirmation</h1>
        <h3>are you sure want to save the new lineups ?</h3>
        <p><a href="#/save_formation" class="button">Yes</a>
            <a href="#" class="button" onclick="$.fancybox.close();return false;">Cancel</a></p>
    </div>
    <div class="saving" style="display:none;">
        <h1>Saving your lineup</h1>
        <h3>Please wait..</h3>
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