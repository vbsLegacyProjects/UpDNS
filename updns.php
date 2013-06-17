<?php
echo 'Starting uPDns...'."\n";
echo 'IP='.$ip."\n";
if(!isset($ip)||trim($ip)==''){
	echo ('No IP set!');
	die(16);
}
define('UPDIR',str_replace('\\','/',dirname(__FILE__)).'/');
require_once(UPDIR.'updns.config.php');
$base_url= "http://".CPANEL_HOST.':'.CPANEL_PORT.'/';
$ch=curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION		, true);
curl_setopt($ch, CURLOPT_COOKIEFILE			, UPDIR.'updns.cookies');
curl_setopt($ch, CURLOPT_COOKIEJAR			, UPDIR.'updns.cookies');
curl_setopt($ch, CURLOPT_RETURNTRANSFER		, 1);

function cpanelLogin(){
	echo 'cpanelLogin > Starting... ';
	global $ch;
	global $base_url;
	curl_setopt($ch, CURLOPT_POST		, true); 
	curl_setopt($ch, CURLOPT_POSTFIELDS	, 'user='.CPANEL_USER.'&pass='.CPANEL_PASSWORD.'&login=');
	curl_setopt($ch, CURLOPT_URL		, $base_url.'/login/');
	$page=curl_exec($ch);
	curl_setopt($ch, CURLOPT_POSTFIELDS	, '');
	curl_setopt($ch, CURLOPT_POST		, false); 
	echo 'done'."\n";
	return $page;
}

function cpanelListDnsConfig(){
	global $ch;
	global $base_url;
	$list_url='json-api/cpanel?cpanel_jsonapi_version=2&cpanel_jsonapi_module=ZoneEdit&cpanel_jsonapi_func=fetchzone&domain='.CPANEL_DOMAIN;
	curl_setopt($ch, CURLOPT_URL		, $base_url.$list_url);
	$page=curl_exec($ch);
	return json_decode($page,true);
}

function cpanelUpdateLine($data,$ip){
	global $ch;
	global $serialnum;
	global $base_url;
	$update_url='json-api/cpanel?cpanel_jsonapi_version=2&cpanel_jsonapi_module=ZoneEdit&cpanel_jsonapi_func=edit_zone_record&domain='.CPANEL_DOMAIN.'&line='.$data['line'].'&class='.$data['class'].'&type='.$data['type'].'&name='.$data['name'].'&ttl='.$data['ttl'].'&serialnum='.$serialnum.'&address='.$ip.'&cache_fix='.@mktime();
	curl_setopt($ch, CURLOPT_URL		, $base_url.$update_url);
	$page=curl_exec($ch);
	return $page;
}

echo 'Starting...'."\n";
curl_setopt($ch, CURLOPT_URL, $base_url);
$page=curl_exec($ch);
if(stripos($page,'cPanel Login')!==false){
	$page=cpanelLogin();
}
if(stripos($page,'cPanel Login')!==false){
	echo 'ERROR! Incorrect login... aborting'."\n";
	die(1);
}else{
	echo 'Login successfull'."\n";
}

$list=cpanelListDnsConfig();
if(!is_array($list) || !isset($list['cpanelresult']) || !isset($list['cpanelresult']['data'])){
	echo 'ERROR! Could not convert dns list... aborting'."\n";
	die(2);
}
$list=$list['cpanelresult']['data'];
$serialnum=$list[0]['serialnum'];
echo 'S serialnum='.$serialnum."\n";

echo "\n";

echo 'Scanning for entry names... '."\n";
foreach($update_names as $k=>$v){
	echo '#  '.$v."\n";
}
echo "\n";

$todo=array();
echo "Listing dns data (A,CNAME)...\n";
foreach($list[0]['record'] as $k=>$v){
	if(isset($v['type']) && in_array($v['type'],array('A','CNAME'))){
		echo 'N='.$v['name'].' A='.$v['address'].' R='.$v['record'].' L='.$v['line'].' TTL='.$v['ttl'];
		if(in_array($v['name'],$update_names)){
			echo ' **';
			$todo[]=$v;
		}
		echo "\n";
	}
}
echo "\n";

echo count($todo).' lines to update to [IP='.$ip.']'."\n";
if(count($todo)==0){
	echo 'Done, nothing to do'."\n";
	die(0);
}

foreach($todo as $k=>$v){
	if($v['address']!==$ip){
		echo '! L='.$v['line']."\n";
		$result=cpanelUpdateLine($v,$ip);
		$result=json_decode($result,true);
		if(isset($result['cpanelresult']['data'][0]['result']['newserial'])){
			$serialnum=$result['cpanelresult']['data'][0]['result']['newserial'];
			echo 'S serialnum='.$serialnum."\n";
		}
	}else{
		echo '- L='.$v['line'].' ok'."\n";
	}
}
