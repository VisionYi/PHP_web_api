<?php

class Home extends Controller {

    public function Index(){
        echo "PHP_web_api <br><br>";

        require_once 'database/libs/MyPDO.php';
        $DB = new MyPDO();

        echo "<b>DB connection success !!</b>";
    }
}
 ?>
