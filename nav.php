<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <title>Hello, world!</title>
  <script>
    function minusFunc(id){
      const inputField = document.getElementById(id);
      const currentValue = Number(inputField.value) || 0;
      if(inputField.value > 0) inputField.value = currentValue - 1;
    }
    function addFunc(id){
      const inputField = document.getElementById(id);
      const currentValue = Number(inputField.value) || 0;
      inputField.value = currentValue + 1;
    }
    function check_name(shopname){
      if(shopname!=""){
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function(){
          var message;
          if(this.readyState == 4 && this.status == 200){
            switch(this.responseText){
              case 'YES':
                message='available.';
                break;
              case 'NO':
                message='not available.';
                break;
              default:
                message='something wrong.';
                break;
            }
            document.getElementById("msg").innerHTML = message;
          }
        };

        xhttp.open("POST", "check_shopname.php", true); //check_name!
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("shopname="+shopname);
      }else{
        document.getElementById("msg").innerHTML = "";
      }
    }
    function MyOrderFilter(){
      var v=document.getElementById("MyOrderFilter").value;
      alert(v);
    }
  </script>

</head>

<body onbeforeunload="window.location='logout.jsp'">


  <div class="container">
    
<?php
    session_start();
    $dbservername='localhost';
    $dbname='DBproject_uber';
    $dbusername='DBproject_uber';
    $dbpassword='DBproject_uber';

    
    $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    # ????????????????????????
    if ($_SESSION['Authenticated'] == FALSE){
    	header("Location: index.php");
    	exit();
    }
    
    $account = $_SESSION['u_account'];
    $sql = "select *  from user where u_account =:account";
    $stmt=$conn->prepare($sql);
    $stmt->execute(array ('account'=> $account));
    $row = $stmt->fetch();    

    $phone = $row['u_phone'];
    $latitude = $row['u_latitude'];
    $longitude = $row['u_longitude'];
    $money = $row['u_money'];

    //????????????????????????????????????
    $stmt=$conn->prepare("select user.u_account, shops.s_name, shops.s_type, shops.s_latitude, shops.s_longitude
      from user, shops where user.u_account= shops.u_account and user.u_account= :account");
    $stmt->execute(array ('account'=> $account));
    $row = $stmt->fetch();       # ??? fetch function ?????? query ????????????
    
    if(!empty($row)){//???????????????
      $identity ='shopkeeper';
      $s_name = $row['s_name'];
      $s_type = $row['s_type'];
      $s_latitude = $row['s_latitude'];
      $s_longitude = $row['s_longitude'];
      
      $_SESSION['s_name'] = $s_name;
      $_SESSION['s_type'] = $s_type;
      $_SESSION['s_latitude'] = $s_latitude;
      $_SESSION['s_longitude'] = $s_longitude;
    }
    else{
      $identity = 'user';
      $s_name = "";
      $s_type = "";
      $s_latitude = "";
      $s_longitude = "";
    }

    //?????????????????????????????????????????????????????????????????????
    if ($identity =='shopkeeper'){
      $stmt = $conn -> prepare("select f_picture, f_name, f_price, f_left, f_picture_type, f_picture_name from foods where foods.s_name = :s_name");
      // $stmt -> bindValue('sname', $s_name);
      $stmt -> execute(array('s_name' => $s_name));
      $result = array();
      foreach($stmt as $index => $row){
          $result[$index][0] = $row[0];
          $result[$index][1] = $row[1];
          $result[$index][2] = $row[2];
          $result[$index][3] = $row[3];
          $result[$index][4] = $row[4];
          $result[$index][5] = $row[5];
      }
      $_SESSION['foods_in_my_shop']=$result;
    }

    //???????????? nav ????????????????????????????????????
    if(isset($_SESSION['search_result'])){
      $all_food_in_shops=array();
      foreach($_SESSION['search_result'] as $shopindex => $shop){//???????????????
          $sql = "select f_picture, f_name, f_price, f_left, f_picture_type, f_picture_name from foods where foods.s_name = :shop ";
          $stmt = $conn -> prepare($sql);
          $stmt -> execute(array('shop'=> $shop[0]));
          $result2 = array();//????????????????????????????????????????????????6???????????????
          foreach($stmt as $itemindex => $item){//????????????????????????????????????
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
    }



    echo <<< EOT

    <!doctype html>
    <html lang="en">

    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <!-- Bootstrap CSS -->

      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
      <title>Hello, world!</title>
      <script>
        function check_name(shopname){
          if(shopname!=""){
            var xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function(){
              var message;
              if(this.readyState == 4 && this.status == 200){
                switch(this.responseText){
                  case 'YES':
                    message='available.';
                    break;
                  case 'NO':
                    message='not available.';
                    break;
                  default:
                    message='something wrong.';
                    break;
                }
                document.getElementById("msg").innerHTML = message;
              }
            };

            xhttp.open("POST", "check_shopname.php", true); //check_name!
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("shopname="+shopname);
          }else{
            document.getElementById("msg").innerHTML = "";
          }
        }
      </script>


    </head>

    <body onbeforeunload="window.location='logout.jsp'">
    
    <nav class="navbar navbar-inverse">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand " href="#">WebSiteName</a>
        </div>
      </div>
    </nav>


      <div class="container">
        <!-- class="container" ????????????  <ul class="nav nav-tabs">...</ul> ???????????????????????? <div class="tab-content">...</div> ???????????????-->

    <!-- -------------------------------------------------------?????????????????????????????????------------------------------------------------------------- -->
        <ul class="nav nav-tabs"> <!--????????????????????????????????? .nav-tabs ???????????????????????????????????????-->
          <li class="active"><a href="#home">Home</a></li> <!-- action ????????????????????? Home ??????????????????????????????????????????????????? -->
          <li class="nav-item"><a href="#menu1" class="nav-link">shop</a></li>
          <li class="nav-item"><a href="#MyOrder" class="nav-link">MyOrder</a></li>
          <li class="nav-item"><a href="#ShopOrder" class="nav-link">Shop Order</a></li>
          <li class="nav-item"><a href="#Transaction" class="nav-link">Transaction Record</a></li>
          <li class="out"><a href="index.php">Logout</a></li>
        </ul>

    <!--------------------------------------------------------- ????????? Home ?????????---------------------------------------------------------------->
        <div class="tab-content">
          <div id="home" class="tab-pane fade in active">
            <h3>Profile</h3>
            <div class="row">
              <div class="col-xs-12">

                Account : $account , $identity, phone number : $phone, location : ($latitude,$longitude)
                
                <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal"
                data-target="#location">edit location</button>
                
                
                <!--????????????-->
                <form action = "reset_position.php" method = "post">
                  <div class="modal fade" id="location"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog  modal-sm">
                      <div class="modal-content">
          
                        <div class="modal-header"> <!-- ?????????????????? -->
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">edit location</h4>
                        </div>
                        <div class="modal-body">
                          <label class="control-label " for="latitude">latitude</label>
                          <input type="text" class="form-control" name="new_latitude" placeholder="enter latitude">
                            <br>
                            <label class="control-label " for="longitude">longitude</label>
                          <input type="text" class="form-control" name="new_longitude" placeholder="enter longitude">
                        </div>
                        <div class="modal-footer">
                          <input type="submit" value="Edit">
                        </div>

                      </div>
                    </div>
                  </div>
                </form>

                walletbalance: $money
                <!-- Modal -->
                <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal"
                  data-target="#RechargeModal">Recharge</button>
                
                <form action="recharge.php" method="post">
                  <div class="modal fade" id="RechargeModal"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog  modal-sm">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">Recharge</h4>
                        </div>
                        <div class="modal-body">
                          <input type="text" class="form-control" name="value" placeholder="enter add value">
                        </div>
                        <div class="modal-footer">
                          <input type="submit" value="Add">
                        </div>

                      </div>
                    </div>
                  </div>
                </form>

              </div>

            </div>

            <h3>Search</h3>
              <div class=" row  col-xs-8">
              <form class="form-horizontal" action="search.php" method = "post">

                <div class="form-group">
                <!-- ???????????????????????????????????????????????????????? -->

                <label class="control-label col-sm-1" for="Shop">Shop</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" placeholder="Enter Shop name" name = "shop">
                </div>

                <label class="control-label col-sm-1" for="distance">distance</label>
                <div class="col-sm-5">
                  <select class="form-control" id="sel1" name = "distance">
                    <option>near</option>
                    <option>medium </option>
                    <option>far</option>
                  </select>
                </div>

                </div>

                <div class="form-group">

                  <label class="control-label col-sm-1" for="Price">Price</label>
                  <div class="col-sm-2">
                    <input type="text" class="form-control" name = "price_left">
                  </div>

                  <label class="control-label col-sm-1" for="~">~</label>
                  <div class="col-sm-2">         
                    <input type="text" class="form-control" name = "price_right">
                  </div>

                  <label class="control-label col-sm-1" for="Meal">Meal</label>
                  <div class="col-sm-5">
                    <input type="text" list="Meals" class="form-control" id="Meal" name = "meal" placeholder="Enter Meal">
                  </div>

                </div>

                <div class="form-group">
                  <label class="control-label col-sm-1" for="category" > category</label>
                    <div class="col-sm-5">
                      <input type="text" list="categorys" class="form-control" id="category" placeholder="Enter shop category" name = "category">
                      <datalist id="categorys">
    EOT;
    
    $sql = "select distinct s_type from shops";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    foreach ($stmt as $index => $row) {
      echo "           <option value=\"$row[0]\"></option>";
    }

    //??????????????? (??????????????????)
    if(isset($_SESSION['shop_asc'])) $shop_asc = $_SESSION['shop_asc'];
    if(isset($_SESSION['type_asc']))$type_asc = $_SESSION['type_asc'];
    if(isset($_SESSION['dis_asc']))$dis_asc = $_SESSION['dis_asc'];


    echo <<<EOT1
                      </datalist>
                    </div>
                    <button type="submit" style="margin-left: 18px;"class="btn btn-primary">Search</button>
                  
                </div>
              </form>
            </div>


            <div class="row">
              <div class="  col-xs-8">
                <table class="table" style=" margin-top: 15px;">
                  <thead>
                    <tr>
                      <form method = "post">
                      <th scope="col">#</th>
                      <th scope="col">shop name<input type = "submit" name="sort1" class="button" value=" ??? "/></th>
                      <th scope="col">shop category<input type = "submit" name="sort2" class="button" value=" ??? "/></th>
                      <th scope="col">Distance<input type = "submit" name="sort3" class="button" value=" ??? "/></th>
                      </form>
                    </tr>
                  </thead>
                  <tbody>    
    EOT1;

    if(isset($_SESSION['search_result']) && !empty($_SESSION['search_result']) && array_key_exists('sort1', $_POST)) {
      $shop_asc = $_SESSION['shop_asc'];
      $search_result = Sort1($_SESSION['search_result'], $shop_asc);
      $_SESSION['search_result'] = $search_result;

    }else if (isset($_SESSION['search_result']) && !empty($_SESSION['search_result']) && array_key_exists('sort2', $_POST)){
      $type_asc = $_SESSION['type_asc'];
      $search_result = Sort2($_SESSION['search_result'], $type_asc);
      $_SESSION['search_result'] = $search_result;
      
    }else if (isset($_SESSION['search_result']) && !empty($_SESSION['search_result']) && array_key_exists('sort3', $_POST)){
      $dis_asc = $_SESSION['dis_asc'];
      $search_result = Sort3($_SESSION['search_result'], $dis_asc);
      $_SESSION['search_result'] = $search_result;
    }
  
    function Sort1($array, $asc) {
      $newResult = [];
      //??????????????????????????????column
      foreach($array as $k1 => $v1){
        $values[$k1] = $v1[0] ?? "";
      }
      //????????????bool????????????
      $asc ? arsort($values) : asort($values);
      if ($asc == 1){
        $_SESSION['shop_asc'] = 0;
        $shop_asc = 0;
      }else{
        $_SESSION['shop_asc'] = 1;
        $shop_asc = 1;
      }

      //????????????????????????
      foreach($values as $k2 =>$v2){
        $newResult[$k2] = $array[$k2];	
      }
      return $newResult;
    }

    function Sort2($array, $asc) {
      $newResult = [];
      //??????????????????????????????column
      foreach($array as $k1 => $v1){
        $values[$k1] = $v1[1] ?? "";
      }
      //????????????bool????????????
      $asc ? arsort($values) : asort($values);
      if ($asc == 1){
        $_SESSION['type_asc'] = 0;
        $type_asc = 0;
      }else{
        $_SESSION['type_asc'] = 1;
        $type_asc = 1;
      }

      //????????????????????????
      foreach($values as $k2 =>$v2){
        $newResult[$k2] = $array[$k2];	
      }
      return $newResult;
    }

    function Sort3($array, $asc) {
      $newResult = [];
      //??????????????????????????????column
      foreach($array as $k1 => $v1){
        $values[$k1] = $v1[3] ?? "";
      }
      //????????????bool????????????
      $asc ? arsort($values) : asort($values);
      if ($asc == 1){
        $_SESSION['dis_asc'] = 0;
        $dis_asc = 0;
      }else{
        $_SESSION['dis_asc'] = 1;
        $dis_asc = 1;
      }

      //????????????????????????
      foreach($values as $k2 =>$v2){
        $newResult[$k2] = $array[$k2];	
      }
      return $newResult;
    }


    if(isset($_SESSION['search_result']) && !empty($_SESSION['search_result']) ){
        $data_nums = Count($_SESSION['search_result']); //all
        $per = 5; //????????????????????????
        $pages = ceil($data_nums/$per); //????????????????????????????????????
        // if (!isset($search_result))

        $search_result = $_SESSION['search_result'];
        if (!isset($_GET["page"])){ //??????$_GET["page"]?????????
          $page=1; //???????????????????????????
        }else{
          $page = intval($_GET["page"]); //????????????????????????????????????
        }
        $current_page = (!isset($_GET["page"]) || $_GET["page"] == "") ? 1: $_GET["page"];
        $offset = ($current_page-1)*$per;
        $new_headline = array_slice($search_result, $offset, $per);
        foreach ($new_headline as $shopindex => $value) {
          $tmp = $shopindex+1;
          $tmp2 = $shopindex+($current_page-1)*5+200000;//?????????????????????????????????
          echo "
                    <tr>
                      <th scope=\"row\">$tmp</th>
                      <td>$value[0]</td>
                      <td>$value[1]</td>
                      <td>$value[2]</td>
                      <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#$tmp2\">Open menu</button></td>
                    </tr>
          ";
        }
      }
      
      
    echo"
                  </tbody>
                </table>
    ";
      
    if(!isset($_SESSION['search_result']) || empty($_SESSION['search_result']))
      echo "Have no search result.";
    else{
      //??????????????????
      echo $data_nums.' shop(s) in total.'.$pages.' page(s) in total.';
      echo "<br>";
      echo '< ';
      for( $i=1 ; $i<=$pages ; $i++ ) {
        if ( $page-3 < $i && $i < $page+3 ) {
          echo "<a href= \"nav.php?page=".$i."\">".$i."</a> ";
        }
      }  
      echo '>';
    }

    echo <<< EOT2
  
              </div>
            </div>
          </div>




    <!--------------------------------------------------------- ????????? shop ?????????---------------------------------------------------------------->
          <div id="menu1" class="tab-pane fade" >

            <h3 > Start a business </h3>
            <form action = "shop_register.php" method = "post" >

              <div class="form-group ">
                <div class="row">
                  <div class="col-xs-2">
                    <label for="ex5">shop name</label>
                    <input class="form-control" id="ex5" placeholder="$s_name" name="s_name" type="text" oninput="check_name(this.value);" >
                    <label id="msg"></label>
                  </div>
                  <div class="col-xs-2">
                    <label for="ex5">shop category</label>
                    <input class="form-control" id="ex5" placeholder="$s_type" type="text" name="s_category" >
                  </div>
                  <div class="col-xs-2">
                    <label for="ex6">latitude</label>
                    <input class="form-control" id="ex6" placeholder="$s_latitude" type="text" name="s_latitude" >
                  </div>
                  <div class="col-xs-2">
                    <label for="ex8">longitude</label>
                    <input class="form-control" id="ex8" placeholder="$s_longitude" type="text" name ="s_longitude">
                  </div>
                </div>
              </div>
EOT2;
              
              
    if($identity == "shopkeeper"){
      echo "<div class=\" row\" style=\" margin-top: 25px;\"><div class=\" col-xs-3\">
      <button type=\"button\" class=\"btn btn-primary\" disabled>register</button></div></div>";
    }else{
      echo "<div class= \"row\" style= \"margin-top: 25px;\"><div class=\"col-xs-3\"><input class=\"btn btn-primary\"  type=\"submit\" value =\"register\"></div></div>";
    }
    
    
    echo <<< EOT3

              <hr>
            </form>


            <!--????????????????????? -->
            <form action = "add_food.php" method ="post" enctype = "multipart/form-data">
            <h3>ADD</h3>
              <div class="form-group ">
              <div class="row">

                <div class="col-xs-6">
                  <label for="ex3">meal name</label>
                  <input class="form-control" id="ex3" type="text" name = "mealname">
                </div>
              </div>
              <div class="row" style=" margin-top: 15px;">
                <div class="col-xs-3">
                  <label for="ex7">price</label>
                  <input class="form-control" id="ex7" type="text" name = "price">
                </div>
                <div class="col-xs-3">
                  <label for="ex4">quantity</label>
                  <input class="form-control" id="ex4" type="text" name="quantity">
                </div>
              </div>


              <div class="row" style=" margin-top: 25px;">
                <div class=" col-xs-3">
                  <label for="ex12">????????????</label>
                  <input id="myFile" type="file" name="file" multiple class="file-loading">
                </div>
                
    EOT3;

    if($identity == "shopkeeper"){
      echo "<div class=\" col-xs-3\"><input class=\"btn btn-primary\"  type=\"submit\" value =\"ADD\"></div>";
      
    }else{
      echo"<div class=\" col-xs-3\"><button style=\" margin-top: 15px;\" type=\"button\" class=\"btn btn-primary\" disabled>Add</button></div>";
    }


    echo <<<EOT4


              </div>

            </div>
            </form>

            <div class="row">
              <div class="  col-xs-8">
                <table class="table" style=" margin-top: 25px;">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Picture</th>
                      <th scope="col">meal name</th>
                  
                      <th scope="col">price</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Edit</th>
                      <th scope="col">Delete</th>
                    </tr>
                  </thead>
                  <tbody>
                  
    EOT4;

    if(isset($_SESSION['foods_in_my_shop']) && !empty($_SESSION['foods_in_my_shop']) ){
      $row = $_SESSION['foods_in_my_shop'];
      foreach ($row as $index => $value) {
        $tmp = $index+1;
        $tmp2 = $tmp+300000;
        $tmp3 = $tmp+400000;
        $tmp4 = $index+500000;//????????????????????????????????????????????????id
        echo "
                    <tr>
                      <th scope=\"row\">$tmp</th>
                      <td><img src=\"Picture/$value[5]\" width =\"100\" height=\"100\" alt=\"$value[1]\" ></td>
                      <td>$value[1]</td>
                      <td>$value[2]</td>
                      <td>$value[3]</td>
                      <td><button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\" data-target=\"#$tmp4\">Edit</button></td>  
                      
                      <!-- Modal -->
                      <form action =\"edit_meal.php\" method=\"post\">
                        <input type=\"hidden\" name=\"f_name\" value=\"$value[1]\">
                        <input type=\"hidden\" name=\"f_price\" value=\"$value[2]\">
                        <input type=\"hidden\" name=\"f_quantity\" value=\"$value[3]\">
                        <div class=\"modal fade\" id=\"$tmp4\" data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                          <div class=\"modal-dialog\" role=\"document\">
                            <div class=\"modal-content\">

                              <div class=\"modal-header\">
                                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">$value[1] Edit</h5>
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                                  <span aria-hidden=\"true\">&times;</span>
                                </button>
                              </div>

                              <div class=\"modal-body\">
                                <div class=\"row\" >
                                  <div class=\"col-xs-6\">
                                    <label for=\"ex$tmp2\">price</label>
                                    <input class=\"form-control\" id=\"ex$tmp2\" type=\"text\" name = \"new_price\">
                                  </div>

                                  <div class=\"col-xs-6\">
                                    <label for=\"ex$tmp3\">quantity</label>
                                    <input class=\"form-control\" id=\"ex$tmp3\" type=\"text\" name = \"new_quantity\">
                                  </div>
                                </div>
                              </div>

                              <div class=\"modal-footer\">
                                <input type=\"submit\" class=\"btn btn-secondary\" value=\"Edit\">
                              </div>

                            </div>
                          </div>
                        </div> 
                      </form>

                      <form action =\"delete_meal.php\" method = \"post\">
                        <input type=\"hidden\" name=\"f_name\" value=\"$value[1]\">
                        <td><input type=\"submit\" class=\"btn btn-danger\" value=\"Delete\"></td>
                      </form>
                      </tr>
        ";
      }
    }

      echo"
                    </tbody>
                  </table>
      ";
      if($identity == "shopkeeper" && (!isset($_SESSION['foods_in_my_shop']) || empty($_SESSION['foods_in_my_shop'])))
        echo "Have no food in your shop yet!";

    echo <<<EOT5

                    </div>
                  </div>
                </div>

    EOT5; //Shop ??????
    echo "<!--------------------------------------------------------- ????????? MyOrder ?????????---------------------------------------------------------------->";
    //???????????? filter ?????????
    $MyOrder_index = 0;
    if(isset($_POST["MyOrderFilter"])){
      $MyOrderFilter=$_POST["MyOrderFilter"];
    }else{
      $MyOrderFilter = "ALL";
    }
    if($MyOrderFilter == "ALL"){
      echo"
      <div id=\"MyOrder\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"MyOrderFilter\">Status</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"MyOrderFilter\" name = \"MyOrderFilter\" onchange=\"this.form.submit()\">
                  <option selected>ALL</option>
                  <option>finished</option>
                  <option>not finish</option>
                  <option>cancel</option>
                </select>
              </div>
            </form>
      ";
    }else if($MyOrderFilter == "finished"){
      echo"
      <div id=\"MyOrder\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"MyOrderFilter\">Status</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"MyOrderFilter\" name = \"MyOrderFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option selected>finished</option>
                  <option>not finish</option>
                  <option>cancel</option>
                </select>
              </div>
            </form>
      ";
    }else if($MyOrderFilter == "not finish"){
      echo"
      <div id=\"MyOrder\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"MyOrderFilter\">Status</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"MyOrderFilter\" name = \"MyOrderFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>finished</option>
                  <option selected>not finish</option>
                  <option>cancel</option>
                </select>
              </div>
            </form>
      ";
    }else if($MyOrderFilter == "cancel"){
      echo"
      <div id=\"MyOrder\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"MyOrderFilter\">Status</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"MyOrderFilter\" name = \"MyOrderFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>finished</option>
                  <option>not finish</option>
                  <option selected>cancel</option>
                </select>
              </div>
            </form>
      ";
    }
          
    echo <<<EOT6
            <br>
            <br>
            <br>
            <form action="cancel_all.php" method = "post">
              <button type="submit" name="delete_all">Delete Selected Orders</button>
              <div class="row">
                <div class="  col-xs-8">
                  <table class="table" style=" margin-top: 25px;">
                    <thead>
                      <tr>
                        <th scope="col">checkbox</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Start</th>
                        <th scope="col">End</th>
                        <th scope="col">Shop name</th>
                        <th scope="col">Total Price</th>
                        <th scope="col">Order Details</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
    EOT6;
    //?????? filter ???????????????
    $MyOrder_index = 0;
    if(isset($_POST["MyOrderFilter"])){
      $MyOrderFilter=$_POST["MyOrderFilter"];
    }else{
      $MyOrderFilter = "ALL";
    }
    
    //for finished filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == "finished"){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
      where orders.u_account = :u_account and o_state='finished'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));

      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_finished = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_finished as $index => $order) {
          $MyOrder_index = $MyOrder_index +1;
          $tmp2 = $index+6000;
          echo "
                        <tr>
                          <td></td>
                          <th scope=\"row\">$MyOrder_index</th>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#MyOrder$tmp2\">Order detail</button></td>
                        <tr>

          ";
        }
      }
    } 
    //for not finish filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == 'not finish'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
        where orders.u_account = :u_account and o_state='not finish'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_not_finish = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_not_finish as $index => $order) {
          $MyOrder_index = $MyOrder_index +1;
          $tmp2 = $index+7000;
          echo "
                        <tr>
                          <td><input type=\"checkbox\" name=\"Orders[]\" value=\"$order[0]\"></td>
                          <td>$MyOrder_index</td>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#MyOrder$tmp2\">Order detail</button></td>
                          <input type=\"hidden\" name=\"cancel_order\" value=\"$order[0]\">
                          <td><input type=\"submit\" class=\"btn btn-danger\" value=\"Cancel\" name=\"delete_one\"></td>
                        <tr>
          ";
        }
      }
    }
    //for cancel filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == 'cancel'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
          where orders.u_account = :u_account and o_state='cancel'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_cancel = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_cancel as $index => $order) {
          $MyOrder_index = $MyOrder_index + 1;
          $tmp2 = $index+8000;
          echo "
                        <tr>
                          <td></td>
                          <th scope=\"row\">$MyOrder_index</th>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#MyOrder$tmp2\">Order detail</button></td>
                        <tr>
          ";
        }
      }
    }
    echo <<< EOT7
                    </tbody>
                  </table>
                </div>
              </div>
            </form>
          </div>
    EOT7;  // MyOrder??????
    echo "<!--------------------------------------------------------- ????????? Shop Order ?????????---------------------------------------------------------------->";
    //???????????? filter ?????????
    $ShopOrder_index = 0;
    if(isset($_POST["ShopOrderFilter"]) && $identity =='shopkeeper'){ 
      $ShopOrderFilter = $_POST["ShopOrderFilter"];
    }else if($identity ='shopkeeper'){
      $ShopOrderFilter = "ALL";
    }else{
      $ShopOrderFilter = ""; //???????????????????????????????????????????????????????????????????????? if ??????
    }
    if($ShopOrderFilter == "finished"){
        echo"
          <div id=\"ShopOrder\" class=\"tab-pane fade\" >
              <label class=\"control-label col-sm-1\" for=\"ShopOrderFilter\">Status</label>
              <form method =\"post\" action=\"\">
                <div class=\"col-sm-5\">
                  <select class=\"form-control\" id=\"ShopOrderFilter\" name = \"ShopOrderFilter\" onchange=\"this.form.submit()\">
                    <option>ALL</option>
                    <option selected>finished</option>
                    <option>not finish</option>
                    <option>cancel</option>
                  </select>
                </div>
              </form>
        ";
    }else if($ShopOrderFilter == "not finish"){
        echo"
          <div id=\"ShopOrder\" class=\"tab-pane fade\" >
              <label class=\"control-label col-sm-1\" for=\"ShopOrderFilter\">Status</label>
              <form method =\"post\" action=\"\">
                <div class=\"col-sm-5\">
                  <select class=\"form-control\" id=\"ShopOrderFilter\" name = \"ShopOrderFilter\" onchange=\"this.form.submit()\">
                    <option>ALL</option>
                    <option>finished</option>
                    <option selected>not finish</option>
                    <option>cancel</option>
                  </select>
                </div>
              </form>
        ";
    }else if($ShopOrderFilter == "cancel"){
        echo"
          <div id=\"ShopOrder\" class=\"tab-pane fade\" >
              <label class=\"control-label col-sm-1\" for=\"ShopOrderFilter\">Status</label>
              <form method =\"post\" action=\"\">
                <div class=\"col-sm-5\">
                  <select class=\"form-control\" id=\"ShopOrderFilter\" name = \"ShopOrderFilter\" onchange=\"this.form.submit()\">
                    <option>ALL</option>
                    <option>finished</option>
                    <option>not finish</option>
                    <option selected>cancel</option>
                  </select>
                </div>
              </form>
        ";
    }else{
        echo"
          <div id=\"ShopOrder\" class=\"tab-pane fade\" >
              <label class=\"control-label col-sm-1\" for=\"ShopOrderFilter\">Status</label>
              <form method =\"post\" action=\"\">
                <div class=\"col-sm-5\">
                  <select class=\"form-control\" id=\"ShopOrderFilter\" name = \"ShopOrderFilter\" onchange=\"this.form.submit()\">
                    <option selected>ALL</option>
                    <option>finished</option>
                    <option>not finish</option>
                    <option>cancel</option>
                  </select>
                </div>
              </form>
        ";
    }
          
    echo <<< EOT8
            <br>
            <br>
            <br>
            <form action="done_all.php" method = "post">
              <button type="submit" name="cancel_all">Delete Selected Orders</button>
              <button type="submit" name="done_all">Done Selected Orders</button>

              <div class="row">
                <div class="  col-xs-8">
                  <table class="table" style=" margin-top: 25px;">
                    <thead>
                      <tr>
                        <th scope="col">checkbox</th>
                        <th scope="col">Record ID</th>
                        <th scope="col">Action</th>
                        <th scope="col">Time</th>
                    
                        <th scope="col">End</th>
                        <th scope="col">Shop name</th>
                        <th scope="col">Total Price</th>
                        <th scope="col">Order Details</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
    EOT8;
    //?????? filter ???????????????
    $ShopOrder_index = 0;
    if(isset($_POST["ShopOrderFilter"]) && $identity =='shopkeeper'){ 
      $ShopOrderFilter=$_POST["ShopOrderFilter"];
    }else if($identity ='shopkeeper'){
      $ShopOrderFilter = "ALL";
    }else{
      $ShopOrderFilter = ""; //???????????????????????????????????????????????????????????????????????? if ??????
    }
    
    //for finished filter
    if($ShopOrderFilter == "ALL" or $ShopOrderFilter == "finished"){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
        where orders.s_name = :s_name and o_state='finished'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('s_name'=>$s_name));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $ShopOrder_results_finished = $stmt -> fetchAll(); //???????????????????????????
        foreach ($ShopOrder_results_finished as $index => $order) {
          $ShopOrder_index = $ShopOrder_index +1;
          $tmp2 = $index+3000;
          echo "
                        <tr>
                          <td></td>
                          <th scope=\"row\">$ShopOrder_index</th>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#ShopOrder$tmp2\">Order detail</button></td>
                        <tr>

          ";
        }
      }
    } 
    //for not finish filter
    if($ShopOrderFilter == "ALL" or $ShopOrderFilter == 'not finish'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
        where orders.s_name = :s_name and o_state='not finish'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('s_name'=>$s_name));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $ShopOrder_results_not_finish = $stmt -> fetchAll(); //???????????????????????????
        foreach ($ShopOrder_results_not_finish as $index => $order) {
          $ShopOrder_index = $ShopOrder_index + 1;
          $tmp2 = $index+4000;
          echo "
                        <tr>
                          <td><input type=\"checkbox\" name=\"Orders[]\" value=\"$order[0]\"></td>
                          <th scope=\"row\">$ShopOrder_index</th>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#ShopOrder$tmp2\">Order detail</button></td>
                        
                          <input type=\"hidden\" name=\"cancel_order\" value=\"$order[0]\">
                          <td><input type=\"submit\" class=\"btn btn-danger\" value=\"Cancel\" name=\"cancel_one\"></td>
                          <input type=\"hidden\" name=\"done_order\" value=\"$order[0]\">
                          <td><input type=\"submit\" class=\"btn btn-danger\" value=\"Done\" name=\"done_one\"></td>
                          
                        <tr>

          ";
        }
      }
    }
    //for cancel filter
    if($ShopOrderFilter == "ALL" or $ShopOrderFilter == 'cancel'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
          where orders.s_name = :s_name and o_state='cancel'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('s_name'=>$s_name));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $ShopOrder_results_cancel = $stmt -> fetchAll(); //???????????????????????????
        foreach ($ShopOrder_results_cancel as $index => $order) {
          $ShopOrder_index = $ShopOrder_index+1;
          $tmp2 = $index+5000;
          echo "
                        <tr>
                          <td></td>
                          <th scope=\"row\">$ShopOrder_index</th>
                          <td>$order[1]</td>
                          <td>$order[2]</td>
                          <td>$order[3]</td>
                          <td>$order[6]</td>
                          <td>$order[4]</td>
                          <td> <button type=\"button\" class=\"btn btn-info \" data-toggle=\"modal\" data-target=\"#ShopOrder$tmp2\">Order detail</button></td>
                        <tr>
          ";
        }
      }
    }



    echo <<<EOT9
                    </tbody>
                  </table>
                </div>
              </div>
            </form>
          </div> 

    EOT9; // Shop Order ??????
    echo "<!--------------------------------------------------------- ????????? transaction Record ?????????---------------------------------------------------------------->";
    //???????????? filter ?????????
    if(isset($_POST["TransactionFilter"])){
      $TransactionFilter = $_POST["TransactionFilter"];
    }else{
      $TransactionFilter = "ALL";
    }
    if($TransactionFilter == "ALL"){
      echo"
      <div id=\"Transaction\" class=\"tab-pane fade\" >
        <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
        <form action=\"\" method=\"post\">
          <div class=\"col-sm-5\">
            <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
              <option selected>ALL</option>
              <option>Payment</option>
              <option>Receive</option>
              <option>Recharge</option>
              <option>Shop Refundency</option>
              <option>User Refundency</option>
            </select>
          </div>
        </form>
      ";
    }else if($TransactionFilter == "Payment"){
      echo"
      <div id=\"Transaction\" class=\"tab-pane fade\" >
        <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
        <form action=\"\" method=\"post\">
          <div class=\"col-sm-5\">
            <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
              <option>ALL</option>
              <option selected>Payment</option>
              <option>Receive</option>
              <option>Recharge</option>
              <option>Shop Refundency</option>
              <option>User Refundency</option>
            </select>
          </div>
        </form>
      ";
    }else if($TransactionFilter == "Receive"){
      echo"
        <div id=\"Transaction\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>Payment</option>
                  <option selected>Receive</option>
                  <option>Recharge</option>
                  <option>Shop Refundency</option>
                  <option>User Refundency</option>
                </select>
              </div>
            </form>
      ";
    }else if($TransactionFilter == "Recharge"){
      echo"
        <div id=\"Transaction\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>Payment</option>
                  <option>Receive</option>
                  <option selected>Recharge</option>
                  <option>Shop Refundency</option>
                  <option>User Refundency</option>
                </select>
              </div>
            </form>
      ";
    }else if($TransactionFilter == "Shop Refundency"){
      echo"
        <div id=\"Transaction\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>Payment</option>
                  <option>Receive</option>
                  <option>Recharge</option>
                  <option selected>Shop Refundency</option>
                  <option>User Refundency</option>
                </select>
              </div>
            </form>
      ";
    }else if($TransactionFilter == "User Refundency"){
      echo"
        <div id=\"Transaction\" class=\"tab-pane fade\" >
            <label class=\"control-label col-sm-1\" for=\"TransactionFilter\">Action</label>
            <form action=\"\" method=\"post\">
              <div class=\"col-sm-5\">
                <select class=\"form-control\" id=\"TransactionFilter\" name = \"TransactionFilter\" onchange=\"this.form.submit()\">
                  <option>ALL</option>
                  <option>Payment</option>
                  <option>Receive</option>
                  <option>Recharge</option>
                  <option>Shop Refundency</option>
                  <option selected>User Refundency</option>
                </select>
              </div>
            </form>
      ";
    }
          
    echo <<< EOT10
            <br>
            <br>
            <br>


            <div class="row">
              <div class="  col-xs-8">
                <table class="table" style=" margin-top: 25px;">
                  <thead>
                    <tr>
                      <th scope="col">Record ID</th>
                      <th scope="col">Action</th>
                      <th scope="col">Time</th>
                      <th scope="col">Trader</th>
                      <th scope="col">Account change</th>
                    </tr>
                  </thead>
                  <tbody>
    EOT10;

    //?????? filter ???????????????
    $transaction_index = 0;
    if(isset($_POST["TransactionFilter"])){ 
      $TransactionFilter=$_POST["TransactionFilter"];
    }else{
      $TransactionFilter = "ALL";
    }
    
    //for Payment filter
    if($TransactionFilter == "ALL" or $TransactionFilter == "Payment"){
      $sql ="select t_action, t_time, trader, t_price from transaction where u_account =:u_account and t_action = 'Payment'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????transaction????????????
        $transaction_results_payment = $stmt -> fetchAll(); //???????????????????????????
        foreach ($transaction_results_payment as $index => $transaction) {
          $transaction_index = $transaction_index +1;
          echo "
                        <tr>
                          <th scope=\"row\">$transaction_index</th>
                          <td>$transaction[0]</td>
                          <td>$transaction[1]</td>
                          <td>$transaction[2]</td>
                          <td>$transaction[3]</td>
                        <tr>

          ";
        }
      }
    }
        
    //for Receive filter
    if($TransactionFilter == "ALL" or $TransactionFilter == "Receive"){
      $sql ="select t_action, t_time, trader, t_price from transaction where u_account =:u_account and t_action = 'Receive'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????transaction????????????
        $transaction_results_Receive = $stmt -> fetchAll(); //???????????????????????????
        foreach ($transaction_results_Receive as $index => $transaction) {
          $transaction_index = $transaction_index +1;
          echo "
                        <tr>
                          <th scope=\"row\">$transaction_index</th>
                          <td>$transaction[0]</td>
                          <td>$transaction[1]</td>
                          <td>$transaction[2]</td>
                          <td>$transaction[3]</td>
                        <tr>

          ";
        }
      }
    } 
        
    //for Recharge filter
    if($TransactionFilter == "ALL" or $TransactionFilter == "Recharge"){
      $sql ="select t_action, t_time, trader, t_price from transaction where u_account =:u_account and t_action = 'Recharge'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????transaction????????????
        $transaction_results_Recharge = $stmt -> fetchAll(); //???????????????????????????
        foreach ($transaction_results_Recharge as $index => $transaction) {
          $transaction_index = $transaction_index +1;
          echo "
                        <tr>
                          <th scope=\"row\">$transaction_index</th>
                          <td>$transaction[0]</td>
                          <td>$transaction[1]</td>
                          <td>$transaction[2]</td>
                          <td>$transaction[3]</td>
                        <tr>

          ";
        }
      }
    } 
        
    //for Shop Refundency filter
    if($TransactionFilter == "ALL" or $TransactionFilter == "Shop Refundency"){
      $sql ="select t_action, t_time, trader, t_price from transaction where u_account =:u_account and t_action = 'Shop Refundency'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????transaction????????????
        $transaction_results_Shop_Refundency = $stmt -> fetchAll(); //???????????????????????????
        foreach ($transaction_results_Shop_Refundency as $index => $transaction) {
          $transaction_index = $transaction_index +1;
          echo "
                        <tr>
                          <th scope=\"row\">$transaction_index</th>
                          <td>$transaction[0]</td>
                          <td>$transaction[1]</td>
                          <td>$transaction[2]</td>
                          <td>$transaction[3]</td>
                        <tr>

          ";
        }
      }
    } 
        
    //for User Refundency filter
    if($TransactionFilter == "ALL" or $TransactionFilter == "User Refundency"){
      $sql ="select t_action, t_time, trader, t_price from transaction where u_account =:u_account and t_action = 'User Refundency'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????transaction????????????
        $transaction_results_User_Refundency = $stmt -> fetchAll(); //???????????????????????????
        foreach ($transaction_results_User_Refundency as $index => $transaction) {
          $transaction_index = $transaction_index +1;
          echo "
                        <tr>
                          <th scope=\"row\">$transaction_index</th>
                          <td>$transaction[0]</td>
                          <td>$transaction[1]</td>
                          <td>$transaction[2]</td>
                          <td>$transaction[3]</td>
                        <tr>

          ";
        }
      }
    } 
    






    echo <<<EOT11
                  </tbody>
                </table>
              </div>
            </div>
          </div>
    EOT11; //transaction Record ??????






