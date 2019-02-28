#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

include ("account.php");

	$userdb = mysqli_connect($hostname, $username, $password, $db);
global $userdb;

if (mysqli_connect_errno())
{
	echo "failed to connect to MySQL: "."\n". mysqli_connect_error();
	exit();
}else
{
	echo "Successfully connected to MYSQL."."\n".PHP_EOL;
}

function auth ($user, $pass){
	
	global $userdb;
	$s = "SELECT * from testtable where username = \"$user\" && password = \"$pass\"";
	$t = mysqli_query($userdb, $s);


	if (mysqli_num_rows($t) == 0 )
	{
		echo "User and Password combination not found.".PHP_EOL;
		return false;
	}
	else {
		echo "Successfully Authenticated.".PHP_EOL;
		return true;

	}
}

	


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(isset($request['type']))
  {
 	 switch ($request['type'])
  {
   		 case "login":
			 auth($request['username'], $request['password']);
			 break;
			 
		default:
			echo "try again";
	
}
  }
 }
$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

