<h3>
	User Analytics
</h3>

<div class="row">
	<!-- Unique Player Daily last 14 days-->
	<div class="unique_daily">
	</div>
	<!-- end of unique player daily -->
</div>
<div class="row">
	<!-- Unique Player Weekly last 4 weeks -->
	<div class="unique_weekly">
	</div>
	<!-- end of unique player Weeky last 12 months-->
</div>
<div class="row">
	<!-- Unique Player Monthly -->
	<div class="unique_monthly">
	</div>
	<!-- end of unique player monthly -->
</div>


<!--charts -->
<script>
$(function () {
	unique_user_daily();
	unique_user_weekly();
	unique_user_monthly();
	
});

function addChart(options){
		console.log(options);
        $(options.target).highcharts({
            title: {
                text: options.title,
                x: -20 //center
            },
           
            xAxis: {
                categories: options.categories,
                labels: {
                    rotation: -45,
                    align: 'right',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                title: {
                    text: options.yText
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
           
            
            series: [{
                name: options.xText,
                data: options.xValue
            }]
        });
    
}
function unique_user_daily(){
	api_call('<?=$this->Html->url("/analytics/unique_user_daily")?>',
			function(response){
				if(response.status==1){
					addChart({
						target:'.unique_daily',
						categories: response.data.categories,
						xValue: response.data.values,
						xText: "Tanggal",
						yText: "Total",
						title: 'Unique Players Daily'
					});
				}	
			});
}
function unique_user_weekly(){
	api_call('<?=$this->Html->url("/analytics/unique_user_weekly")?>',
			function(response){
				if(response.status==1){
					addChart({
						target:'.unique_weekly',
						categories: response.data.categories,
						xValue: response.data.values,
						xText: "Minggu/Tahun",
						yText: "Total",
						title: 'Unique Players Weekly'
					});
				}	
			});
}
function unique_user_monthly(){
	api_call('<?=$this->Html->url("/analytics/unique_user_monthly")?>',
			function(response){
				if(response.status==1){
					addChart({
						target:'.unique_monthly',
						categories: response.data.categories,
						xValue: response.data.values,
						xText: "Bulan/Tahun",
						yText: "Total",
						title: 'Unique Players Monthly'
					});
				}	
			});
}
</script>
<!-- end of charts -->

<?php echo $this->Html->script('jquery.dataTables.min');?>

<script>
	var start = 0;
	var data = [];
	function getdata(){
		api_call("<?=$this->Html->url('/merchandises/get_items/?start=')?>"+start,
			function(response){
				if(response.status==1){
					if(response.data.length > 0){
						for(var i in response.data){
							data.push([
									'<img src="<?=$pic_dir?>'+response.data[i].MerchandiseItem.pic+'"/>',
									response.data[i].MerchandiseItem.name,
									response.data[i].MerchandiseCategory.name,
									response.data[i].MerchandiseItem.price_currency,
									response.data[i].MerchandiseItem.price_credit,
									response.data[i].MerchandiseItem.price_money,
									response.data[i].MerchandiseItem.stock,
									'<a href="<?=$edit_url?>'+response.data[i].MerchandiseItem.id+'">Edit</a>'
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
				{ "sTitle": "" },
				{ "sTitle": "Name" },
				{ "sTitle": "Category" },
				{ "sTitle": "ss$" },
				{ "sTitle": "Credit" },
				{ "sTitle": "IDR"},
				{ "sTitle": "stock"},
				{ "sTitle": "Action"}
			]
		} );
	}
	//getdata();
</script>