////////////////////////////////////////////////////// ??????/////////////////////////////////////////////////////////////////////////

  
////////////////////////////////////////////////////////////???????????? open menu////////////////////////////////////////////////////////////
if(isset($_SESSION['search_result']) && !empty($_SESSION['search_result']) ){
  $search_result = $_SESSION['search_result'];
  foreach ($search_result as $shopindex => $value) {
    $tmp = $shopindex+1;
    $tmp2 = $shopindex+200000;//?????????????????????????????????????????? id
    echo "
                <!-- Modal -->
                <form action = \"tmp_order.php\" method=\"post\">

                  <!-- ????????????????????????????????????????????????-->
                  <input type=\"hidden\" name=\"shopindex\" value=\"$shopindex\">


                  <div class=\"modal fade\" id=\"$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                    <div class=\"modal-dialog\">

                      <!-- Modal content-->
                      <div class=\"modal-content\">
                        <div class=\"modal-header\">
                          <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                          <h4 class=\"modal-title\">$value[0] menu</h4>
                        </div>
                
                        <div class=\"modal-body\">
                        <!--  -->
                          <div class=\"row\">
                            <div class=\"  col-xs-12\">
                              <table class=\"table\" style=\"margin-top:15px\">
                              <thead>
                                <tr>
                                  <th scope=\"col\">#</th>
                                  <th scope=\"col\">Picture</th>
                                  <th scope=\"col\">meal name</th>                      
                                  <th scope=\"col\">price</th>
                                  <th scope=\"col\">Quantity</th> 
                                  <th scope=\"col\">Order check</th>
                                </tr>
                              </thead>
                              <tbody>";
      $shops = $_SESSION['all_food_in_shops'];
      $shop = $shops[$shopindex];//???????????? Search ????????????????????????

    foreach($shop as $itemindex => $item){
      $tmp2 = $itemindex +1;
      $tmp3 = $tmp2 + 100000 + $shopindex*100;
      echo "                        
                                <tr>
                                  <th scope=\"row\">$tmp2</th>
                                  <td><img src=\"Picture/$item[5]\" width =\"100\" height=\"100\" alt=\"$value[1]\"></td>
                                  <td>$item[1]</td>
                                  <td>$item[2]</td>
                                  <td>$item[3]</td>
                                  <td> <input type=\"button\" value=\"-\" id=\"minus\" onclick=\"minusFunc('number_$tmp3')\">
                                    <input value=\"0\" id=\"number_$tmp3\" name = \"order_number_$itemindex\" >
                                    <input type=\"button\" value=\"+\" id=\"plus\" onclick=\"addFunc('number_$tmp3')\"> </td>
                                </tr>";          

    }
    echo "
                              </tbody>
                            </table>
                            </div>
                          </div>
                        <!--  -->
                        
                        </div>
                        <label class=\"control-label col-sm-1\" for=\"type\">Type</label>
                        <div class=\"col-sm-5\">
                            <select class=\"form-control\" name = \"delivery_or_pick_up\">
                              <option>Delivery</option>
                              <option>Pick up</option>
                            </select>
                        </div>


                        <div class=\"modal-footer\">
                          <input type=\"submit\"  value=\"Calculate\">
                        </div>
                      </div> <!-- div class=\"modal-content\" -->
                    </div>
                  </div>
                </form>
    ";
  }
}



