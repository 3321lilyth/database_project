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
        # 直接輸入網址的情況
        if (!isset($_POST['value'])){
			header("Location: index.php");
			exit();
		}

        # 輸入為空的情況
		if (empty($_POST['value']))
            throw new Exception('Please input how much you want to recharge.');
        
        $rechargevalue = $_POST['value'];
        $u_account = $_SESSION['u_account'];
        if (!preg_match("/^[1-9][0-9]*$/" ,$rechargevalue) || $rechargevalue <= 0)
            throw new Exception('recharge format incorrect.');

        # 用prepare statement 將交易紀錄存到資料庫裡
        $action = 'Recharge';
        $stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price, :account, :trader)");
        $trader = 'myself:D';
        $stmt->execute(array('action' => $action, 'price' => $rechargevalue, 'account' => $u_account, 'trader'=> $trader));
        

        $sql = "select u_money from user where u_account =:u_account";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array ('u_account'=> $u_account));
        $result = $stmt->fetch(); #應該只會有一行(數字索引)

        #加好存回去
        $newMoney = $result['u_money'] + $rechargevalue;
        $sql = "update user set u_money =:u_money where u_account =:u_account ";
        //update foods set f_price = :new_price, f_left = :new_quantity where s_name = :name and f_name = :f_name
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('u_money'=> $newMoney, 'u_account'=> $u_account));
        $_SESSION['u_money'] = $newMoney;



        // # 設定session
        echo <<< EOT
        <!DOCTYPE html>
            <html>
            <body>
            <script>
                alert("Recharge successfully!!");   <!-- 顯示畫面說建立成功-->
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