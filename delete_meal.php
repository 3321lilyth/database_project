<?php
session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{

    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # 防止直接輸入網址進入
    if (!isset($_POST['f_name'])){
        header("Location: index.php");
        exit();
    }
    $s_name = $_SESSION['s_name']; 
    $f_name = $_POST['f_name'];

    #取消已成立的訂單
    #0. 找到未完成訂單中有刪除餐點的訂單
    $sql = "select distinct u_account, orders.OID from orders, contain where o_state = 'not finish' and orders.s_name = :s_name and f_name = :f_name and orders.OID= contain.OID";
    $stmt1 = $conn->prepare($sql);
    $stmt1->execute(array('s_name'=> $s_name, 'f_name'=> $f_name));
    if($stmt1->rowcount()>0){
        $orders = $stmt1->fetchALL();

        foreach($orders as $order){
            #1.更新錢包金額
            $u_account = $order['u_account'];
            $o_id = $order['OID'];
            
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
            $_SESSION['u_money'] = $newMoney;
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
            
            #2.新增交易紀錄
            # 1)店家
            $action = 'Shop Refundency';
            $tmp_price = -($trans_price);   #扣款存負值
            $stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price,:account, :trader)");
            $stmt->execute(array('action' => $action, 'price' => $tmp_price,  'account' => $shopkeeper, 'trader'=> $u_account));
            
            // # 2)使用者
            $action = 'User Refundency';
            $stmt=$conn->prepare("insert into transaction (t_action, t_price, u_account, trader) values (:action, :price, :account, :trader)");
            $stmt->execute(array('action' => $action, 'price' => $trans_price, 'account' => $u_account, 'trader'=> $s_name));
            
            // #3.更新訂單狀態
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
    }

    //刪除餐點
    $sql = "delete from foods where s_name = :s_name and f_name =:f_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array ('s_name'=> $s_name, 'f_name'=> $f_name));    

    # 重新設定session
    echo <<< EOT
    <!DOCTYPE html>
        <html>
        <body>
        <script>
            alert("Delete meal successfully.");   <!-- 顯示畫面說成功-->
            window.location.replace("nav.php");        <!-- 導回nav.php-->
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