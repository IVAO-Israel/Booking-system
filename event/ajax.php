<?php require_once __DIR__.'/../include/auth.php';
if(!$booking->is_admin()){
	die('Forbidden');
}
if(isset($_GET['events'])){
	$events = $booking->get_all_events();
	echo json_encode($events);
} else if (isset($_GET['event'])){
	$event = $booking->get_event($_GET['event']);
	echo json_encode($event);
} else if (isset($_POST['id']) && isset($_POST['event'])){ 
	$event = $_POST['event'];
	$booking->update_event($_POST['id'], $event["name"], $event["date"], $event["b_time"], $event["e_time"], $event["active"]);
} else if (isset($_POST['new_event']) && isset($_POST['event'])){
	$event = $_POST['event'];
	$booking->update_event($_POST['id'], $event["name"], $event["date"], $event["b_time"], $event["e_time"], $event["active"]);
} else if(isset($_POST['delete']) && isset($_POST['id'])){
	$booking->delete_event($_POST['id']);
	echo 'Successfully deleted event.';
}
?>