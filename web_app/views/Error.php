<div class="well ">
    <h1>404 NOT FOUND!</h1>
    <h3>不好意思，找不到您要的網頁</h3>

    <?php
        echo "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . "<br><br>";
        $this->error_message();
    ?>
</div>
