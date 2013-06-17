<?php
@date_default_timezone_set('Europe/Bucharest');
if(
       (!isset($_GET['key']) || $_GET['key']!=='your-key-here')
    && (!isset($_POST['key']) || $_POST['key']!=='your-key-here')
){
	die('ERR:NO_AUTH');
}
if(!isset($_GET['ip'])){
	die('NO_IP');
}
if(isset($_GET['ip']) &&( trim($_GET['ip']=='') || $_GET['ip']=='AUTO')){
	$_GET['ip']=$_SERVER["REMOTE_ADDR"];
}
$ip=$_GET['ip'];
$agent='?';
if(isset($_GET['agent'])){
	$agent=$_GET['agent'];
}
file_put_contents('last.log','date='.@date('Y-m-d h:i:s')."\n"
.'timestamp='.@mktime()."\n"
.'ip='.$ip."\n"
.$agent."\n"
);
require_once('updns.php');
