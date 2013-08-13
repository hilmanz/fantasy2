<div id="fillDetailsPage">
    
    <div id="thecontent">
        <div id="content">
            <div class="content">
                <div class="row-2">
                    <h1 class="red">Choose Your Staff</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
                </div><!-- end .row-2 -->
                <form class="theForm" action="<?=$this->Html->url('/profile/register_staff')?>"
                    method="post" enctype="application/x-www-form-urlencoded">
                    <div class="row-2">
                        <div class=" staff-list" id="available">
                            <?php
                                foreach($officials as $official):
                            ?>
                            <div class="thumbStaff">
                                <div class="avatar-big">
                                    <img src="<?=$this->Html->url('/content/thumb/default_avatar.png')?>" />
                                </div><!-- end .avatar-big -->
                                <p><?=h($official['name'])?></p>
                                <div>
                                    SS$<?=number_format($official['salary'])?> / Week
                                </div>
                                <div>
                                    <?php if(@$official['hired']):?>
                                        <a id="staff-<?=$official['id']?>" href="#/dismiss/<?=$official['id']?>" class="button">Hired</a>
                                    <?php else:?>
                                        <a id="staff-<?=$official['id']?>" href="#/hire/<?=$official['id']?>" class="button">Select</a>
                                    <?php endif;?>
                                </div>
                            </div><!-- end .thumbStaff -->
                            <?php
                                endforeach;
                            ?>
                        </div><!-- end .col2 -->
                      
                    </div><!-- end .row-2 -->
                   <div class="row-2">
                        <input type="hidden" name="fb_id" value="<?=$USER_DATA['fb_id']?>"/>
                        <input type="hidden" name="complete_registration" value="1"/>
                        <input type="submit" value="Save &amp; Continue" class="button" />
                   </div>
                </form>
            </div><!-- end .content -->
        </div><!-- end #content -->
    <div id="sidebar" class="tr">
        <div class="widget">
            <div class="nav-side">
                <ul>
                   <li><span>Fill in Your Detail</span></li>
                   <li><span>Fill in Your Team</span></li>
                   <li><span>Fill in Your Players</span></li>
                   <li class="current"><span>Fill in Your Staff</span></li>
                  
                </ul>
            </div><!-- end .nav-side -->
        </div><!-- end .widget -->
        <div class="widget">
            <div class="cash-left">
                <h3 class="red">Cash Left</h3>
                <h1>SS$ <?=number_format($team_bugdet)?></h1>
                <h3 class="red">Est. Weekly Expenses</h3>
                <h1 class="expenses">SS$ <?=number_format($weekly_salaries)?></h1> 
            </div>
        </div><!-- end .widget -->
    </div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->

<script>
est_expenses = <?=intval($weekly_salaries)?>;
staffs = <?=json_encode($officials)?>;
</script>