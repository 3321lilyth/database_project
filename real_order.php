
<?php
    //使用者真的訂購後，從 show order 按下 order 後會跳來這邊。檢查能否下訂單，如果下成功了還要同時更新資料庫

    # 避免網址直接輸入
    if (!isset($_POST['delivery_or_pick_up']) || !isset($_POST['shopindex'])){
        header("Location: index.php");
        exit();
    }

    session_start();
    //取得店家基本資訊
    $dbservername='localhost';		    # mysql的 domain name 或 IP
	$dbname='DBproject_uber';       # mysql 中的資料庫名稱
	$dbusername='DBproject_uber';   # 去連這個資料庫需要的帳號
	$dbpassword='DBproject_uber';   # 去連這個資料庫需要的密碼

    $shopindex = $_POST['shopindex']; //這家店在 $_SESSION['search_result'] 中的index
    $shop_info = $_SESSION['search_result'][$shopindex]; 
    $shop_name = $shop_info[0];
    $shop_type = $shop_info[1];
    $ture_distance = $shop_info[3];//店家到使用者的真正距離，單位 公尺

	
	try {
        $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //取得店家商品清單
        $shop_foods = $_SESSION['all_food_in_shops'][$shopindex];
    
        //計算交易金額
        $subtotal = 0;//所有食物價格總和
        $delivery_or_pick_up = $_POST['delivery_or_pick_up'];
        if ($delivery_or_pick_up =='Pick up'){
            $delivery_fee = 0;
        }
        else{
            $delivery_fee = round($ture_distance/1000)*10;
            if($delivery_fee <10) $delivery_fee = 10; //確保每一筆訂單的運費至少10元
        }
        
        //檢查每一個食物能不能訂購
        $order_too_much = "We don't have enough ";
        foreach($shop_foods as $itemindex => $item){//基本上我們預設是就算使用者沒有訂購，也會傳一個0過來，所以這個是寫保險的
            $subtotal = $subtotal + $_POST['order_number_'.$itemindex]* $item[2];
            if ($_POST['order_number_'.$itemindex] > $item[3]){//訂購數量 > 店家庫存
                if($order_too_much == "We don't have enough "){
                    $order_too_much = $order_too_much."$item[1] ";
                }else{
                    $order_too_much = $order_too_much.", $item[1] ";
                }
            }
            if($_POST['order_number_'.$itemindex] < 0 || !preg_match("/^[0-9]*$/" ,$_POST['order_number_'.$itemindex])){//數量小於零或者非整數
                throw new Exception('please input correct order quantity format.');
            }

            $sql = "select f_name, f_price, f_left from foods where foods.s_name = :s_name and foods.f_name = :f_name";
            $stmt = $conn -> prepare($sql);
            $stmt -> execute(array('s_name'=> $shop_name, 'f_name'=>$item[1]));
            $row = $stmt->fetch();
            if (empty($row)){//餐點不存在
                throw new Exception('The food '.$item[1].' do not exist now.');
            }
            if($row['f_price'] != $item[2]){//顧客下單以前此產品價格發生變動
                throw new Exception('The price of '.$item[1].' was changed, please order again.');
            }
            if ($row['f_left'] < $_POST['order_number_'.$itemindex]){//顧客下單以前此產品數量發生變動導致存貨不足
                throw new Exception('The quantity of '.$item[1].' was changed, please order again.');
            }      
    
        }
        
        $total_price = $subtotal+$delivery_fee;//$subtotal + 運費
        if ($order_too_much!="We don't have enough "){//存在商品訂購數量 > 店家庫存
            throw new Exception($order_too_much);
        }else if($total_price >$_SESSION['u_money']){//價格>使用者餘額
            throw new Exception("Insufficient balance");
        }
    

        //訂購成功之下開始設定資料庫
        //新增一筆 order ，shop 和 user 都要有。訂單包含甚麼食物跟多少數量放在 contain，下面弄
        $sql = "insert into orders(o_state, o_total_price, o_type, s_name, u_account, o_delivery_fee) 
            value('not finish',:total_price, :o_type, :s_name, :u_account, :delivery_fee)";//start預設當下時間、end預設null
        $stmt = $conn -> prepare($sql);
        $stmt -> execute(array('total_price'=>$total_price, 'o_type'=>$delivery_or_pick_up, 's_name'=> $shop_name, 
            'u_account'=> $_SESSION['u_account'], 'delivery_fee'=>$delivery_fee));

        //更新商品數量、訂單包含甚麼食物跟多少數量放在contain
        foreach($shop_foods as $itemindex => $item){
            //更新商品數量
            $sql = "select f_left from foods where foods.s_name = :s_name and foods.f_name = :f_name";
            $stmt = $conn -> prepare($sql);
            $stmt -> execute(array('s_name'=> $shop_name, 'f_name'=>$item[1]));
            $row = $stmt->fetch();

            $new_left = $row['f_left'] - $_POST['order_number_'.$itemindex];
            $sql = "update foods set f_left=:new_left where foods.s_name = :s_name and foods.f_name = :f_name";
            $stmt = $conn -> prepare($sql);
            $stmt -> execute(array('s_name'=> $shop_name, 'f_name'=>$item[1], 'new_left' => $new_left));

            //加進 contain 裡面
            if ($_POST['order_number_'.$itemindex]>0){
                $sql = "select max(OID) from orders";//取得最新的那一筆，也就是剛剛上面加入的那一筆的 OID 才能來來加進 contian 裡面
                $stmt = $conn -> prepare($sql);
                $stmt -> execute();
                $row = $stmt->fetch();
                $OID = $row[0];
    
                $sql = "select FID, f_price, f_picture, f_name, f_picture_name from foods where foods.s_name = :s_name and foods.f_name = :f_name";//取得這個食物的所有資料
                $stmt = $conn -> prepare($sql);
                $stmt -> execute(array('s_name'=> $shop_name, 'f_name'=>$item[1]));
                $row = $stmt->fetch();
                $FID = $row[0];
                $f_current_price = $row[1];
                $f_picture = $row[2];
                $f_name = $row[3];
                $f_picture_name = $row[4];

                $sql = "insert into contain(OID, FID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name)
                     value(:OID, :FID, :amount, :f_current_price, :f_picture, :f_name, :s_name, :f_picture_name)";
                $stmt = $conn -> prepare($sql);
                $stmt -> execute(array('OID'=> $OID, 'FID'=> $FID, 'amount'=> $_POST['order_number_'.$itemindex],
                     'f_current_price'=>$f_current_price, 'f_picture'=>$f_picture, 'f_name'=> $f_name, 's_name'=>$shop_name, 'f_picture_name'=>$f_picture_name));
            }

        }
            
        //卡，使用者扣款且店家收款，使用者與店家各產生一筆交易紀錄
        //交易紀錄（買家）
        $action = 'Payment';
        $sql = "insert into transaction(t_action, t_price, u_account, trader) 
                    value(:action, :total_price, :u_account, :trader)";//time沒有
        $stmt = $conn->prepare($sql);
        $tmp_price = -1*$total_price;
        $stmt->execute(array('action'=> $action, 'total_price'=>$tmp_price, 'u_account'=> $_SESSION['u_account'], 'trader'=>$shop_name));
        //錢包更新（買家）
        $newmoney = $_SESSION['u_money'] - $total_price;
        $_SESSION['u_money'] = $newmoney;
        $sql = "update user set u_money =:u_money where u_account =:u_account ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('u_money'=> $newmoney, 'u_account'=> $_SESSION['u_account']));

        //交易紀錄（賣家）
        $action = 'Receive';
        $sql = "insert into transaction(t_action, t_price, u_account, trader) 
                    value(:action, :total_price, :u_account, :trader)";//time沒有
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('action'=> $action,'total_price'=>$total_price, 'u_account'=> $shop_info[4], 'trader'=>$_SESSION['u_account']));

        //錢包更新（賣家）
        $sql = "select u_money from user,shops where shops.u_account = :u_account and shops.u_account = user.u_account";
        $stmt = $conn -> prepare($sql);
        $stmt -> execute(array('u_account'=> $shop_info[4]));
        $row = $stmt->fetch();
        $newmoney = $row[0] + $total_price;
        $sql = "update user set u_money =:u_money where u_account =:u_account ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('u_money'=> $newmoney, 'u_account'=> $shop_info[4]));

        // # 設定session
        
        echo <<< EOT
        <!DOCTYPE html>
            <html>
            <body>
            <script>
                alert("Order successfully!!");   <!-- 顯示畫面說建立成功-->
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