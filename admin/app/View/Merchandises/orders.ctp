<h3>
	Merchandises
</h3>
<div class="row">
	<a href="<?=$this->Html->url('/merchandises/create')?>" class="button">Add Merchandise</a>
	<a href="<?=$this->Html->url('/merchandises/categories')?>" class="button">Categories</a>
	<a href="<?=$this->Html->url('/merchandises/orders')?>" class="button">Purchase Orders</a>
</div>

<div class="row">
	<table 
		width="100%" border="0" cellspacing="0" cellpadding="0" 
		class="dataTable dataTableTeam" 
		id="tbl">

</table>
</div>

<?php echo $this->Html->script('jquery.dataTables.min');?>
<?php
$pic_dir = Configure::read('avatar_web_url')."merchandise/thumbs/2_";
$view_url = $this->Html->url('/merchandises/view_order/');
?>
<script>
	var start = 0;
	var data = [];
	function getdata(){
		api_call("<?=$this->Html->url('/merchandises/get_orders/?start=')?>"+start,
			function(response){
				if(response.status==1){
					if(response.data.length > 0){
						for(var i in response.data){
							var status = 'Pending';
							switch(response.data[i].MerchandiseOrder.n_status){
								case '1':
									status = 'Order Accepted, waiting for shipping';
								break;
								case '2':
									status = 'Delivered';
								break;
								case '3':
									status = 'Closed';
								break;
								case '4':
									status = 'Canceled';
								break;
								default:
									status = 'Pending';
								break;
							}
							data.push([
									response.data[i].MerchandiseOrder.po_number,
									response.data[i].MerchandiseOrder.first_name+' '+response.data[i].MerchandiseOrder.last_name,
									status,
									'<a href="<?=$view_url?>'+response.data[i].MerchandiseOrder.id+'">View</a>'
								]);
						}
						start = response.next_offset;
						$(".progress").html($(".progress").html()+'.');
						getdata();
					}else{
						//draw table
						draw_table();
						$(".progress").hide();
						
					}
				}
			});
	}
	function draw_table(){
		$('#tbl').dataTable( {
			"fnDrawCallback":function(){
				//initClickEvents();
			},

			"aaData": data,
			"aoColumns": [
				
				{ "sTitle": "PO" },
				{ "sTitle": "Customer" },
				{ "sTitle": "Status" },
				{ "sTitle": "Action"}
			]
		} );
	}
	getdata();
</script>