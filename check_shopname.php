<?php

session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{
	$shopname=$_REQUEST['shopname'];
	$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (!isset($_REQUEST['shopname']) || empty($_REQUEST['shopname'])){
		header("Location: index.php");
		exit();
	}

	$stmt=$conn->prepare("select s_name from shops where s_name like binary :s_name");
	$stmt->execute(array('s_name' => $shopname));

	if ($stmt->rowCount()==0){
		echo 'YES';
	}else{
		echo 'NO';
	}
}
catch(Exception $e){
	echo 'FAILED'; 
}
?>