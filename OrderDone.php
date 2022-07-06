<?php
session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{

    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $OID = $_POST['done_order'];

    $sql = "select NOW()";
    $stmt=$conn->prepare($sql);
    $stmt->execute();
    $time = $stmt ->fetch();
    $time = $time[0];
    $state = 'finished';
    $sql = "update orders set o_state = :o_state, o_end_time =:time where OID =:o_id";
    $stmt=$conn->prepare($sql);
    $stmt->execute(array('o_state' => $state, 'time'=> $time, 'o_id'=>$OID));

    # 重新設定session
    echo <<< EOT
    <!DOCTYPE html>
        <html>
        <body>
        <script>
            alert("Order Finished!!");   <!-- 顯示畫面說成功-->
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