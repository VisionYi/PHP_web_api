<?php

class Home {

    public function Index() {
        echo "<h3>PHP_web_api</h3> <br><br>";

        // Testing DB connection
        require_once 'database/libs/MyPDO.php';
        $DB = new MyPDO();

        echo "<b>DB connection success !!</b>";
    }
}
 ?>
