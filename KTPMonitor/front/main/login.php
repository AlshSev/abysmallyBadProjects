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
            Please log in first<br>
            <form action="<?php basename($_SERVER['PHP_SELF']);?>" method="get">
                <?php 
                foreach($_GET as $key => $val) {
                    if ($key == "login")
                        continue;
                    if ($key == "password")
                        continue;
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '">';
                }
                ?>
                <input type="text" name="login"><br>
                <input type="password" name="password"><br>
                <input type="submit" value="Log In">
            </form>
        </center>
    </main>
</body>