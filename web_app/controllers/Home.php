<?php
use database\libs\MyPDO;

class Home {

    public function Index() {
        echo "<h3>PHP_web_api</h3> <br>";

        // Testing DB connection
        $DB = new MyPDO();

        echo "<b>DB connection success !!</b>";
    }
}
 ?>
