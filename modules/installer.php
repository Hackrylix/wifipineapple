<?php
$status = "done";

if(isset($_GET[status])){
	echo $status; exit(0);
}

exec("echo \"sh /pineapple/modules/installer.sh $_GET[name] $_GET[version] $_GET[dest] $_GET[md5]\" | at now");



?>