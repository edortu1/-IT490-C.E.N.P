#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

include ("account.php");

	$userdb = mysqli_connect($hostname, $username, $db);
global $userdb;

function logger($statement)
{
    $logClient = new rabbitMQClient("logger.ini","testServer");
    $request = array();
    $request['type'] = "error";
    $request['LogMessage'] = $statement;
    file_put_contents('error.log',$request['LogMessage'], FILE_APPEND);
    $response = $logClient->publish($request);
}

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


	if (!$t || mysqli_num_rows($t) == 0 )
	{
		echo "User and Password combination not found.".PHP_EOL;
		$error = "User and Password combination not found.".PHP_EOL;
	echo $error;
	logger($error);
		return false;
	}
	else {
		header('Location:192.168.1.10:3000');
		echo "Successfully Authenticated.".PHP_EOL;
		return true;

	}
}


function signup ($user, $pass, $email){
    global $userdb;
    $s = "SELECT * from testtable where username = \"$user\" || email = \"$email\"";
    $t = mysqli_query($userdb, $s);
    
    if (!$t || mysqli_num_rows($t) >= 1)
    {
	echo "User/email is already on database.".PHP_EOL;
	return false;
    }
    else
    {
	$a = "INSERT INTO testtable(username,password,email) VALUES (\"$user\",\"$pass\",\"$email\")";
	mysqli_query($userdb, $a);
	echo "Successfully added User.".PHP_EOL;
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
		 case "signup":
signup($request['username'],$request['password'],$request['email']);
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

