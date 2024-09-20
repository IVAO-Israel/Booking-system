<?php
class booking{
	private $server = 'localhost';
	private $username = 'root';
	private $password = '';
	private $database = 'ilivao_booking';
	public $url = "http://localhost/ivao/";
	public $client_id = '4f7c4cc2-34b1-4665-b78a-f5158154a95c';
	public $client_secret = 'Zxfo6d073nHuYVnn6SuYC28kmnVKTcxL';
	public $oauth_uri;
	private $openid_configuration_uri = 'https://api.ivao.aero/.well-known/openid-configuration';
	public $openid_configuration;
	public $db;
	public $api_endpoints = [];
	
	function __construct(){
		$this->oauth_uri = $this->url.'oauth.php';
		$this->db= mysqli_connect($this->server, $this->username, $this->password, $this->database);
		$this->openid_configuration = $this->get_configuration($this->openid_configuration_uri);
		$this->api_endpoints["userinfo_endpoint"] = $this->openid_configuration["userinfo_endpoint"];
		$this->api_endpoints["ATCPositions"] = "https://api.ivao.aero/v2/ATCPositions/";
	}
	
	function get_configuration($url){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $response = curl_exec($ch);
	    curl_close($ch);
	    return json_decode($response, true);
	}
	function fetch_data($access_token, $endpoint, $header=[]) {
		// Initialize cURL
	    $ch = curl_init();
	    
	    // Set the URL and request headers
	    curl_setopt($ch, CURLOPT_URL, $endpoint);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $full_header = ['Authorization: Bearer ' . $access_token];
	    if(count($header)>0){
	    	foreach($header as $h){
	    		array_push($full_header, $h);
	    	}
	    }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $full_header);
	    // Execute cURL request
	    $response = curl_exec($ch);
	    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
	    curl_close($ch);
	    
