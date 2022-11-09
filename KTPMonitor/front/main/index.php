<!DOCTYPE html>
<html lang="en">
    <?php include("login_check.php"); if($logged_in) : ?>
    <?php 
        if (isset($_GET['new_div'])) {
            $res = exec("python3 ../../back/redis-utils/add_div.py " . $_GET['new_div']);
            header("Refresh:0; url=" . basename($_SERVER['PHP_SELF']));
        }
    ?>
    <head class="">
        <meta charset="utf-8">
        <title>KTPMonitor</title>
        <link rel="shortcut icon" href="../assets/PageLogo.png"/>
    </head>
    <body style="position:absolute; top:50%; left: 50%">
        <main style="transform:translate(-50%, -50%)">
            <center class="" style="font-size:600%; font-family:Arial;">
                <nobr>
                    <img src="../assets/Logo.svg" height="150" style="transform: translate(0px, 12%)">
                    KTP<b style="color:#A9C4EB">Monitor</b>
                </nobr>
            </center>
            <br>
            <center style="font-family:\'Courier New\', monospace;; font-size:300%;">
            <?php
                $res = exec("python3 ../../back/redis-utils/get_divs.py");
                $res = substr($res, 2, strlen($res) - 4);
                if ($res != "") {
                    $divs = explode("', '", $res);
                    foreach($divs as $k => $div)
                        echo '<a href="./division.php?div=' . $div . '" style="color:#CCCC00">Div ' . $div . '</a><br>';
                }
            ?>
            <br>
            <details class="" style="font-size:50%; font-family:Arial;">
                <summary>Добавить дивизион</summary>
                <form>
                    <input type="text" name="new_div">
                    <input type="submit" value="Добавить" method="get">
                </form>
            </details>
            </center>
        </main>
    </body>
    <?php else : include("login.php") ?>
    <?php endif ?>
</html>
