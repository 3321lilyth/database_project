<?php
	# 打開 session
	session_start();
	$dbservername='localhost';		    # mysql的 domain name 或 IP
	$dbname='DBproject_uber';       # mysql 中的資料庫名稱
	$dbusername='DBproject_uber';   # 去連這個資料庫需要的帳號
	$dbpassword='DBproject_uber';   # 去連這個資料庫需要的密碼
	
	try {
		# 避免直接輸入網址
		if (!isset($_POST['s_name']) || !isset($_POST['s_category']) || !isset($_POST['s_latitude']) || !isset($_POST['s_longitude'])){
			header("Location: index.php");
			exit();
		}

		# 輸入為空的情況
		if (empty($_POST['s_name']))
			throw new Exception('Shop name field required.');
        if (empty($_POST['s_category']))
            throw new Exception('Shop Category field required.');
        if(empty($_POST['s_latitude']))
            throw new Exception('Shop latitude field required.');
        if(empty($_POST['s_longitude']))
            throw new Exception('Shop longitude field required.');

        # 用post取出輸入值
		$shopname = $_POST['s_name'];
		$category = $_POST['s_category'];
		$latitude = $_POST['s_latitude'];
		$longitude = $_POST['s_longitude'];
        $account = $_SESSION['u_account'];

		
        #經緯度格式不對
		if (!is_numeric($latitude) || $latitude > 90.0 || $latitude <-90.0)
			throw new Exception('Latitude format incorrect.');
		if (!is_numeric($longitude) || $longitude > 180.0 || $longitude <-180.0)
			throw new Exception('Longitude format incorrect.');
		
		
		# 產生PDO object : 給定代表連線的字串，就是前面的四個變數
		$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
		
		# set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		#看店名有沒有重複
		$stmt=$conn->prepare("select s_name from shops where s_name = :s_name  COLLATE utf8mb4_bin");
		$stmt->execute(array('s_name' => $shopname));

		if ($stmt->rowCount() == 0)	{               # 店名沒有重複
			# 用prepare statement 將shop data存到資料庫裡
			$stmt=$conn->prepare("insert into shops (s_name, s_type, s_latitude, s_longitude, u_account) 
				value (:name, :type, :latitude ,:longitude, :account) ");
			$stmt->execute(array('name' => $shopname, 'type' => $category, 'latitude' => $latitude, 
                                    'longitude' => $longitude, 'account' => $account));
			
			# 設定session
			echo <<< EOT
			<!DOCTYPE html>
				<html>
				<body>
				<script>
					alert("Open a shop successfully!!");   <!-- 顯示畫面說建立成功-->
					window.location.replace("nav.php");        <!-- 導到nav.php-->
				</script> 
				</body> 
			</html>

			EOT;
			exit();
		}else  # 帳號被用過
			throw new Exception("Shop name has been registered!!");
	
	}catch(Exception $e){
		$msg=$e->getMessage();      # 儲存 exception中的錯誤訊息
		echo <<< EOT

		<!DOCTYPE html>
		<html>
		<body>
		<script>
				alert("$msg");
				window.location.replace("nav.php");
		</script>
		</body>
		</html>

		EOT;
	}

?>