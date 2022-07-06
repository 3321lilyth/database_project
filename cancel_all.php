<?php
//這個檔案是使用者用的，只能取消不能完成

session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';
$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (!isset($_POST['delete_all']) && !isset($_POST['delete_one'])){
	header("Location: index.php");
	exit();
}

if(isset($_POST['delete_all'])){
	
	try{

        # 0.先確定他有勾選任何訂單、每一筆她勾選的訂單還沒有被完成或者被另一方取消，也就是 o_state =  not finish，否則報錯
		if (empty($_POST['Orders'])){
			throw new Exception('please choose at least one order');  
		}

        $can_delete_all = TRUE;
        foreach($_POST['Orders'] as $o_id){
            $stmt = $conn->prepare("select o_state from orders where OID =:o_id");
            $stmt->execute(array ('o_id'=> $o_id));
            $result = $stmt->fetchALL();
            if ($result[0]['o_state'] == 'cancel' or $result[0]['o_state'] == 'finished'){
                $can_delete_all = FALSE;
            }
        }
        if ($can_delete_all == FALSE){
            throw new Exception('there is a order had been canceled or done');    
        }
        # 確認所有訂單都還處於 not finish 的狀態下才開始做事
		foreach($_POST['Orders'] as $o_id){
			$stmt = $conn->prepare("select u_account, s_name from orders where OID =:o_id");
			$stmt->execute(array ('o_id'=> $o_id));
			$result = $stmt->fetch(); #應該只會有一行

			$s_name = $result['s_name'];
			$u_account = $result['u_account'];

			#1.加回訂單中商品 
			#找到訂單中的所有商品
			$stmt = $conn->prepare("select FID, amount from contain where OID =:o_id");
			$stmt->execute(array ('o_id'=> $o_id));
			$foods = $stmt->fetchALL();
		
			foreach($foods as $row){
				$f_id = $row['FID'];
				$amount = $row['amount'];

				#取出DB裡的商品數量
				$sql = "select f_left from foods where FID =:f_id";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array ('f_id'=> $f_id));
				$result = $stmt->fetch(); #應該只會有一行

				#加好存回去
				$newAmount = $result['f_left'] + $amount;
				$sql = "update foods set f_left = :f_left where FID =:f_id";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array ('f_left'=>$newAmount, 'f_id'=> $f_id));
			}

			#2.更新錢包金額

			#整張訂單的價錢
			$stmt = $conn->prepare("select o_total_price from orders where OID =:o_id");
			$stmt->execute(array ('o_id'=> $o_id));
			$result = $stmt->fetch();
			$trans_price = $result['o_total_price'];

			# 1)店家(錢減少)
			$sql = "select user.u_account, u_money from user, shops where s_name =:s_name and user.u_account = shops.u_account";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array ('s_name'=> $s_name));
			$result = $stmt->fetch(); #應該只會有一行
		
			$shopkeeper = $result['u_account'];

			$newMoney = $result['u_money'] - $trans_price;  #減好存回去
			$sql = "update user set u_money =:u_money where u_account =:u_account";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array ('u_money'=> $newMoney, 'u_account'=> $shopkeeper));  #店長的帳號

			# 2)使用者(錢增加)
			$sql = "select u_money from user where u_account =:u_account";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array ('u_account'=> $u_account));
			$result = $stmt->fetch(); #應該只會有一行
			
			$newMoney = $result['u_money'] + $trans_price;  #加好存回去
			$sql = "update user set u_money =:u_money where u_account =:u_account";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array('u_money'=> $newMoney, 'u_account'=> $u_account));
            $_SESSION['u_money'] = $newMoney;

			#3.新增交易紀錄
			# 1)店家
			$action = 'Shop Refundency';
			$tmp_price = -1*$trans_price;   #扣款存負值
			$stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price,:account, :trader)");
			$stmt->execute(array('action' => $action, 'price' => $tmp_price,  'account' => $shopkeeper, 'trader'=> $u_account));

			# 2)使用者
			$action = 'User Refundency';
			$stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price, :account, :trader)");
			$stmt->execute(array('action' => $action, 'price' => $trans_price, 'account' => $u_account, 'trader'=>$s_name));

			#4.更新訂單狀態
			$sql = "select NOW()";
			$stmt=$conn->prepare($sql);
			$stmt->execute();
			$time = $stmt ->fetch();
			$time = $time[0];
			$state = 'cancel';
			$sql = "update orders set o_state = :o_state, o_end_time =:time where OID =:o_id";
			$stmt=$conn->prepare($sql);
			$stmt->execute(array('o_state' => $state, 'o_id'=>$o_id, 'time'=> $time));

		}
		# 重新設定session
		echo <<< EOT
		<!DOCTYPE html>
			<html>
			<body>
			<script>
				alert("Orders canceled successfully.");   <!-- 顯示畫面說成功-->
				window.location.replace("nav.php");		<!-- 導回nav.php-->
			</script> 
			</body> 
		</html>

		EOT;
		exit();
	}catch(Exception $e){
		$msg=$e->getMessage();	  # 儲存 exception中的錯誤訊息

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
}elseif (isset($_POST['delete_one'])){
	try{

		$o_id = $_POST['cancel_order'];

		# 0.先確定這筆訂單還沒有被完成或者被另一方取消，也就是 o_state =  not finish，否則報錯
		$stmt = $conn->prepare("select o_state from orders where OID =:o_id");
		$stmt->execute(array ('o_id'=> $o_id));
		$result = $stmt->fetchALL();
		if ($result[0]['o_state'] == 'cancel'){
			throw new Exception('the order had been canceled');
		}else if($result[0]['o_state'] == 'finished'){
			throw new Exception('the order had been done');
		}

		$stmt = $conn->prepare("select u_account, s_name from orders where OID =:o_id");
		$stmt->execute(array ('o_id'=> $o_id));
		$result = $stmt->fetch(); #應該只會有一行

		$s_name = $result['s_name'];
		$u_account = $result['u_account'];

		#1.加回訂單中商品 
		#找到訂單中的所有商品
		$stmt = $conn->prepare("select FID, amount from contain where OID =:o_id");
		$stmt->execute(array ('o_id'=> $o_id));
		$foods = $stmt->fetchALL();
		
		foreach($foods as $row){
			$f_id = $row['FID'];
			$amount = $row['amount'];

			#取出DB裡的商品數量
			$sql = "select f_left from foods where FID =:f_id";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array ('f_id'=> $f_id));
			$result = $stmt->fetch(); #應該只會有一行

			#加好存回去
			$newAmount = $result['f_left'] + $amount;
			$sql = "update foods set f_left = :f_left where FID =:f_id";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array ('f_left'=>$newAmount, 'f_id'=> $f_id));
		}

		#2.更新錢包金額

		#整張訂單的價錢
		$stmt = $conn->prepare("select o_total_price from orders where OID =:o_id");
		$stmt->execute(array ('o_id'=> $o_id));
		$result = $stmt->fetch();
		$trans_price = $result['o_total_price'];

		# 1)店家(錢減少)
		$sql = "select user.u_account, u_money from user, shops where s_name =:s_name and user.u_account = shops.u_account";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array ('s_name'=> $s_name));
		$result = $stmt->fetch(); #應該只會有一行
		
		$shopkeeper = $result['u_account'];

		$newMoney = $result['u_money'] - $trans_price;  #減好存回去
		$sql = "update user set u_money =:u_money where u_account =:u_account";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array ('u_money'=> $newMoney, 'u_account'=> $shopkeeper));  #店長的帳號

		# 2)使用者(錢增加)
		$sql = "select u_money from user where u_account =:u_account";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array ('u_account'=> $u_account));
		$result = $stmt->fetch(); #應該只會有一行
		
		$newMoney = $result['u_money'] + $trans_price;  #加好存回去
		$sql = "update user set u_money =:u_money where u_account =:u_account";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array('u_money'=> $newMoney, 'u_account'=> $u_account));
        $_SESSION['u_money'] = $newMoney;

		#3.新增交易紀錄
		# 1)店家
		$action = 'Shop Refundency';
		$tmp_price = -1*$trans_price;   #扣款存負值
		$stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price,:account, :trader)");
		$stmt->execute(array('action' => $action, 'price' => $tmp_price,  'account' => $shopkeeper, 'trader'=>$u_account));

		# 2)使用者
		$action = 'User Refundency';
		$stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account,trader) values (:action, :price, :account, :trader)");
		$stmt->execute(array('action' => $action, 'price' => $trans_price, 'account' => $u_account, 'trader'=>$s_name));

		#4.更新訂單狀態
		$sql = "select NOW()";
		$stmt=$conn->prepare($sql);
		$stmt->execute();
		$time = $stmt ->fetch();
		$time = $time[0];
		$state = 'cancel';
		$sql = "update orders set o_state = :o_state, o_end_time =:time where OID =:o_id";
		$stmt=$conn->prepare($sql);
		$stmt->execute(array('o_state' => $state, 'o_id'=>$o_id, 'time'=> $time));

		# 重新設定session
		echo <<< EOT
		<!DOCTYPE html>
			<html>
			<body>
			<script>
				alert("Order canceled successfully.");   <!-- 顯示畫面說成功-->
				window.location.replace("nav.php");		<!-- 導回nav.php-->
			</script> 
			</body> 
		</html>

		EOT;
		exit();

	}catch(Exception $e){
		$msg=$e->getMessage();	  # 儲存 exception中的錯誤訊息

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

}

?>