<?php include("login_check.php"); if ($logged_in) : 
    if (isset($_GET['rm_con'])) {
        $res = exec("python3 ../../back/redis-utils/rm_contest.py " . $_GET['div'] . " " . $_GET['rm_con'], $tmp);
        // var_dump($res);
        // var_dump($tmp);
        // var_dump("python3 ../../back/redis-utils/rm_contest.py " . $_GET['div'] . " " . $_GET['rm_con']);
        header("Refresh:0; url=./contests.php?div=" . $_GET['div']);
    } else {
        exec("python3 ../../back/redis-utils/get_contest.py " . $_GET['id'], $raw_students);
        // var_dump($raw_students);
        // $raw_students = explode("\n", $file_content); // parse to array of strings

        $students = array();
        for($i = 0; $i < count($raw_students); $i++){ // fill students array
            $tmp = explode(",,", $raw_students[$i]);
            $students[$i]  = array('name' => $i > 0 ? exec("python3 ../../back/redis-utils/get_name.py " . $tmp[0]) : "Имя",
                                    'nik' => $tmp[0],
                                    'sum' => $tmp[1]);
            for($j = 3; $j < count($tmp); $j++){
                $students[$i][$j] = $tmp[$j];
            }
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
        <?php if(!isset($_GET['rm_con'])) : ?>
        <main>
            <h style="font-size: 350%"><a style="text-decoration:none; color:black;" href="./index.php">Div</a> <?php echo $_GET["div"]; ?>/</h>
            <br><br>
            <nav>
                <a href="./division.php?div=<?php echo $_GET["div"]; ?>" class="general">общее</a>
                <a href="./contests.php?div=<?php echo $_GET["div"]; ?>" class="contests">контесты</a>
                <a href="./students.php?div=<?php echo $_GET["div"]; ?>" class="students">студенты</a>
                <a href="./performance.php?div=<?php echo $_GET["div"]; ?>" class="performance">успеваемость</a>
            </nav>
            <br>
            <table style="width:70%;">
                <?php
                    echo "<thead>";
                    echo "<tr><td>#</td></td>";
                    foreach($students[0] as &$header){
                        echo "<td style=\"text-align: center\">" . $header . "</td>";
                    }
                    echo "</tr>";
                    echo "</thead>";
                    for($i = 1; $i < count($students); $i++){
                        echo "<tr>";
                        echo "<td>" . $i . "</td>\n";
                        echo "<td>" . $students[$i]['name'] . "</td>\n";
                        echo "<td><a href=\"https://codeforces.com/profile/" . str_replace('*', '', $students[$i]['nik']) . "\">" . $students[$i]['nik'] . "</a></td>\n";
                        echo "<td>" . $students[$i]['sum'] . "</td>\n";
                        for($j = 3; $j < count($students[$i]); $j++){
                            echo "<td style=\"text-align: center\">" . $students[$i][$j] . "</td>\n";
                        }
                        echo "</tr>\n";
                    }
                ?>
            </table>
        </main>
        <br>
        <details>
            <summary>Удалить контест</summary>
            <form>
                <input type="hidden" name="div" value="<?php echo $_GET['div']; ?>">
                <input type="hidden" name="rm_con" value="<?php echo $_GET['id']; ?>">
                <input type="submit" value="Удалить" method="get">
            </form>
        </details>
        <?php endif; ?>
    </body>
    <?php else : include("login.php") ?>
    <?php endif; ?>
</html>
