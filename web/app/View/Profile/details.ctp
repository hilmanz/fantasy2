<div id="fillDetailsPage">
	<?php echo $this->element('infobar'); ?>
    <div id="thecontent">
        <div id="content">
        	<div class="content">
            	<div class="row-2">
                    <h1 class="red">MY PROFILE</h1>
                    <p>Aenean lacinia bibendum nulla sed consectetur. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Etiam porta sem malesuada magna mollis euismod. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</p>
    			</div><!-- end .row-2 -->
                <form class="theForm" action="<?=$this->Html->url('/profile/update')?>" 
                  enctype="multipart/form-data" method="post">
                    <div class="tr avatarBox">
                        <div class="avatar-big">
                            <?php if($user['avatar_img']==null):?>
                            <img src="http://graph.facebook.com/<?=$USER_DATA['fb_id']?>/picture" />
                            <?php else:?>
                            <img src="<?=$this->Html->url('/files/120x120_'.$user['avatar_img'])?>" />
                            <?php endif;?>
                        </div>

                        <a href="#popup-upload" class="button" id="btn_upload">Change Avatar</a>
                    </div>
                    <div class="row">
                        <label>Name</label>
                        <input type="text" name="name" value="<?=h($user['name'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Email</label>
                        <input type="text" name="email" value="<?=h($user['email'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Location</label>
                        <?=@$user['location']?>
                    </div><!-- end .row -->
                   
                    <div class="row">
                        <input type="hidden" name="save" value="1"/>
                        <input type="submit" value="Save Changes" class="button" />
                    </div><!-- end .row -->
                </form>
			</div><!-- end .content -->
        </div><!-- end #content -->
	<div id="sidebar" class="tr">
	    
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">CASH LEFT</h3>
	            <h1>EUR <?=number_format($team_bugdet)?></h1>
	            <h3 class="red">POINTS</h3>
	            <h1><?=number_format($USER_POINTS)?> pts</h1> 
                <h3 class="red">CURRENT RANK</h3>
                <h1><?=number_format($USER_RANK)?></h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->
<!--popups-->
<div class="popup">
    <div class="popupContainer popup-small" id="popup-upload">
        <div class="popupHeader">
            <h3>Upload Avatar</h3>
        </div><!-- END .popupHeader -->
        <div class="popupContent">
            <div class="entry-popup">
                <form id="uploadForm" enctype="multipart/form-data" method="post">
                    <input type="file" name="file"/>
                </form>
            </div><!--END .entry-popup-->
        </div><!-- END .popupContent -->
    </div><!-- END .popupContainer -->
</div><!-- END .popup --> 
<div class="popup">
    <div class="popupContainer popup-small" id="popup-message">
        <div class="popupHeader">
            <h3>Upload Avatar</h3>
        </div><!-- END .popupHeader -->
        <div class="popupContent">
            <div class="entry-popup">
               
            </div><!--END .entry-popup-->
        </div><!-- END .popupContent -->
    </div><!-- END .popupContainer -->
</div><!-- END .popup -->

<a id='popupmsg' href="#popup-message" style="display:none;">#</a>

<?php
echo $this->Html->script('d_uploader');
?>
<script>
var avatar_dir = "<?=$this->Html->url($avatar_dir)?>";
$("#btn_upload").fancybox({});
$("#popupmsg").fancybox({});
$("#uploadForm").file_uploader('<?=$this->Html->url("/profile/upload_image")?>',
{
    beforeSend:function(e){
        
    },
    success:function(e){
        $("#fileuploader").hide();
        var o = JSON.parse(e);
        if(o.status==1){
            $("#msg").hide();
            $("#uploaditemform").show();
            $("#uploadForm").find("input[name='file']").show();
            $("#progress").hide();
            $.fancybox.close();
            $("#popup-message").find('.entry-popup').html('Upload Completed !');
            $("#popupmsg").trigger('click');
            $(".avatar-big").find('img').attr('src',avatar_dir+o.files);
        }else{
            $("#popup-message").find('.entry-popup').html('Cannot upload your image, please try again later !');
            $("#popupmsg").trigger('click');
        }
    },
    error:function(e){
        console.log("error : "+e);
    }
});
</script>