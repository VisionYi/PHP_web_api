<div>
    <h1>404 NOT FOUND!</h1>
    <h3>不好意思，找不到您要的網頁</h3>

    <?php
        // 顯示目前的網址
        echo "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "<br><br>";

        if (isset($this)) {
            $this->errorMessage();
        }

        // 以下皆為測試用途
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET)) {
            echo "<br>GET~~";
            var_export($_GET);
        }

        if (!empty($_REQUEST)) {
            echo "<br>REQUEST~~";
            var_export($_REQUEST);
        }

        if (isset($_GET['page'])) {
            echo "<br>GET: page = ".$_GET['page'];
        }
    ?>
</div>
