<?php
//在 nav.php按下案件後，利用 js 執行 ajax 來呼叫 這份php的function

session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{
    # 避免直接輸入網址
    if (!isset($_POST['new_latitude']) || !isset($_POST['new_longitude'])){
        header("Location: index.php");
        exit();
    }

    if ( empty($_POST['new_latitude']) || empty($_POST['new_longitude']) 
        || ($_POST['new_latitude'] == $_SESSION['u_latitude'] && $_POST['new_longitude'] == $_SESSION['u_longitude']))
        throw new Exception("Please input new latitude and new longitude");
        
    $new_latitude = $_POST['new_latitude'];
    $new_longitude = $_POST['new_longitude'];
    $account = $_SESSION['u_account'];
    
    if (!is_numeric($new_latitude) || $new_latitude > 90.0 || $new_latitude <-90.0)
        throw new Exception('Latitude format incorrect.');
    if (!is_numeric($new_longitude) || $new_longitude > 180.0 || $new_longitude <-180.0)
        throw new Exception('Longitude format incorrect.');
    
    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt=$conn->prepare("update user set u_latitude = :new_latitude, u_longitude = :new_longitude where u_account = :account");
    $stmt->execute(array ('account'=> $account, 'new_latitude' => $new_latitude,'new_longitude' => $new_latitude));

    # 重新設定session
    $_SESSION['u_latitude']=$new_latitude;
    $_SESSION['u_longitude']=$new_longitude;

    echo <<< EOT
    <!DOCTYPE html>
        <html>
        <body>
        <script>
            alert("Reset position successfully.");   <!-- 顯示畫面說成功-->
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