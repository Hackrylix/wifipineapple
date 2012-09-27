<?php

if(isset($_GET[start])){
	exec("wifi");
	header("Location: /index.php");
}

if(isset($_GET[stop])){
	exec("killall hostapd && ifconfig wlan0 down");
	header("Location: /index.php");
}
