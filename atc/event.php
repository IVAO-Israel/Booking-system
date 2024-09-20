<?php
$event = $booking->get_event($_GET['event']);
if(!$event){
	echo "<div class='alert alert-danger'>Event doesn't exist.</div>";
} else if(!$event["active"] || strtotime($event['date'].' '.$event['e_time']) < strtotime("now")){
	echo "<div class='alert alert-success'>Event is finished.</div>";
} else {
?>
<script>
	var url = "<?php echo $baseurl.'atc';?>";
	var positions = [];
	function unselect_event(){
		window.location= url;
	}
	$(document).ready(()=>{
		get_positions();
		$("#modal").one('hide.bs.modal', ()=>get_positions());
	});
	async function get_positions(){
		$("#positions").html('<div class="spinner-border"></div>');
		var html = await $.get(url+"/ajax.php", {"table":true, "event":<?php echo $_GET['event'];?>});
		var data = await $.get(url+"/ajax.php", {"list":true, "event":<?php echo $_GET['event'];?>});
		if(data){
			positions= JSON.parse(data);
		}
		$("#positions").html(html);
	}
	async function book(id){
		var data = await $.post(url+"/ajax", {"book":1, "id":id});
		alert(data);
		get_positions();
	}
	function get_position_info(id){
		for(position of positions){
			if(position.ID==id){
				return position;
			}
		}
		return false;
	}
	async function remove(id){
		var position = get_position_info(id);
		if(position){
			if(confirm("Do you want to remove booking for "+position.name+" "+position.b_time+"-"+position.e_time+"?")){
				var data = await $.post(url+"/ajax", {"book":0, "id":id});
				alert(data);
				get_positions();
			}
		}
	}
	function new_position(){
		$("#modal").modal('show');
		$("#modal_title").text("New position");
		$("#position_name").val("").attr('disabled', false);
		$("#b_time").val($("#event_b_time").val());
		$("#e_time").val($("#event_e_time").val());
		$("#b_time_div").show();
		$("#e_time_div").show();
		$("#interval_div").val("30").change().show();
		$("#div_table").hide();
		$("#modal_submit").off('click').on('click', ()=>save_new());
	}
	async function save_new(){
		var position = {"name":$("#position_name").val(), "b_time":$("#b_time").val(), "e_time":$("#e_time").val(), "interval":$("#interval").val()};
		var data = await $.post(url+"/ajax", {"new_position":position, "event":<?php echo $_GET['event'];?>});
		$("#modal").modal('hide');
		get_positions();
	}
	async function edit_position(name){
		var data = await $.post(url+"/ajax", {"edit_position":name, "event":<?php echo $_GET['event'];?>});
		if(data){
			var positions = JSON.parse(data);
			$("#modal").modal('show');
			$("#modal_title").text("Edit "+name);
			$("#position_name").val(name).attr('disabled', true);
			$("#b_time_div").hide();
			$("#e_time_div").hide();
			$("#interval_div").hide();
			$("#div_table").show();
			$("#modal_submit").off('click').on('click', ()=>{$("#modal").modal('hide');});
			var html = "";
			if(positions && positions.length>0){
				for(let position of positions){
					html+='<tr><td>';
					html+='<input type="time" data-id="'+position.ID+'" value="'+position.b_time+'" onchange="change_position(\''+name+'\', dataset.id, \'b_time\' , this.value)">';
					html+='</td><td>';
					html+='<input type="time" data-id="'+position.ID+'" value="'+position.e_time+'">';
					html+='</td><td><button type="button" class="btn btn-danger" onclick="delete_position('+position.ID+')">Delete</button></td></tr>';
				}
			} else {
				$("#modal").modal('hide');
			}
			$("#modal_table").html(html);
		}
	}
	async function change_position(name, id, type, data){
		var data = await $.post(url+"/ajax", {"id":id, "type":type, "value":data});
		await edit_position(name);
	}
	async function delete_position(id){
		var position = get_position_info(id);
		if(position){
			if(confirm("Do you want to delete position "+position.name+" "+position.b_time+"-"+position.e_time+"?")){
				var data = await $.post(url+"/ajax", {"delete":true, "id":id});
				alert(data);
				await get_positions();
				edit_position(position.name);
			}
		}
	}
</script>
<div class="modal" id="modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_title">New position</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
		<div class="row">
			<label for="position_name" class="form-label col">Position:</label>
			<div class="col"><input type="text" class="form-control col" id="position_name"></div>
		</div>
		<div class="row" id="b_time_div">
			<label for="b_time" class="form-label col">Start time:</label>
			<div class="col"><input type="time" class="form-control col" id="b_time"></div>
		</div>
		<div class="row" id="e_time_div">
			<label for="e_time" class="form-label col">End time:</label>
			<div class="col"><input type="time" class="form-control col" id="e_time"></div>
		</div>
		<div class="row" id="interval_div">
			<label for="interval" class="form-label col">Interval:</label>
			<div class="col"><select class="form-control col" id="interval">
				<option value="15">15 minutes</option>
				<option value="30">30 minutes</option>
				<option value="60">60 minutes</option>
			</select></div>
		</div>
      </div>
      <div id="div_table">
      	<table class="table table-boredered table-hover">
      		<thead><tr><th>Start time</th><th>End time</th><th></th></tr></thead>
      		<tbody id="modal_table"></tbody>
      	</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="modal_submit">Save changes</button>
      </div>
    </div>
  </div>
</div>
<div class="container mx-auto bg-light">
<!--<button type="button" class="btn btn-primary" onclick="unselect_event()">Return back to the list</button>-->
<div class="row">
	<label for="event_name" class="form-label col">Event name:</label>
	<div class="col"><input type="text" class="form-control col" id="event_name" value="<?php echo $event["name"]?>" disabled></div>
</div>
<div class="row">
	<label for="event_date" class="form-label col">Date:</label>
	<div class="col"><input type="date" class="form-control col" id="event_date" value="<?php echo $event["date"]?>" disabled></div>
</div>
<div class="row">
	<label for="event_b_time" class="form-label col">Event start:</label>
	<div class="col"><input type="time" class="form-control col" id="event_b_time" value="<?php echo $event["b_time"]?>" disabled></div>
</div>
<div class="row">
	<label for="event_e_time" class="form-label col">Event end:</label>
	<div class="col"><input type="time" class="form-control col" id="event_e_time" value="<?php echo $event["e_time"]?>" disabled></div>
</div>
<?php if($booking->is_admin()){ ?>
<div>
<button type="button" class="btn btn-success" onclick="new_position()">Add new position</button>
</div>
<?php }?>
<table class="table table-bordered table-hover" id="positions">
</table>
</div>
<?php }?>