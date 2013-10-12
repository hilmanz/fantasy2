
	<meta charset="utf-8">
	<title>SuperSoccer Football Manager - Show What You Know</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<?php echo $this->Html->meta('icon', '/images/favicon.ico');?>
	<meta name="description" content="">
	<meta name="author" content="">
	<script>
	var api_url = "<?=$this->Html->url('/')?>";
	var base_url = "<?=$this->Html->url('/')?>";
	var est_expenses = 0; 
	var staffs = [];
	var canResetFormation = false;
		 function resetLineups(){
            $.each($("#the-formation").children(),function(t,l){
                if(typeof $(l).find('a').attr('no') !== 'undefined'){
                    $(l).remove();
                }
            });
            
            $("div.bench").removeClass('playerBoxChoosed');
            $("div.starter").removeClass('playerBoxChoosed');
            $("div.bench").removeClass('playerBoxSelected');
            $("div.starter").removeClass('playerBoxSelected');
            $("#draggable").hide();
            //show slots for subs
            for(var i=0;i<17;i++){
                $("#p"+i+".slot").show();
            }
        }
	</script>

	<?php
	if(isset($ENABLE_OPTA)):
	?>
	<link rel="stylesheet" href="http://widget.cloud.opta.net/2.0/css/widgets.opta.css" type="text/css">
	<!--[if IE 9]>
		<link rel="stylesheet" type="text/css" href="http://widget.cloud.opta.net/2.0/css/ie9.widgets.opta.css" media="screen" />
	<![endif]-->
	<!--[if IE 8]>
		<link rel="stylesheet" type="text/css" href="http://widget.cloud.opta.net/2.0/css/ie8.widgets.opta.css" media="screen" />
	<![endif]-->
	<!--[if IE 7]>
		<link rel="stylesheet" type="text/css" href="http://widget.cloud.opta.net/2.0/css/ie7.widgets.opta.css" media="screen" />
	<![endif]-->
	<script type="text/javascript" src="http://widget.cloud.opta.net/2.0/js/widgets.opta.js"></script>
	<script type="text/javascript">
		var _optaParams = {
			custID:		'<?=$OPTA_CUSTOMER_ID?>',
			language:	'en',
		};

	</script>
	<?php endif;?>
	<!-- Le styles -->
	<?php echo $this->Html->css(
			  array('datatables/tables','ffl','superfish','fancybox/jquery.fancybox.css?v=2.1.5'),
		null,array('media'=>'all')); 
	?>
	<?php echo $this->Html->script(
	  array('jquery-1.9.1',
			'jquery-ui-1.10.3.custom.min',
			'hoverIntent',
			'superfish',
			'fancybox/jquery.fancybox.js?v=2.1.5',
			'datatables/jquery.dataTables',
			'datatables/DT_bootstrap',
			'datatables/tables',
	  ));
?>
	<!--[if IE 6]>
	<link rel="stylesheet" type="text/css" href="js/slider/themes/default/jquery.slider.ie6.css" />
	<?php echo $this->Html->css(
			  array('slider/themes/default/jquery.slider.ie6'
			  ),
				null,array('media'=>'all')); 
	?>
	<![endif]-->
	<!--[if gte IE 9]>
	  <style type="text/css">
	    .grad {
	       filter: none;
	    }
	  </style>
	<![endif]-->