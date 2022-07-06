
<?php
	# 打開 session
	session_start();
	$dbservername='localhost';		    # mysql的 domain name 或 IP
	$dbname='DBproject_uber';       # mysql 中的資料庫名稱
	$dbusername='DBproject_uber';   # 去連這個資料庫需要的帳號
	$dbpassword='DBproject_uber';   # 去連這個資料庫需要的密碼
	
	try {
        $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!isset($_POST['mealname']) || !isset($_POST['price']) || !isset($_POST['quantity'])){
			header("Location: index.php");
			exit();
		}
        # 輸入為空的情況
		if (empty($_POST['mealname']))
            throw new Exception('meal name field required.');
        if (empty($_POST['price']))
            throw new Exception('price field required.');
        if(empty($_POST['quantity']))
            throw new Exception('quantity field required.');
        if($_FILES['file']['error'] != UPLOAD_ERR_OK)
            throw new Exception('Need to upload food picture');

        $mealname = $_POST['mealname'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $s_name = $_SESSION['s_name'];
        if (!preg_match("/^[1-9][0-9]*$/" ,$price) || $price < 0)
            throw new Exception('price format incorrect.');
        if (!preg_match("/^[1-9][0-9]*$/" ,$quantity) || $quantity < 0)
            throw new Exception('quantity format incorrect.');

		

        # 圖片處理
        /*
        $_FILES["file"]["name"]：上傳檔案的原始名稱。
        $_FILES["file"]["type"]：上傳的檔案類型。
        $_FILES["file"]["size"]：上傳的檔案原始大小。
        $_FILES["file"]["tmp_name"]：上傳檔案後的暫存資料夾位置。
        $_FILES["file"]["error"]：如果檔案上傳有錯誤，可以顯示錯誤代碼。
        */

        //開啟圖片檔
        $file = fopen($_FILES["file"]["tmp_name"], "rb");
        // 讀入圖片檔資料
        $fileContents = fread($file, filesize($_FILES["file"]["tmp_name"])); 
        //關閉圖片檔
        fclose($file);
        //讀取出來的圖片資料必須使用base64_encode()函數加以編碼：圖片檔案資料編碼
        $fileContents = base64_encode($fileContents);

        //組合查詢字串
        $imgType=$_FILES["file"]["type"];
        # 用prepare statement 將shop data存到資料庫裡
        $stmt=$conn->prepare("insert into foods (f_name, f_price, f_left, s_name, f_picture, f_picture_type, f_picture_name) 
            values (:name, :price, :quantity ,:s_name, :fileimg, :fileimgType, :imgname)");
        $stmt->execute(array('name' => $mealname, 'price' => $price, 'quantity' => $quantity, 's_name' => $s_name,
            'fileimg' => $fileContents, 'fileimgType'=> $imgType, 'imgname'=>$_FILES['file']['name']));
        
        //只負責加入食物到資料庫，而不回傳資料給 nav，在 nav 那邊(最上面)自己實施 sql


        // # 設定session
        echo <<< EOT
        <!DOCTYPE html>
            <html>
            <body>
            <script>
                alert("Add a product successfully!!");   <!-- 顯示畫面說建立成功-->
                window.location.replace("nav.php");        <!-- 導到nav.php-->
            </script> 
            </body> 
        </html>

        EOT;
        exit();
	
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