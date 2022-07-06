<?php
//在 nav.php按下案件後，利用 js 執行 ajax 來呼叫 這份php的function


session_start();
$dbservername='localhost';
$dbname='DBproject_uber';
$dbusername='DBproject_uber';
$dbpassword='DBproject_uber';


try{
    
    # 避免直接輸入網址，因為 distance 在真的搜尋時不可能為空所以就用它了
    if (!isset($_POST['distance'])){
        header("Location: index.php");
        exit();
    }
    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $u_latitude = $_SESSION['u_latitude'];
    $u_longitude = $_SESSION['u_longitude'];
    
    //英文大小寫輸入條件->他店名跟種類都寫無限制
    
    $variables = array();
    
///////////////////這邊先正常的尋找所有符合搜尋條件的"商品"。然後下面將所有找到的商品的對應店家的"所有"商品也找一遍一起弄到 session 裡面////////////////////////
    
    $sql = "select distinct shops.s_name, s_type, ( 3959 * acos( cos( radians( :u_latitude ) ) * cos( radians( s_latitude ) ) * cos( radians( s_longitude ) 
        - radians( :u_longitude ) ) + sin( radians( :u_latitude ) ) * sin( radians( s_latitude ) ) ) ) as distance 
        from shops,foods where shops.s_name = foods.s_name ";
    $variables['u_latitude' ] = $u_latitude;
    $variables['u_longitude'] = $u_longitude;


    if (!empty($_POST['shop'])){
        $shop = $_POST['shop'];
        $shop = '%'.$shop.'%';
        $tmp = " and (select lower (convert (shops.s_name using utf8)) like :shop) ";   
        $sql = $sql.$tmp;
        $variables['shop'] = $shop;
    }

    if(!empty($_POST['price_left'])){
        if (!is_numeric($_POST['price_left']))
            throw new Exception('Price format incorrect.');
        $price_left = $_POST['price_left'];
        $tmp = " and foods.f_price >= :price_left  and foods.s_name = shops.s_name ";
        $sql = $sql.$tmp;
        $variables['price_left'] = $price_left;
    }

    if(!empty($_POST['price_right'])){
        if (!is_numeric($_POST['price_right']))
        throw new Exception('Price format incorrect.');
        $price_right = $_POST['price_right'];
        $tmp = " and foods.f_price <= :price_right and foods.s_name = shops.s_name ";
        $sql = $sql.$tmp;
        $variables['price_right'] = $price_right;
    }

    if(!empty($_POST['meal'])){
        $meal = $_POST['meal'];
        $meal = '%'.$meal.'%';
        $tmp = " and (select lower (convert (foods.f_name using utf8)) like :meal) ";
        $sql = $sql.$tmp;
        $variables['meal'] = $meal;
    }

    if(!empty($_POST['category'])){
        $category = $_POST['category'];
        $category = '%'.$category.'%';
        $tmp = " and (select lower (convert (shops.s_type using utf8)) like :category) ";
        $sql = $sql.$tmp;
        $variables['category'] = $category;
    }

    if($_POST['distance'] == 'near'){
        $sql = $sql." having distance < 3000 ";
    }else if($_POST['distance'] == 'medium'){
        $sql = $sql." having distance >= 3000 and distance < 5000 ";

    }else if($_POST['distance'] == 'far'){
        $sql = $sql." having distance >= 5000 ";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($variables);
    
    
    $shops = array();
    foreach ($stmt as $index => $row) {
        $shops[$index][0] = $row[0]; //shop name
        $shops[$index][1] = $row[1]; //shop type
        $shops[$index][2] = $_POST['distance'];
        $shops[$index][3] = $row[2];//真實距離

        $sql = "select u_account from shops where s_name = :s_name";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('s_name' =>$row[0]));
        $shop_owner = $stmt-> fetch();
        $shops[$index][4] = $shop_owner['u_account'];
    }

    $_SESSION['search_result'] = $shops;
    $_SESSION['shop_asc'] = 1;
    $_SESSION['type_asc'] = 1;
    $_SESSION['dis_asc'] = 1;






/////////////////////針對上面所有找到的店家(就算重複也沒有關係就存多筆)，再做一次搜尋，搜出該店家所有的商品清單之後也記錄到 session 裡面//////////////////////////
    $all_food_in_shops=array();
    foreach($shops as $shopindex => $shop){//對每一間店
        $sql = "select f_picture, f_name, f_price, f_left, f_picture_type, f_picture_name from foods where foods.s_name = :shop ";
        $stmt = $conn -> prepare($sql);
        // $shop[0] = strtolower($shop[0]);
        $stmt -> execute(array('shop'=> $shop[0]));
        $result2 = array();//裡面是單一間的裡面的每一個商品的6項細節資料
        foreach($stmt as $itemindex => $item){//對這間店裡面的每一個商品
            $result2[$itemindex][0] = $item[0];
            $result2[$itemindex][1] = $item[1];
            $result2[$itemindex][2] = $item[2];
            $result2[$itemindex][3] = $item[3];
            $result2[$itemindex][4] = $item[4];
            $result2[$itemindex][5] = $item[5];
        }
        $all_food_in_shops[$shopindex]=$result2;
    }
    $_SESSION['all_food_in_shops'] = $all_food_in_shops;


    # 重新設定session
    echo <<< EOT
    <!DOCTYPE html>
        <html>
        <body>
        <script>
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