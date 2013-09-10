<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<!-- Le styles -->
  <?php echo $this->Html->css(
        array('fancybox/jquery.fancybox.css?v=2.1.5','supersoccer'),
    null,array('media'=>'all')); 
  ?>
  <?php echo $this->Html->script(
    array('jquery-1.9.1',
      'jquery-ui-1.10.3.custom.min',
      'fancybox/jquery.fancybox.js?v=2.1.5'
    ));
?>
<?php echo $this->Html->script(
    array(
      'fancybox/jquery.fancybox-1.3.4.pack',
      'jquery.mousewheel',
      'jquery.jscrollpane.min',
      'scroll-startstop.events.jquery',
      'detectbrowser',
      'underscore-min',
      'backbone-min',
      'hoverIntent',
      'superfish',
      'customform',
      'slider/jquery.flexslider',
      'jquery.jcarousel.min.js',
      'supersoccer'
    ));
?>

<!--<script src="http://stats.supersoccer.co.id/js/widget.js"></script>-->
<script src="http://localhost/fantasy_stats/js/widget.js"></script>
<script>
var game_id=1;
</script>
<!--[if IE 6]>
<link rel="stylesheet" type="text/css" href="js/slider/themes/default/jquery.slider.ie6.css" />
<![endif]-->
<!--[if gte IE 9]>
  <style type="text/css">
    .grad {
       filter: none;
    }
  </style>
<![endif]-->
<title>SUPER SOCCER </title>
</head>

<body>
    <div id="top">
        <a id="logo" href="index.php" title="SUPER SOCCER - FANTASY FOOTBALL LEAGUE">&nbsp;</a>
        <div class="topnav">
            <ul id="topnav">
                <li><a href="http://www.supersoccer.co.id/" target="_blank" class="nav1">Home</a></li>
                <li><a href="http://www.supersoccer.co.id/category/supersoccer-tv/" class="nav2">Suppersoccer TV</a></li>
                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/" class="nav3">Liga Inggris</a>
                  <ul>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/berita-liga-inggris/">Berita</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/ulasan-liga-inggris/">Ulasan</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/special-features-liga-inggris/">Special Features</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/preview-pertandingan-liga-inggris/">Preview Pertandingan</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/laporan-pertandingan-liga-inggris/">Laporan Pertandingan</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/video-liga-inggris/">Video</a></li>
                        <li><a href="http://www.supersoccer.co.id/category/liga-inggris/photo-liga-inggris/">Photo</a></li>
                        <li><a href="http://www.supersoccer.co.id/info-statistik-liga-inggris/">Info Statistik</a></li>
                        <li class="menu2"><a href="http://www.supersoccer.co.id/category/klub-liga-inggris/">Klub Liga Inggris</a>
                            <ul>
                                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-united/">Manchester United</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-city/">Manchester City</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/arsenalfc/">Arsenal</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/chelsea/">Chelsea</a></li>
                                <li><a href="http://www.supersoccer.co.id/category/liga-inggris/klub-liga-inggris/manchester-united/">Liverpool</a></li>
                            </ul>
                        </li>
                	</ul>
                </li>
                <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/" class="nav4">Sepakbola Internasional</a>
                	 <ul>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/berita-liga-internasional/">Berita</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/ulasan-liga-internasional/">Ulasan</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/special-features-liga-internasional/">Special Features</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/preview-pertandingan-liga-internasional/">Preview Pertandingan</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/laporan-pertandingan-liga-internasional/">Laporan Pertandingan</a></li>
                    <li><a href="http://www.supersoccer.co.id/photo-sepakbola-internasional/">Photo</a></li>
                    <li><a href="http://www.supersoccer.co.id/info-statistik-sepakbola-internasional/">Info Statistik</a></li>
                    <li class="menu2"><a href="#">Klub</a>
                    	<ul>
                    		<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/">Klub Serie A</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/internazionale-milano">Internazionale Milano</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/juventus">Juventus</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-serie-a/ac-milan">AC Milan</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/">Klub La Liga</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/real-madrid">Real Madrid</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-la-liga/barcelona">Barcelona</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/">Klub Bundesliga</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/bayern-munich">Bayern Munich</a></li>
	                    	<li><a href="http://www.supersoccer.co.id/category/sepakbola-internasional/klub-bundesliga/borussia-dortmund">Borussia Dortmund</a></li>
	                    </ul>
                    </li>
                </ul>
                </li>
                <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/" class="nav5">Sepakbola Indonesia</a>
                    <ul>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/berita-sepakbola-indonesia/">Berita</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/ulasan-sepakbola-indonesia/">Ulasan</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/special-features-sepakbola-indonesia/">Special Features</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/preview-pertandingan-sepakbola-indonesia/">Preview Pertandingan</a></li>
                    <li><a href="http://www.supersoccer.co.id/category/sepakbola-indonesia/laporan-pertandingan-sepakbola-indonesia/">Laporan Pertandingan</a></li>
                    </ul>
                </li>
                <li><a href="#" class="nav6">Fun Zone</a>
                	
                </li>
                <li><a href="#" class="nav7">Suppersoccer Exclusive</a></li>
            </ul>
        </div>
    </div>
  <div id="body">
        <div id="universal">
        	<?php echo $this->fetch('content'); ?>
            <div id="footer">
            	<p>&copy; 2013 Suppersoccer All Right Reserved | Power by Gflix</p>
            </div>
        </div><!-- end #universal -->
    </div><!-- end #body -->
</body>
</html>