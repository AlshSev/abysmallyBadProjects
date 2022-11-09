<?php include("login_check.php"); if ($logged_in) :
    if (isset($_GET['to_rem'])) {
        exec("python3 ../../back/redis-utils/rm_pupil.py " . $_GET['div'] . " " . $_GET['to_rem']);
        header("Refresh:0; url=" . basename($_SERVER['PHP_SELF']) . "?div=" . $_GET['div']);
    }
    else if (isset($_GET['fio'])) {
        exec("python3 ../../back/redis-utils/add_pupil.py " . $_GET['div'] . 
            " '" . $_GET['nick'] . "'" . 
            " '" . $_GET['fio'] . "'" . 
            " '" . $_GET['birth'] . "'" . 
            " '" . $_GET['school'] . "'" . 
            " '" . $_GET['grade'] . "'" . 
            " '" . $_GET['city'] . "'", $ret);
        header("Refresh:0; url=" . basename($_SERVER['PHP_SELF']) . "?div=" . $_GET['div']);
    }
    else {
        $to_parse = exec("python3 ../../back/redis-utils/get_pupils.py " . $_GET["div"]);
        $pupils_list = explode(", ", substr($to_parse, 1, strlen($to_parse) - 2));
        // var_dump($pupils_list);

        // $file = "../../data/" . $_GET["div"] . "/people/students.txt";
        // $file_content = file_get_contents($file);   // raw input
        // $raw_students = explode("\n", $file_content); // parse to array of strings

        $students = array();
        $students[0] = array("Имя", "Ник", "Дата рождения", "Школа", "Класс", "Город", "");
        $i = 1;
        if (strlen($pupils_list[0]))
            foreach($pupils_list as &$pupil) { // fill students array
                $nick = substr($pupil, 1, strlen($pupil) - 2);
                $data = explode(",,", exec("python3 ../../back/redis-utils/get_pupil.py " . $nick));
                $students[$i] = array($data[0], $nick, $data[1], $data[2], $data[3], $data[4]);
                $i += 1;
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
            <h style="font-size: 350%"><a style="text-decoration:none; color:black;" href="./index.php">Div</a> <?php echo $_GET["div"]; ?>/</h>
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
                    foreach($students[0] as &$header){
                        echo "<td style=\"text-align: center\">" . $header . "</td>\n";
                    }
                    echo "</tr>";
                    echo "</thead>";
                    for($i = 1; $i < count($students); $i++){
                        echo "<tr>";
                        echo "<td>" . $i . "</td>\n";
                        echo "<td>" . $students[$i][0] . "</td>\n";
                        echo "<td><a href=\"https://codeforces.com/profile/" . $students[$i][1] ."\">" . $students[$i][1] . "</a></td>\n";
                        echo "<td>" . $students[$i][2] . "</td>\n";
                        echo "<td>" . $students[$i][3] . "</td>\n";
                        echo "<td>" . $students[$i][4] . "</td>\n";
                        echo "<td>" . $students[$i][5] . "</td>\n";

                        echo "<td><a href=\"" . basename($_SERVER['PHP_SELF']) . "?div=" . $_GET['div'] . "&to_rem=" . $students[$i][1] . "\" onclick=\"return confirm('Вы действительно хотите удалить ученика " . $students[$i][0] . "?');\">x</a></td>\n";

                        echo "</tr>";
                    }
                ?>
            </table>
            <br>
            <details class="" style="font-family:Arial;">
                <summary>Добавить студента</summary>
                <form>
                    <input type="hidden" name="div" value="<?php echo $_GET["div"]; ?>">
                    <input type="text" name="fio" placeholder="ФИО"><br>
                    <input type="text" name="nick" placeholder="Ник на Codeforces"><br>
                    <input type="text" name="birth" placeholder="Дата рождения"><br>
                    <input type="text" name="school" placeholder="Школа"><br>
                    <input type="text" name="grade" placeholder="Класс"><br>
                    <input type="text" name="city" placeholder="Город проживания"><br>
                    <input type="submit" value="Добавить" method="get">
                </form>
            </details>
        </main>
    </body>
    <?php else : include("login.php") ?>
    <?php endif ?>
</html>