////////////////////////////////////////////////////////////?????? my order ??? order detail/////////////////////////////////////////////////////////
    //for finished filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == "finished"){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
        where orders.u_account = :u_account and o_state='finished'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_finished = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_finished as $myorderindex => $order) {
          $delivery_fee = $order[8];
          $total_price = $order[4];
          $subtotal = $total_price - $delivery_fee;
          $OID=$order[0];
          $o_state = $order[1];
          $tmp = $myorderindex+1;
          $tmp2 = $myorderindex+6000;
          echo "
                      <!-- Modal -->
                        <div class=\"modal fade\" id=\"MyOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                          <div class=\"modal-dialog\">

                            <!-- Modal content-->
                            <div class=\"modal-content\">
                              <div class=\"modal-header\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                <h4 class=\"modal-title\">$order[0] order</h4>
                              </div>
                      
                              <div class=\"modal-body\">
                              <!--  -->
                                <div class=\"row\">
                                  <div class=\"  col-xs-12\">
                                    <table class=\"table\" style=\"margin-top:15px\">
                                    <thead>
                                      <tr>
                                        <th scope=\"col\">#</th>
                                        <th scope=\"col\">Picture</th>
                                        <th scope=\"col\">meal name</th>                      
                                        <th scope=\"col\">price</th>
                                        <th scope=\"col\">Quantity</th> 
                                      </tr>
                                    </thead>
                                    <tbody>";
          //????????????????????????????????????????????????????????????????????????
          $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
          $stmt = $conn -> prepare($sql);
          $stmt -> execute(array('OID'=>$OID));
          $foods = $stmt->fetchALL();
        
          foreach($foods as $foodindex => $food){//?????????????????????????????????
            $tmp2 = $foodindex +1;
            $tmp3 = $tmp2 + 600;
            echo "                        
                                      <tr>
                                        <th scope=\"row\">$tmp2</th>
                                        <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                        <td>$food[4]</td>
                                        <td>$food[2]</td>
                                        <td>$food[1]</td>
                                      </tr>";          

          }
          echo "
                                    </tbody>
                                  </table>
                                  </div>
                                </div>
                                <p>subtotal = $subtotal</p>
                                <p>delivery fee = $delivery_fee</p>
                                <p>Total price = $total_price</p>
                              <!--  -->                          
                              </div>

                            </div> <!-- div class=\"modal-content\" -->
                          </div>
                        </div>

          ";
        }
      }
    } 
    //for not finish filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == 'not finish'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
        where orders.u_account = :u_account and o_state='not finish'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_not_finish = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_not_finish as $myorderindex => $order) {
          $delivery_fee = $order[8];
          $total_price = $order[4];
          $subtotal = $total_price - $delivery_fee;
          $OID=$order[0];
          $o_state = $order[1];
          $tmp = $myorderindex+1;
          $tmp2 = $myorderindex+7000;
          echo "
                      <!-- Modal -->
                        <div class=\"modal fade\" id=\"MyOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                          <div class=\"modal-dialog\">

                            <!-- Modal content-->
                            <div class=\"modal-content\">
                              <div class=\"modal-header\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                <h4 class=\"modal-title\">$order[0] order</h4>
                              </div>
                      
                              <div class=\"modal-body\">
                              <!--  -->
                                <div class=\"row\">
                                  <div class=\"  col-xs-12\">
                                    <table class=\"table\" style=\"margin-top:15px\">
                                    <thead>
                                      <tr>
                                        <th scope=\"col\">#</th>
                                        <th scope=\"col\">Picture</th>
                                        <th scope=\"col\">meal name</th>                      
                                        <th scope=\"col\">price</th>
                                        <th scope=\"col\">Quantity</th> 
                                      </tr>
                                    </thead>
                                    <tbody>";
          //????????????????????????????????????????????????????????????????????????
          $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
          $stmt = $conn -> prepare($sql);
          $stmt -> execute(array('OID'=>$OID));
          $foods = $stmt->fetchALL();
        
          foreach($foods as $foodindex => $food){//?????????????????????????????????
            $tmp2 = $foodindex +1;
            $tmp3 = $tmp2 + 700;
            echo "                        
                                      <tr>
                                        <th scope=\"row\">$tmp2</th>
                                        <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                        <td>$food[4]</td>
                                        <td>$food[2]</td>
                                        <td>$food[1]</td>
                                      </tr>";          

          }
          echo "
                                    </tbody>
                                  </table>
                                  </div>
                                </div>
                                <p>subtotal = $subtotal</p>
                                <p>delivery fee = $delivery_fee</p>
                                <p>Total price = $total_price</p>
                              <!--  -->                          
                              </div>

                            </div> <!-- div class=\"modal-content\" -->
                          </div>
                        </div>

          ";
        }
      }
    }
    //for cancel filter
    if($MyOrderFilter == "ALL" or $MyOrderFilter == 'cancel'){
      $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
          where orders.u_account = :u_account and o_state='cancel'";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('u_account'=>$account));
      if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
        $MyOrder_results_cancel = $stmt -> fetchAll(); //???????????????????????????
        foreach ($MyOrder_results_cancel as $myorderindex => $order) {          
          $delivery_fee = $order[8];
          $total_price = $order[4];
          $subtotal = $total_price - $delivery_fee;
          $OID=$order[0];
          $o_state = $order[1];
          $tmp = $myorderindex+1;
          $tmp2 = $myorderindex+8000;
          echo "
                      <!-- Modal -->
                        <div class=\"modal fade\" id=\"MyOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                          <div class=\"modal-dialog\">

                            <!-- Modal content-->
                            <div class=\"modal-content\">
                              <div class=\"modal-header\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                <h4 class=\"modal-title\">order</h4>
                              </div>
                      
                              <div class=\"modal-body\">
                              <!--  -->
                                <div class=\"row\">
                                  <div class=\"  col-xs-12\">
                                    <table class=\"table\" style=\"margin-top:15px\">
                                    <thead>
                                      <tr>
                                        <th scope=\"col\">#</th>
                                        <th scope=\"col\">Picture</th>
                                        <th scope=\"col\">meal name</th>                      
                                        <th scope=\"col\">price</th>
                                        <th scope=\"col\">Quantity</th> 
                                      </tr>
                                    </thead>
                                    <tbody>";
          //????????????????????????????????????????????????????????????????????????
          $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
          $stmt = $conn -> prepare($sql);
          $stmt -> execute(array('OID'=>$OID));
          $foods = $stmt->fetchALL();
        
          foreach($foods as $foodindex => $food){//?????????????????????????????????
            $tmp2 = $foodindex +1;
            $tmp3 = $tmp2 + 800;
            echo "                        
                                      <tr>
                                        <th scope=\"row\">$tmp2</th>
                                        <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                        <td>$food[4]</td>
                                        <td>$food[2]</td>
                                        <td>$food[1]</td>
                                      </tr>";          

          }
          echo "
                                    </tbody>
                                  </table>
                                  </div>
                                </div>
                                <p>subtotal = $subtotal</p>
                                <p>delivery fee = $delivery_fee</p>
                                <p>Total price = $total_price</p>
                              <!--  -->                          
                              </div>

                            </div> <!-- div class=\"modal-content\" -->
                          </div>
                        </div>

          ";
        }
      }
    }
