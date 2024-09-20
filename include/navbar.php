<?php
	require_once __DIR__.'/booking.php';
?>
<nav class="navbar navbar-expand-sm bg-light">
  <div class="container-fluid">
    <ul class="navbar-nav">
    	<li class="nav-item">
    	<img src="<?php echo $baseurl.'include/logo.png';?>" height="50" width="50"/>
    	</li>
    </ul>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $baseurl.'atc';?>">ATC booking</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="<?php echo $baseurl.'pilot';?>">Flight booking</a>
      </li>
      <?php if($booking->is_admin()){?>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $baseurl.'event';?>">Event administration</a>
      </li>
      <?php }?>
    </ul>
    <ul class="navbar-nav ms-auto">
    	<li class="nav-item">
    	<?php if (isset($_COOKIE['access_token']) && time() < $_COOKIE['expires_in']) {
			$user = $booking->fetch_data($_COOKIE['access_token'], $booking->api_endpoints["userinfo_endpoint"]);
			if($user){ ?>
    		<a class="nav-link" href="<?php echo $baseurl.'admin/odjava.php';?>"><?php echo $user["firstName"].' '.$user["lastName"];?></a>
    		<?php }} else {?>
    			<a class="nav-link" href="<?php $auth_url = $oauth_endpoint . '?' . http_build_query([
        'response_type' => 'code',
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'scope' => 'email profile',
        'access_type' => 'offline',   // Request a refresh token
        'prompt' => 'consent'
    ]); echo $auth_url;?>">Login</a>
    		<?php }?>
        </li>
    </ul>
  </div>
</nav>