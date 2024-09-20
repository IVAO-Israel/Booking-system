<?php require_once __DIR__.'/../include/auth.php';?>
<!DOCTYPE html>
<html>
<head>
<?php require_once __DIR__.'/../include/head.php';?>
<title>ATC booking</title>
<script>
	function select_event(id){
		var url = "<?php echo $baseurl.'atc/';?>";
		window.location= url+id;
	}
</script>
<style>
#atc_positions, #loading_spinner, #error_event{
	display:none;
}
</style>
</head>
<body>
<?php require_once __DIR__.'/../include/navbar.php';?>
<h2 class="text-center">ATC booking</h2>

<?php
if(isset($_GET['event'])){
	require_once __DIR__.'/event.php';
} else {
?>

<div class="container mx-auto bg-light">
<table class="table table-bordered table-hover">
<thead>
<tr><th>Name</th><th>Date</th><th>Time</th>
<?php if($booking->is_admin()){
	echo '<th>Active</th>';
}?>
</tr>
</thead>
<tbody>
<?php 
	$events = $booking->get_upcoming_events();
	if($events){
		foreach($events as $event){
			echo '<tr><td onclick="select_event('.$event["ID"].')">';
			echo $event["name"];
			echo '</td><td onclick="select_event('.$event["ID"].')">';
			echo get_date($event["date"]);
			echo '</td><td onclick="select_event('.$event["ID"].')">';
			echo get_time($event["b_time"]).'-'.get_time($event["e_time"]);
			if($booking->is_admin()){
				echo '<td>';
				if($event["active"]){
					echo 'Yes';
				} else {
					echo 'No';
				}
				echo '</td>';
			}
			echo '</tr>';
		}
	} else{
		echo '<tr>';
		if($booking->is_admin()){
			echo '<td colspan="4" class="text-center">';
		} else {
			echo '<td colspan="3" class="text-center">';
		}
		echo 'No upcoming events.</td></tr>';
	}
?>
</tbody>
</table>
</div>
<?php }?>
<?php require_once __DIR__.'/../include/footer.php';?>
</body>
</html>