/////////////////////////////////////////////////////////////?????? my order ??? order detail ??????//////////////////////////////////////////////////////




///////////////////////////////////////////////?????? shop order ??? order detail/////////////////////////////////////////////////////////
//for finished filter
if($ShopOrderFilter == "ALL" or $ShopOrderFilter == "finished"){
  $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
  where orders.s_name = :s_name and o_state='finished'";
  $stmt = $conn -> prepare($sql);
  $stmt -> execute(array('s_name'=>$s_name));
  if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
    $ShopOrder_results_finished = $stmt -> fetchAll(); //???????????????????????????
    foreach ($ShopOrder_results_finished as $shoporderindex => $order) {          
      $delivery_fee = $order[8];
      $total_price = $order[4];
      $subtotal = $total_price - $delivery_fee;
      $OID=$order[0];
      $o_state = $order[1];
      $tmp = $shoporderindex+1;
      $tmp2 = $shoporderindex+3000;
      echo "
                  <!-- Modal -->
                    <div class=\"modal fade\" id=\"ShopOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                      <div class=\"modal-dialog\">

                        <!-- Modal content-->
                        <div class=\"modal-content\">
                          <div class=\"modal-header\">
                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                            <h4 class=\"modal-title\">order</h4>
                          </div>
                  
                          <div class=\"modal-body\">
                          <!--  -->
                            <div class=\"row\">
                              <div class=\"  col-xs-12\">
                                <table class=\"table\" style=\"margin-top:15px\">
                                <thead>
                                  <tr>
                                    <th scope=\"col\">#</th>
                                    <th scope=\"col\">Picture</th>
                                    <th scope=\"col\">meal name</th>                      
                                    <th scope=\"col\">price</th>
                                    <th scope=\"col\">Quantity</th> 
                                  </tr>
                                </thead>
                                <tbody>";
      //????????????????????????????????????????????????????????????????????????
      $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('OID'=>$OID));
      $foods = $stmt->fetchALL();

      foreach($foods as $foodindex => $food){//?????????????????????????????????
        $tmp2 = $foodindex +1;
        $tmp3 = $tmp2 + 300;
        echo "                        
                                  <tr>
                                    <th scope=\"row\">$tmp2</th>
                                    <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                    <td>$food[4]</td>
                                    <td>$food[2]</td>
                                    <td>$food[1]</td>
                                  </tr>";          

      }
      echo "
                                </tbody>
                              </table>
                              </div>
                            </div>
                            <p>subtotal = $subtotal</p>
                            <p>delivery fee = $delivery_fee</p>
                            <p>Total price = $total_price</p>
                          <!--  -->                          
                          </div>

                        </div> <!-- div class=\"modal-content\" -->
                      </div>
                    </div>

      ";
    }
  }
} 
//for not finish filter
if($ShopOrderFilter == "ALL" or $ShopOrderFilter == 'not finish'){
  $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
    where orders.s_name = :s_name and o_state='not finish'";
  $stmt = $conn -> prepare($sql);
  $stmt -> execute(array('s_name'=>$s_name));
  if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
    $ShopOrder_results_not_finish = $stmt -> fetchAll(); //???????????????????????????
    foreach ($ShopOrder_results_not_finish as $shoporderindex => $order) {          
      $delivery_fee = $order[8];
      $total_price = $order[4];
      $subtotal = $total_price - $delivery_fee;
      $OID=$order[0];
      $o_state = $order[1];
      $tmp = $shoporderindex+1;
      $tmp2 = $shoporderindex+4000;
      echo "
                  <!-- Modal -->
                    <div class=\"modal fade\" id=\"ShopOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                      <div class=\"modal-dialog\">

                        <!-- Modal content-->
                        <div class=\"modal-content\">
                          <div class=\"modal-header\">
                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                            <h4 class=\"modal-title\">order</h4>
                          </div>
                  
                          <div class=\"modal-body\">
                          <!--  -->
                            <div class=\"row\">
                              <div class=\"  col-xs-12\">
                                <table class=\"table\" style=\"margin-top:15px\">
                                <thead>
                                  <tr>
                                    <th scope=\"col\">#</th>
                                    <th scope=\"col\">Picture</th>
                                    <th scope=\"col\">meal name</th>                      
                                    <th scope=\"col\">price</th>
                                    <th scope=\"col\">Quantity</th> 
                                  </tr>
                                </thead>
                                <tbody>";
      //????????????????????????????????????????????????????????????????????????
      $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('OID'=>$OID));
      $foods = $stmt->fetchALL();

      foreach($foods as $foodindex => $food){//?????????????????????????????????
        $tmp2 = $foodindex +1;
        $tmp3 = $tmp2 + 400;
        echo "                        
                                  <tr>
                                    <th scope=\"row\">$tmp2</th>
                                    <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                    <td>$food[4]</td>
                                    <td>$food[2]</td>
                                    <td>$food[1]</td>
                                  </tr>";          

      }
      echo "
                                </tbody>
                              </table>
                              </div>
                            </div>
                            <p>subtotal = $subtotal</p>
                            <p>delivery fee = $delivery_fee</p>
                            <p>Total price = $total_price</p>
                          <!--  -->                          
                          </div>

                        </div> <!-- div class=\"modal-content\" -->
                      </div>
                    </div>

      ";
    }
  }

}
//for cancel filter
if($ShopOrderFilter == "ALL" or $ShopOrderFilter == 'cancel'){
  $sql ="select OID, o_state, o_start_time, o_end_time, o_total_price, o_type, s_name, u_account, o_delivery_fee from orders 
  where orders.s_name = :s_name and o_state='cancel'";
  $stmt = $conn -> prepare($sql);
  $stmt -> execute(array('s_name'=>$s_name));
  if($stmt ->rowcount() > 0){ //??????????????????????????????????????????
    $ShopOrder_results_cancel = $stmt -> fetchAll(); //???????????????????????????
    foreach ($ShopOrder_results_cancel as $shoporderindex => $order) {          
      $delivery_fee = $order[8];
      $total_price = $order[4];
      $subtotal = $total_price - $delivery_fee;
      $OID=$order[0];
      $o_state = $order[1];
      $tmp = $shoporderindex+1;
      $tmp2 = $shoporderindex+5000;
      echo "
                  <!-- Modal -->
                    <div class=\"modal fade\" id=\"ShopOrder$tmp2\"  data-backdrop=\"static\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
                      <div class=\"modal-dialog\">

                        <!-- Modal content-->
                        <div class=\"modal-content\">
                          <div class=\"modal-header\">
                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                            <h4 class=\"modal-title\">order</h4>
                          </div>
                  
                          <div class=\"modal-body\">
                          <!--  -->
                            <div class=\"row\">
                              <div class=\"  col-xs-12\">
                                <table class=\"table\" style=\"margin-top:15px\">
                                <thead>
                                  <tr>
                                    <th scope=\"col\">#</th>
                                    <th scope=\"col\">Picture</th>
                                    <th scope=\"col\">meal name</th>                      
                                    <th scope=\"col\">price</th>
                                    <th scope=\"col\">Quantity</th> 
                                  </tr>
                                </thead>
                                <tbody>";
      //????????????????????????????????????????????????????????????????????????
      $sql = "select OID, amount, f_current_price, f_picture, f_name, s_name, f_picture_name from contain where contain.OID = :OID";
      $stmt = $conn -> prepare($sql);
      $stmt -> execute(array('OID'=>$OID));
      $foods = $stmt->fetchALL();

      foreach($foods as $foodindex => $food){//?????????????????????????????????
        $tmp2 = $foodindex +1;
        $tmp3 = $tmp2 + 500;
        echo "                        
                                  <tr>
                                    <th scope=\"row\">$tmp2</th>
                                    <td><img src=\"Picture/$food[6]\" width =\"100\" height=\"100\" alt=\"$food[4]\"></td>
                                    <td>$food[4]</td>
                                    <td>$food[2]</td>
                                    <td>$food[1]</td>
                                  </tr>";          

      }
      echo "
                                </tbody>
                              </table>
                              </div>
                            </div>
                            <p>subtotal = $subtotal</p>
                            <p>delivery fee = $delivery_fee</p>
                            <p>Total price = $total_price</p>
                          <!--  -->                          
                          </div>

                        </div> <!-- div class=\"modal-content\" -->
                      </div>
                    </div>

      ";
    }
  }

}
///////////////////////////////////////////////?????? shop order ??? order detail ??????/////////////////////////////////////////////////////////




    echo"
    </div><!-- for <div class=\"tab-content\">  end -->
    </div><!-- for <div class=\"container\"> end -->
    <script> <!-- ??????????????? head ????????? script ???????????????????????? -->
    $(document).ready(function () {
    $(\".nav-tabs a\").click(function () {
        $(this).tab('show');
      });
    });
    </script>
    </body>

    </html>
    ";


?>


