<?php require_once __DIR__.'/../include/auth.php';
if(isset($_GET['table']) && isset($_GET['event'])){
	$times = $booking->get_atc_positions_time_event($_GET['event']);
	if($times){
		echo '<thead><tr><th>Position</th>';
		foreach ($times as $time) {
		    echo '<th>' . get_time($time["time"]) . '</th>';
		}
		echo '</tr></thead>';
	} else {
		echo '<thead><tr><th>No positions available</th></tr></thead>';
	}
	$position_names = $booking->get_atc_positions_name_event($_GET['event']);
	if ($position_names) {
	    foreach ($position_names as $name) {	
	        echo '<tr>';
	        if($booking->is_admin()){
	        	echo '<td onclick="edit_position(\''.$name["name"].'\')">';
	        } else {
	        	echo '<td>';
	        }
	        echo $name["name"] . '</td>';
	        for ($i = 0;$i<count($times);$i++) {
	        	$time = $times[$i];
	            $position = $booking->get_atc_position_event_by_name_b_time($_GET['event'], $name["name"], $time["time"]);
	            $user = $user_data;
	            $event = $booking->get_event($_GET['event']);
	            $span = 0;
				if($event && strtotime($time["time"])<strtotime($event["e_time"])){
					$j = $i;
					if($position){
						while($j<count($times) && $position["e_time"] != $times[$j]["time"]){
							$j+=1;
							$span+=1;
						}
						$i=$j-1;
					}
		            echo '<td colspan="'.$span.'" class="text-center">';
		            if($position && $user){
			            if($position["vid"] == 0){
			            	echo '<button type="button" class="btn btn-success" onclick="book('.$position["ID"].')">Book</button>';
			            } else if($booking->is_admin() || $user["vid"]==$position["vid"]){
			            	echo '<button type="button" class="btn btn-danger" onclick="remove('.$position["ID"].')">Booked by '.$position["vid"].'</button>';
			            } else {
			            	echo '<button type="button" class="btn btn-danger" onclick="alert("Not allowed to remove.");">Booked by '.$position["vid"].'</button>';
			            }
					} else {
						
							echo 'Not available for booking.';
					}
		            echo '</td>';
		       	}
	        }
	        echo '</tr>';
	    }
	}
} else if(isset($_GET['list']) && isset($_GET['event'])){
	echo json_encode($booking->get_atc_positions_event($_GET['event']));
} else if (isset($_POST['book']) && isset($_POST['id'])){
	$position = $booking->get_atc_position($_POST['id']);
	$user = $user_data;
	if($position && $user){
		if($_POST['book'] == 1){
			$booking->update_atc_position($position["ID"], $position["name"], $position["b_time"], $position["e_time"], $position["event"], $user["id"]);
			echo 'Successfully booked.';
		} else {
			if($booking->is_admin()  || $position["vid"] == $user["id"]){
				$booking->update_atc_position($position["ID"], $position["name"], $position["b_time"], $position["e_time"], $position["event"], 0);
				echo 'Successfully removed booking.';
			}
		}
	} else {
		echo 'Error while booking.'; 
	}
} else if (isset($_POST['new_position']) && isset($_POST['event']) && $booking->is_admin()){
	$position = $_POST['new_position'];
	$name = $position["name"];
	$list=[];
	$b_time = strtotime($position["b_time"]);
	$e_time = strtotime($position["e_time"]);
	$interval = $position["interval"];
	while ($b_time < $e_time){
		$booking->insert_atc_position($name, date("H:i:s",$b_time), date("H:i:s", $b_time+($interval*60)), $_POST['event']);
		$b_time+=$interval*60;
	}
} else if(isset($_POST['edit_position']) && isset($_POST['event']) && $booking->is_admin()){
	echo json_encode($booking->get_atc_positions_event_by_name($_POST['event'], $_POST['edit_position']));
} else if(isset($_POST['id']) && isset($_POST['type']) && isset($_POST['value'])){
	$position = $booking->get_atc_position($_POST['id']);
	if($position){
		if($_POST['type'] == "b_time"){
			$booking->update_atc_position($position["ID"], $position["name"], $_POST['value'], $position["e_time"], $position["event"], $position["vid"]);
		} else if($_POST['type'] == "e_time"){
			$booking->update_atc_position($position["ID"], $position["name"], $position["b_time"], $_POST['value'], $position["event"], $position["vid"]);
		}
	}
} else if (isset($_POST['delete']) && isset($_POST['id']) && $booking->is_admin()){
	$booking->delete_atc_position($_POST['id']);
	echo 'Successfully deleted position.';
}
?>