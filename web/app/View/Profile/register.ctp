<div id="fillDetailsPage">
	
    <div id="thecontent">
        <div id="content">
        	<div class="content">
            	<div class="row-2">
                    <h1 class="red">Isi data lengkap Anda</h1>
                    <p>Silakan isi semua data yang diperlukan sesuai identitas dan jawab beberapa pertanyaan yang ada. Pastikan semuanya diisi dengan benar untuk mendapatkan pengalaman terbaik dalam bermain Fantasy Football League!</p>
    			</div><!-- end .row-2 -->
                <?php echo $this->Session->flash();?>
                <form class="theForm" action="<?=$this->Html->url('/profile/register')?>" method="post" enctype="multipart/form-data">
                    
                    <div class="row">
                        <label>Nama</label>
                        <input type="text" name="name" value="<?=h($user['name'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Email</label>
                        <input type="text" name="email" value="<?=h(@$user['email'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Lokasi</label>
                        <input type="text" name="city" value="<?=h(@$user['location']['name'])?>"/>
                    </div><!-- end .row -->
                    <div class="row">
                        <label>Nomor HP</label>
                        <input type="text" name="phone_number"/>
                        <?php if($phone_empty):?>
                        <span class="error">harap isi dahulu.</span>
                        <?php endif;?>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Dari mana Anda tahu tentang FFL?</label>
                        <input type="radio" class="styled" name="hearffl" checked="checked" value="1"/><span>Supersoccer</span>
                        <input type="radio" class="styled" name="hearffl" value="2"/><span>TV</span>
                        <input type="radio" class="styled" name="hearffl" value="3"/><span>Radio</span>
                        <input type="radio" class="styled" name="hearffl" value="4"/><span>Facebook</span>
                        <input type="radio" class="styled" name="hearffl" value="5"/><span>Twitter</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Apakah Anda ingin menerima statistik harian melalui email?</label>
                        <input type="radio" class="styled" name="daylyemail" checked="checked" value="1"/><span>Ya</span>
                        <input type="radio" class="styled" name="daylyemail" value="0"/><span>Tidak</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Apakah Anda ingin menerima statistik harian melalui SMS*?</label>
                        <input type="radio" class="styled" name="daylysms" checked="checked" value="1"/><span>Ya</span>
                        <input type="radio" class="styled" name="daylysms" value="0"/><span>Tidak</span>
                    </div><!-- end .row -->
                    <div class="row inputRadio">
                        <label>Apakah ini pertama kalinya Anda bermain game liga fantasi?</label>
                        <input type="radio" class="styled" name="firstime" checked="checked" value="1"/><span>Ya</span>
                        <input type="radio" class="styled" name="firstime" value="0"/><span>Tidak</span>
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
	               <li class="current"><span>Fill in Your Detail</span></li>
	               <li><span>Fill in Your Team</span></li>
	               <li><span>Fill in Your Players</span></li>
	               <li><span>Fill in Your Staff</span></li>
	            </ul>
	        </div><!-- end .nav-side -->
	    </div><!-- end .widget -->
	    <div class="widget">
	        <div class="cash-left">
	            <h3 class="red">Cash Left</h3>
	            <h1>SS$ <?=@number_format($INITIAL_BUDGET)?></h1>
	            <h3 class="red">Est. Weekly Expenses</h3>
	            <h1>SS$ 0</h1> 
	        </div>
	    </div><!-- end .widget -->
	</div><!-- end #sidebar -->
    </div><!-- end #thecontent -->
</div><!-- end #fillDetailsPage -->