	    // Handle errors
	    if ($http_code != 200) {
	        echo 'Error: ' . $http_code;
	        return null;
	    }
	    return json_decode($response, true);
	}
	
	//ATC positions
	function insert_atc_position($name, $b_time, $e_time, $event){
		$stmt = $this->db->prepare("INSERT INTO positions (name, b_time, e_time, event) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("sssi", $name, $b_time, $e_time, $event);
		$stmt->execute();
	}
	function get_atc_position($id){
		$stmt = $this->db->prepare("SELECT * FROM positions WHERE ID=?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return false;
	}
	function get_atc_position_event_by_name_b_time($event, $name, $b_time){
		$stmt = $this->db->prepare("SELECT * FROM positions WHERE event=? AND name=? AND b_time=?");
		$stmt->bind_param("iss", $event, $name, $b_time);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return false;
	}
	function get_atc_positions_event_by_name($event, $name){
		$stmt = $this->db->prepare("SELECT * FROM positions WHERE event=? AND name=? ORDER BY b_time");
		$stmt->bind_param("is", $event, $name);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			return $result->fetch_ALL(MYSQLI_ASSOC);
		}
		return false;
	}
	function get_atc_positions_event($event){
		$stmt = $this->db->prepare("SELECT * FROM positions WHERE event=?");
		$stmt->bind_param("i", $event);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function get_atc_positions_name_event($event){
		$stmt = $this->db->prepare("SELECT DISTINCT name FROM positions WHERE event=?");
		$stmt->bind_param("i", $event);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function get_atc_positions_time_event($event){
		$stmt = $this->db->prepare("SELECT DISTINCT time FROM (SELECT b_time AS time FROM positions WHERE event=? UNION SELECT e_time AS time FROM positions WHERE event=?) AS time ORDER BY time;");
		$stmt->bind_param("ii", $event, $event);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function update_atc_position($id, $name, $b_time, $e_time, $event, $vid){
		if($id > 0){
			$stmt = $this->db->prepare("UPDATE positions SET name=?, b_time=?, e_time=?, event=?, vid=? WHERE ID=?");
			$stmt->bind_param("sssiii", $name, $b_time, $e_time, $event, $vid, $id);
			$stmt->execute();
		}
	}
	function delete_atc_position($id){
		if($id > 0){
			$stmt = $this->db->prepare("DELETE FROM positions WHERE ID=?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
	}
	function delete_atc_positions_event($event){
		if($event > 0){
			$stmt = $this->db->prepare("DELETE FROM positions WHERE event=?");
			$stmt->bind_param("i", $event);
			$stmt->execute();
		}
	}

	//EVENTS
	function insert_event($name, $date, $b_time, $e_time, $active){
		$stmt = $this->db->prepare("INSERT INTO events (name, date, b_time, e_time, active) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssi", $name, $date, $b_time, $e_time, $active);
		$stmt->execute();
	}
	function get_event($id){
		$stmt = $this->db->prepare("SELECT * FROM events WHERE ID=?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return false;
	}
	function get_all_events(){
		$result = $this->db->query("SELECT * FROM events ORDER BY date DESC");
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function get_upcoming_events(){
		$result = $this->db->query("SELECT * FROM events WHERE date >= CURDATE() AND active=true");
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function update_event($id, $name, $date, $b_time, $e_time, $active){
		if($id > 0){
			$stmt = $this->db->prepare("UPDATE events SET name=?, date=?, b_time=?, e_time=?, active=? WHERE ID=?");
			$stmt->bind_param("ssssii", $name, $date, $b_time, $e_time, $active, $id);
			$stmt->execute();
		}
	}
	function delete_event($id){
		if($id > 0){
			$stmt = $this->db->prepare("DELETE FROM events WHERE ID=?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$this->delete_atc_positions_event($id);
		}
	}
	
	//FLIGHTS
	function insert_flight($callsign, $departure_id, $departure_time, $arrival_id, $arrival_time, $route, $event){
		$stmt = $this->db->prepare("INSERT INTO flights (callsign, departure_id, departure_time, arrival_id, arrival_time, route, event) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssssi", $callsign, $departure_id, $departure_time, $arrival_id, $arrival_time, $route, $event);
		$stmt->execute();
	}
	function get_flight($id){
		$stmt = $this->db->prepare("SELECT * FROM flights WHERE ID=?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return false;
	}
	function get_flights_event($event){
		$stmt = $this->db->prepare("SELECT * FROM flights WHERE event=?");
		$stmt->bind_param("i", $event);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			return $result->fetch_all(MYSQLI_ASSOC);
		}
		return false;
	}
	function update_flight($id, $callsign, $departure_id, $departure_time, $arrival_id, $arrival_time, $route, $event, $vid){
		if($id > 0){
			$stmt = $this->db->prepare("UPDATE flights SET callsign=?, departure_id=?, departure_time=?, arrival_id=?, arrival_time=?, route=?, event=? vid=? WHERE ID=?");
			$stmt->bind_param("ssssssiii", $callsign, $departure_id, $departure_time, $arrival_id, $arrival_time, $route, $event, $vid, $id);
			$stmt->execute();
		}
	}
	function delete_flight($id){
		if($id > 0){
			$stmt = $this->db->prepare("DELETE FROM flights WHERE ID=?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
	}

	//ADMINS
	function insert_admin($vid, $active){
		$stmt = $this->db->prepare("INSERT INTO admins (vid, active) VALUES (?, ?)");
		$stmt->bind_param("ii", $vid, $active);
		$stmt->execute();
	}
	function get_admin($vid){
		$stmt = $this->db->prepare("SELECT * FROM admins WHERE vid=?");
		$stmt->bind_param("i", $vid);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows == 1){
			return $result->fetch_assoc();
		}
		return false;
	}
	function update_admin($id, $vid, $active){
		if($id > 0){
			$stmt = $this->db->prepare("UPDATE admins SET vid=?, active=? WHERE ID=?");
			$stmt->bind_param("iii", $vid, $active, $id);
			$stmt->execute();
		}
	}
	function delete_admin($id){
		if($id > 0){
			$stmt = $this->db->prepare("DELETE FROM admins WHERE ID=?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
	}
	function is_admin(){
		if (isset($_COOKIE['access_token']) && time() < $_COOKIE['expires_in']) {
			$user = $this->fetch_data($_COOKIE['access_token'], $this->api_endpoints["userinfo_endpoint"]);
			if($user){
				$admin = $this->get_admin($user["id"]);
				if($admin && $admin["active"]){
					return true;
				}
			}
		}
		return false;
	}
}
$booking = new booking();
$baseurl= $booking->url;

// Configuration
$client_id = $booking->client_id;
$client_secret = $booking->client_secret;
$redirect_uri = $booking->oauth_uri;

$configuration = $booking->openid_configuration;
$oauth_endpoint = $configuration["authorization_endpoint"];
$token_endpoint = $configuration["token_endpoint"];
$userinfo_endpoint = $booking->api_endpoints["userinfo_endpoint"];

// Function to fetch additional user data dynamically using the access token
function fetch_user_data($access_token, $endpoint){
	global $booking;
	return $booking->fetch_data($access_token, $endpoint);
}

function get_date($str){
	return date("j.n.Y.",strtotime($str));
}
function insert_date($str){
	return date("Y-m-d",strtotime($date));
}
function get_time($str){
	return date("G:i",strtotime($str));
}
function insert_time($str){
	return date("H:i:s",strtotime($str));
}
?>