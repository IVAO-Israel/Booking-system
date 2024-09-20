<?php require_once __DIR__.'/../include/auth.php';
if(!$booking->is_admin()){
die('Forbidden');
}
?>
<!DOCTYPE html>
<html>
<head>
<?php require_once __DIR__.'/../include/head.php';?>
<title>Events</title>
<script>
	$(document).ready(()=>{
		get_events();
		$("#modal").one('hide.bs.modal', ()=>get_events());
	});
	var evnts= [];
	var url = "<?php echo $baseurl;?>"+"event/ajax";
	async function get_events(){
		$("#tbody").html('<tr><td colspan="5"><div class="spinner-border"></div></td></tr>');
		var data = await $.get(url, {"events":true});
		if(data){
			evnts = JSON.parse(data);
			var html = "";
			if(evnts.length > 0){
				for(var evnt of evnts){
					html+='<tr><td onclick="edit_event('+evnt.ID+')">'+evnt.name+'</td><td onclick="edit_event('+evnt.ID+')">'+evnt.date+'</td><td onclick="edit_event('+evnt.ID+')">'+get_time(evnt.b_time)+'-'+get_time(evnt.e_time)+'</td><td onclick="edit_event('+evnt.ID+')">';
					if(evnt.active==1){
						html+="Yes";
					} else {
						html+="No";
					}
					html+='</td><td><button type="button" class="btn btn-danger" onclick="delete_event('+evnt.ID+')">Delete</button></td></tr>';
				}
			} else {
				html+='<tr><td colspan="4" class="text-center">No events.</td></tr>';
			}
			$("#tbody").html(html);
		}
	}
	async function edit_event(id){
		var data = await $.get(url, {"event":id});
		if(data){
			var evnt = JSON.parse(data);
			$("#modal").modal('show');
			$("#event_name").val(evnt.name);
			$("#event_date").val(evnt.date);
			$("#b_time").val(evnt.b_time);
			$("#e_time").val(evnt.e_time);
			$("#active").val(evnt.active).change();
			$("#modal_submit").off('click').on('click', ()=>save_event(id));
		}
	}
	async function save_event(id=0){
		var evnt = {"name":$("#event_name").val(), "date":$("#event_date").val(), "b_time":$("#b_time").val(), "e_time":$("#e_time").val(), "active":$("#active").val()};
		if(id>0){
			var data = await $.post(url, {"id":id, "event":evnt});
		} else {
			var data = await $.post(url, {"new_event":true, "event":evnt});
		}
		$("#modal").modal('hide');
		await get_events();
	}
	function new_event(){
		$("#modal").modal('show');
		$("#event_name").val("");
		$("#event_date").val("");
		$("#b_time").val("");
		$("#e_time").val("");
		$("#active").val(1).change();
		$("#modal_submit").off('click').on('click', ()=>save_event());
	}
	async function delete_event(id){
		var evnt = get_event(id);
		if(evnt){
			if(confirm("Do you want to delete event "+evnt.name+"?")){
				var data = await $.post(url, {"delete":true, "id":id});
				alert(data);
				get_events();
			}
		}
	}
	function get_event(id){
		for (var evnt of evnts){
			if(evnt.ID==id){
				return evnt;
			}
		}
		return false;
	}
	function get_time(time){
		return time.substring(0, 5);
	}
</script>
<style>

</style>
</head>
<body>
<?php require_once __DIR__.'/../include/navbar.php';?>
<h2 class="text-center">Event administration</h2>

<div class="modal" id="modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_title">New event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
		<div class="row">
			<label for="event_name" class="form-label col">Name:</label>
			<div class="col"><input type="text" class="form-control col" id="event_name"></div>
		</div>
		<div class="row">
			<label for="event_date" class="form-label col">Date:</label>
			<div class="col"><input type="date" class="form-control col" id="event_date"></div>
		</div>
		<div class="row">
			<label for="b_time" class="form-label col">Start time:</label>
			<div class="col"><input type="time" class="form-control col" id="b_time"></div>
		</div>
		<div class="row">
			<label for="e_time" class="form-label col">End time:</label>
			<div class="col"><input type="time" class="form-control col" id="e_time"></div>
		</div>
		<div class="row">
			<label for="active" class="form-label col">Active:</label>
			<div class="col"><select class="form-control col" id="active">
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select></div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="modal_submit">Save changes</button>
      </div>
    </div>
  </div>
</div>

<div class="container mx-auto bg-light">
<button type="button" class="btn btn-success" onclick="new_event()">Add new event</button>
<table class="table table-bordered table-hover">
<thead>
<tr><th>Name</th><th>Date</th><th>Time</th><th>Active</th><th></th></tr>
</thead>
<tbody id="tbody">
<?php 
?>
</tbody>
</table>
</div>
<?php require_once __DIR__.'/../include/footer.php';?>
</body>
</html>