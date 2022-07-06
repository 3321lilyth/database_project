<?php
	# 打開 session
	session_start();
	$_SESSION['Authenticated']=false; # 目前還沒註冊成功，所以 false
	$dbservername='localhost';		    # mysql的 domain name 或 IP
	$dbname='DBproject_uber';       # mysql 中的資料庫名稱
	$dbusername='DBproject_uber';   # 去連這個資料庫需要的帳號
	$dbpassword='DBproject_uber';   # 去連這個資料庫需要的密碼
	
	try {
		# 避免使用者是直接輸入網址連到register.php 而不是從 index.php輸入帳密進來的，應該不用列出全部就擋得住了
		if (!isset($_POST['Account']) || !isset($_POST['name'])){
			header("Location: index.php");
			exit();
		}
		# 如果是從index.php來的沒錯但帳密室空值，就讓使用者輸入
		if (empty($_POST['name']))
			throw new Exception('Please input name.');
		if (empty($_POST['phonenumber']))
			throw new Exception('Please input phone number.');
		if (empty($_POST['Account']))
			throw new Exception('Please input Account.');	
		if(empty($_POST['password']))
			throw new Exception('Please input password.');
		if(empty($_POST['re-password']))
			throw new Exception('Please input re-password.');
		if(empty($_POST['latitude']))
			throw new Exception('Please input latitude.');
		if(empty($_POST['longitude']))
			throw new Exception('Please input longitude.');
		
		
		# 用post取出帳密
		$name = $_POST['name'];
		$phonenumber=$_POST['phonenumber'];
		$Account=$_POST['Account'];
		$password=$_POST['password'];
		$repassword=$_POST['re-password'];
		$latitude=$_POST['latitude'];
		$longitude=$_POST['longitude'];
		
		# 輸入格是不對，卡
		if (!ctype_alnum($password)) # 限制只能输入英文字符和数字
			throw new Exception('Password format incorrect.');
		if (!ctype_alnum($Account)) # 限制只能输入英文字符和数字
			throw new Exception('Account format incorrect.');
		// if (!ctype_alpha($name)) # 限制只能输入英文
		// 	throw new Exception('User name format incorrect.');

		if (!ctype_digit($phonenumber) || strlen($phonenumber) != 10) //ctype_digit 開頭可以有0但不能是小數
			throw new Exception('Phone number format incorrect.');
		if (!is_numeric($latitude) || $latitude > 90.0 || $latitude <-90.0)
			throw new Exception('Latitude format incorrect.');
		if (!is_numeric($longitude) || $longitude > 180.0 || $longitude <-180.0)
			throw new Exception('Longitude format incorrect.');
			
		# 如果密碼跟確認密碼不相同
		if ($password != $repassword)
			throw new Exception('Password and Re-password is not match.');
		
		
		# 產生PDO object : 給定代表連線的字串，就是前面的四個變數
		$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
		
		# set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		# sql injection : 使用者輸入包含一些特殊指令，會讓PHP執行programer不想他做的事。詳細見本頁下面
		# 為了避免sql injection ，所有使用者輸入的值都要用 prepare statement 處理，不要連接成一個字串
		# prepare function 中的 "冒號XXX"就是變數，會在execute function 中被指定的變數取代。
		# 為何不直接在prepare function 裡面寫 $uname 而要多包一層?? 目的是將會導致 sql injection 的字元用其他東西取代
		# 而這兩句的目的是去查訊這個帳號有沒有被用過
		$stmt=$conn->prepare("select u_account from user where u_account = :u_account  COLLATE utf8mb4_bin");
		$stmt->execute(array('u_account' => $Account));

		if ($stmt->rowCount() == 0)	{               # 帳號沒有被用過
			$salt=strval(rand(1000,9999));          # 用rand產生salt並轉成字串，這樣會剛好4個字元，跟table設定的一樣
			$hashvalue=hash('sha256', $salt.$password);  # hash
			# 用prepare statement 將帳號、salt、hash存到資料庫裡
			$stmt=$conn->prepare("insert into user (u_password, u_name, u_phone, u_salt, u_account, u_latitude, u_longitude ) 
				values (:password, :name, :phonenumber, :salt, :Account, :latitude ,:longitude)");
			$stmt->execute(array('password' => $hashvalue, 'name' => $name, 'phonenumber' => $phonenumber,
				 'salt' => $salt, 'Account' => $Account, 'latitude' => $latitude, 'longitude' => $longitude));
			
			# 設定session
			$_SESSION['Authenticated']=true;
			$_SESSION['Username']=$name;
			echo <<< EOT
			<!DOCTYPE html>
				<html>
				<body>
				<script>
					alert("Create an account successfully.");   <!-- 顯示畫面說建立成功-->
					window.location.replace("index.php");        <!-- 導到index.php-->
				</script> 
				</body> 
			</html>

			EOT;
			exit();
		}else  # 帳號被用過
			throw new Exception("User account had been used.");
	
	}catch(Exception $e){
		$msg=$e->getMessage();      # 儲存 exception中的錯誤訊息
		// session_unset();            # 清除session 變數
		// session_destroy();          # 關閉 session
		echo <<< EOT

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