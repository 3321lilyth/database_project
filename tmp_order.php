<?php
	session_start();
    # 避免直接輸入網址
    if (!isset($_POST['shopindex']) || !isset($_POST['delivery_or_pick_up']) ){
        header("Location: index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html class="no-js">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Minimal and Clean Sign up / Login and Forgot Form by FreeHTML5.co</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Free HTML5 Template by FreeHTML5.co" />
    <meta name="keywords" content="free html5, free template, free bootstrap, html5, css3, mobile first, responsive" />
    <meta name="author" content="FreeHTML5.co" />
    <meta property="og:title" content="" />
    <meta property="og:image" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:description" content="" />
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="" />
    <meta name="twitter:url" content="" />
    <meta name="twitter:card" content="" />
    <link rel="shortcut icon" href="favicon.ico">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<?php
try{
?>
<body>
    
    <div class="container">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <?php 
                    //店家資訊
                    $shopindex = $_POST['shopindex'];
                    $shop = $_SESSION['search_result'][$shopindex];
                    $shopname = $shop[0];
                    $distance = $shop[3];//我跟店家的距離

                    $foods = $_SESSION['all_food_in_shops'][$shopindex];
                    $total = 0;
                    $delivery_fee = round($distance/1000) *10;
                    $o_type = $_POST['delivery_or_pick_up'];

                    if($delivery_fee<10) $delivery_fee = 10;
                    if($o_type == 'Pick up') $delivery_fee = 0;
                    foreach($foods as $foodindex => $food){
                        if (!preg_match("/^[0-9]*$/" ,$_POST['order_number_'.$foodindex])){
                            throw new Exception('please input correct order quantity format.');
                        }
                    }
                    ?> 
                    
                    <h3>Order</h3>
                    <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Picture</th>
                            <th scope="col">Meal Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Order Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                    <form action="real_order.php" method="post">
                    <?php

                    $food_index = 0;

                    foreach($foods as $foodindex => $food){
                        $order_number = $_POST['order_number_'.$foodindex];
                        // if ($order_number != 0){
                        $food_index = $food_index +1;
                        echo "                        
                                <tr>
                                    <th scope=\"row\">$food_index</th>
                                    <td><img src=\"Picture/$food[5]\" width =\"100\" height=\"100\" alt=\"$food[1]\"></td>
                                    <td>$food[1]</td>
                                    <td>$food[2]</td>
                                    <td>$order_number</td>
                                    <input type=\"hidden\" value =$order_number name = \"order_number_$foodindex\">
                                </tr>";          

                        // }
                        $total = $total + ($food[2] * $_POST['order_number_'.$foodindex] );
                    }
                    ?>
                    </tbody>
                    </table>

                    <td colspan="3" align="right">Subtotal</td>
                    <td aligh="right">$ <?php echo number_format($total, 2); ?></td>
                    <br>
                    <td colspan="3" align="right">delivery_fee</td>
                    <td aligh="right">$ <?php echo number_format($delivery_fee, 2); ?></td>
                    <br>
                    <td colspan="3" align="right">Total</td>
                    <td aligh="right">$ <?php echo number_format($total+$delivery_fee, 2); ?></td>
                    <br>
                    
                    <?php
                    echo "<input type=\"submit\" class=\"btn btn-default\" value=\"Order\" date-dismiss=\"model\">";
                    echo "<input type=\"hidden\" value =$o_type name = \"delivery_or_pick_up\">";
                    echo "<input type=\"hidden\" value =$shopindex name = \"shopindex\">";
                    ?>
                    </form>
                       
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Placeholder -->
    <script src="js/jquery.placeholder.min.js"></script>
    <!-- Waypoints -->
    <script src="js/jquery.waypoints.min.js"></script>
    <!-- Main JS -->
    <script src="js/main.js"></script>
</body>

</html>

<?php
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