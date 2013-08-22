<h3>Push Logs</h3>
<div>
	Below is the latest push from Opta. Refreshed every 10 seconds.
</div>
<div id="logstable" class="row">
	
</div>
<div class="row">
	<a class="button" href="<?=$this->Html->url('/pushlogs/show_list')?>">Show All Logs</a>
	<a class="button" href="<?=$this->Html->url('/pushlogs/performance')?>">Performance</a>
</div>
<script type="text/template" id="tpl-logstable">
    <table width="100%">
		<tr>
			<td>id</td>
			<td>Time</td>
			<td>Source</td>
			<td>Feed Type</td>
			<td>Saved File</td>
		</tr>
		<%
			for(var i in data){
		%>
		<tr>
			<td><%=data[i].Pushlogs.id%></td>
			<td><%=data[i].Pushlogs.push_date%></td>
			<td><%=data[i].Pushlogs.productionServer%></td>
			<td><%=data[i].Pushlogs.feedType%></td>
			<td><%=data[i].Pushlogs.saved_file%></td>
		</tr>
		<%
			}
		%>
	</table>
</script>
<script>
function getLogs(interval,last_id){
	interval = (typeof last_id === 'undefined') ? 10000 : interval;
	last_id = (typeof last_id === 'undefined') ? 0 : last_id;

	api_call('<?=$this->Html->Url("/pushlogs/logs")?>',
			 function(response){
			 	if(response.status==1){
		 			render_view('#tpl-logstable','#logstable',{data:response.data});
		 		}
			 	setTimeout(function(){
			 		getLogs(interval,last_id);
			 	},interval);
			 });
}
getLogs();
</script>