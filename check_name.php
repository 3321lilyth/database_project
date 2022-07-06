<?php

session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{
	$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (!isset($_POST['Account']) || empty($_POST['Account'])){
		header("Location: index.php");
		exit();
	}
	$Account=$_REQUEST['Account'];
	$stmt=$conn->prepare("select u_account from user where u_account like binary :u_account");
	$stmt->execute(array('u_account' => $Account));

	if ($stmt->rowCount()==0){
		echo 'YES';
		exit();
	}else{
		echo 'NO';
		exit();
	}

}catch(Exception $e){
	# 卡!!!!沒有丟例外他怎麼進來的
	echo 'FAILED'; 
}
?>