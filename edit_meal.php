<?php
session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';

try{
    if (!isset($_POST['new_price']) && !isset($_POST['new_quantity'])){
        header("Location: index.php");
        exit();
    }
    if (empty($_POST['new_price']))
        throw new Exception("Please input new price");
    if(empty($_POST['new_quantity']))
        throw new Exception("Please input new quantity");

    if($_POST['new_price'] == $_POST['f_price'] && $_POST['new_quantity'] == $_POST['f_quantity'])
        throw new Exception("Please input new price or new quantity");
    
    $new_price = $_POST['new_price'];
    $new_quantity = $_POST['new_quantity'];
    $name = $_SESSION['s_name']; 
    $f_name = $_POST['f_name'];

        
    if (!preg_match("/^[1-9][0-9]*$/" ,$new_price) || $new_price < 0)
        throw new Exception('Price format incorrect.');
    if (!preg_match("/^[1-9][0-9]*$/" ,$new_quantity) || $new_quantity < 0)
        throw new Exception('Quantity format incorrect.');
    
    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt=$conn->prepare("update foods set f_price = :new_price, f_left = :new_quantity where s_name = :name and f_name = :f_name");
    $stmt->execute(array ('name'=> $name, 'new_price' => $new_price,'new_quantity' => $new_quantity, 'f_name' => $f_name));

    # 重新設定session
    $_SESSION['f_price']=$new_price;
    $_SESSION['f_quantity']=$new_quantity;

    echo <<< EOT
    <!DOCTYPE html>
        <html>
        <body>
        <script>
            alert("Reset meal successfully.");   <!-- 顯示畫面說成功-->
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