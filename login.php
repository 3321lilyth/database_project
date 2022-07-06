<?php
	session_start();
	$_SESSION['Authenticated']=false;
	$dbservername='localhost';
	$dbname='DBproject_uber';
	$dbusername='DBproject_uber';
	$dbpassword='DBproject_uber';
	try{
		if (!isset($_POST['Account']) || !isset($_POST['password'])){
			header("Location: index.php");
			// echo "?????????????";
			exit();
		}
		if (empty($_POST['Account']) || empty($_POST['password']))
			throw new Exception('Please input user Account and Password.');

		$Account=$_POST['Account'];
		$password=$_POST['password'];
		$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);

		# set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt=$conn->prepare("select u_account, u_password, u_salt, u_phone, u_latitude,
			 u_longitude, u_money, u_name from user where u_account = :Account COLLATE utf8mb4_bin");
		$stmt->execute(array('Account' => $Account));

		if ($stmt->rowCount() == 1){       # 這邊是1而不是0，因為只能有一筆
			$row = $stmt->fetch();       # 用 fetch function 取出 query 後的結果

			# 檢查資料庫存的該帳號的password(也就是hash值) =? H(資料庫存的salt+使用者輸入的密碼)
			# 點點代表連接
			if ($row['u_password'] == hash('sha256',$row['u_salt'].$_POST['password'])){
				$_SESSION['Authenticated']=TRUE;      # 登入成功
				$_SESSION['u_account']=$row[0];
				$_SESSION['u_phone']=$row[3];
				$_SESSION['u_latitude']=$row[4];
				$_SESSION['u_longitude']=$row[5];
				$_SESSION['u_money']=$row[6];
				$_SESSION['u_name']= $row[7];

				//判斷該使用者是否已經有開店，結果直接船進去 nav.php
				
				header("Location: nav.php");
				exit();
			}
			else
				throw new Exception('Incorect password');
		}
		else
			throw new Exception('Login failed.');
	}
	catch(Exception $e){ #同 register.php
		$msg=$e->getMessage();
		session_unset(); 
		session_destroy(); 
		echo <<<EOT

		<!DOCTYPE html>
		<html>
			<body>
			<script>
			alert("$msg");
			window.location.replace("index.php");
			</script>
			</body>
		</html>
		EOT;
	}
?>