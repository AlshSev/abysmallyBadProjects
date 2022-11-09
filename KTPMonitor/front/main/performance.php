<?php include("login_check.php"); if ($logged_in) : 
    exec("python3 ../../back/redis-utils/get_perfomance.py " . $_GET["div"], $students_list);
    //$file = "../../data/" . $_GET["div"] . "/people/stats.txt";
    //$file_content = file_get_contents($file);   // raw input
    // $students_list = explode("\n", $file_content);
    $students = array();
    for($i = 0; $i < count($students_list); $i++){ // fill students array
        $tmp = explode(",,", $students_list[$i]);
        $students[$i] = array('name' => $tmp[0],
                                'nik' => $tmp[1]);
        for($j = 2; $j < count($tmp); $j++){
            $students[$i][$j] = $tmp[$j];
        }
    }
endif ?>

<!DOCTYPE html>
<html lang="en">
    <?php if($logged_in) : ?>
    <head class="">
        <meta charset="utf-8">
        <title>Contests</title>
        <link rel="shortcut icon" href="../assets/PageLogo.png"/>
        <link rel="stylesheet" href="../css/infotables.css">
        <link rel="stylesheet" href="../css/divpage.css">
    </head>
    <body>
        <main>
            <h style="font-size: 350%"><a style="text-decoration:none; color:black;" href="./index.php">Div</a> <?php if($logged_in) : echo $_GET["div"]; endif ?>/</h>
            <br><br>
            <nav>
                <a href="./division.php?div=<?php echo $_GET["div"]; ?>" class="general">общее</a>
                <a href="./contests.php?div=<?php echo $_GET["div"]; ?>" class="contests">контесты</a>
                <a href="./students.php?div=<?php echo $_GET["div"]; ?>" class="students">студенты</a>
                <a href="./performance.php?div=<?php echo $_GET["div"]; ?>" class="performance">успеваемость</a>
            </nav>
            <br>
            <table style="width:50%;">
                <?php
                    echo "<thead>";
                    echo "<tr><td>#</td>";
                    foreach ($students[0] as &$desc) {
                        echo "<td style=\"text-align: center\">" . $desc . "</td>\n";
                    }
                    echo "</tr>";
                    echo "</thead>";
                    for($i = 1; $i < count($students); $i++){
                        echo "<tr>";
                        echo "<td>" . $i . "</td>\n";
                        echo "<td>" . $students[$i]['name'] . "</td>";
                        echo "<td><a href=\"https://codeforces.com/profile/" . $students[$i]['nik'] . "\">" . $students[$i]['nik'] . "</a></td>\n";
                        for($j = 2; $j < count($students[$i]); $j++){
                            echo "<td bgcolor=\"D5E8D4\" style=\"text-align: center;\"><b style=\"color:#009900; font-size:120%\">" . $students[$i][$j] . "</b></td>\n";
                        }
                        echo "</tr>";
                    }
                ?>
            </table>
        </main>
    </body>
    <?php else : include("login.php") ?>
    <?php endif; ?>
</html>
