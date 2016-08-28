<?php
//  /*第1種方式*/
//
// $dbms = 'mysql';           //數據庫類型
// $host = 'localhost';       //數據庫主機名
// $port = 3306;              //數據庫port
// $dbName = 'web_homework';  //使用的數據庫
// $encode = 'utf8';          //數據庫編碼方式(字符集)
// $user = 'root';            //數據庫連接用戶名
// $pass = 'mysql';           //對應的密碼

// $dsn = "$dbms:host=$host;dbname=$dbName;port=$port;charset=$encode";
// try {
//     $pdo = new PDO($dsn, $user, $pass);
//     echo "PDO connection success !! <br><br>";
// } catch (PDOException $e) {
//     echo "PDO connection failed !! <br>Error : " . $e->getMessage();
//     exit;
// }
// $table = 'pdo_test';
// $sql="INSERT INTO $table (id,name,age,skills) VALUES (20,'新增第20位','088','content')";
// $pdo->exec($sql);
// echo "insert success !~!" . '<br>';
//
// ----------------------------------------------------------------------------------------
// /*第2種方式*/
//
// $mypdo = new MyPDO();
// $table = 'pdoTest';

// $skill = '%angular%';
// $sql = "SELECT * FROM $table WHERE skills LIKE :_skill ORDER BY name LIMIT :_limit" ;
// $query = $mypdo->dbQuery($sql ,[
//         ':_skill' => $skill ,
//         ':_limit' => 10
// ]);
// foreach ($query as $row) {
//     echo $row['name'] . ' : ' .$row['skills'] . '<br>';
// }

// $select = [
//         'name' => '新增test!! 修改1',
//         'age'  => '1000',
//         'skills' => 'database U'
// ];
// $mypdo->dbUpdate($table ,$select ,"id = 19");

// $mypdo->showError();
// $mypdo->stmt = null;
// $mypdo->pdo = null;
//
// ----------------------------------------------------------------------------------------
// /*第3種方式*/
//
// require_once 'libs/MyPDO.php';

// 取得Post的資料
// $dataJson = file_get_contents("php://input");
// $data = json_decode($dataJson,true);

// $mypdo = new MyPDO();
// $table = 'pdoTest';

// $query = $mypdo->dbQuery("SELECT * FROM $table WHERE id=" . $data['id']);
// $mypdo->closeDB();

// echo json_encode($query[0],JSON_UNESCAPED_UNICODE);
 ?>
