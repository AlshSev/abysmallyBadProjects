<?php include("login_check.php"); if ($logged_in) : 
    if (isset($_GET['new_id'])) {
        exec("python3 ../../back/redis-utils/add_contest.py " . $_GET['div'] . " " . $_GET['new_id']);
        header("Refresh:0; url=" . basename($_SERVER['PHP_SELF']) . "?div=" . $_GET['div']);
    }
    else {
        // $file = "../../data/" . $params['div'] . "/contests/descriptions.txt";
        // $file_content = file_get_contents($file);   // raw input
        exec("python3 ../../back/redis-utils/get_descriptions.py " . $_GET["div"], $contests_list);
        // var_dump($contests_list);
        // $contests_list = explode("\n", $file_content); // parse to array of strings
        $contests = array();
        for($i = 0; $i < count($contests_list); $i++){ // fill students array
            $tmp = explode(",,", $contests_list[$i]);
            $contests[$i] = array('name' => $tmp[0],
                                    'id' => $tmp[1],
                                    'author' => $tmp[2],
                                    'duration' => $tmp[3]);
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
        <?php if(!isset($_GET['new_id'])) : ?>
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
                    if (count($contests))
                    foreach ($contests[0] as &$desc) {
                        echo "<td style=\"text-align: center\">" . $desc . "</td>\n";
                    }
                    echo "</tr>";
                    echo "</thead>";
                    for($i = 1; $i < count($contests); $i++){
                        echo "<tr>";
                        echo "<td>" . ($i) . "</td>\n";
                        echo "<td>" . $contests[$i]['name'] . "</td>";
                        echo "<td><a href=\"./contest.php?div=" . $_GET["div"] . "&id=" . $contests[$i]['id'] . "\">" . $contests[$i]['id'] . "</a></td>\n";
                        echo "<td><a href=\"https://codeforces.com/profile/" . $contests[$i]['author'] ."\">" . $contests[$i]['author'] . "</a></td>\n";
                        $hours = intdiv((int)$contests[$i]['duration'], 3600);
                        $minutes = intdiv(((int)$contests[$i]['duration'] % 60), 60);
                        $duration = $hours . "ч. " . $minutes . "м.";
                        echo "<td>" . $duration . "</td>";
                        echo "</tr>";
                    }
                ?>
            </table>
            <br>
            <details class="" style="font-family:Arial;">
                <summary>Добавить контест</summary>
                <form>
                    <input type="hidden" name="div" value="<?php echo $_GET["div"]; ?>">
                    <input type="text" name="new_id" placeholder="id контеста">
                    <input type="submit" value="Добавить" method="get">
                </form>
            </details>
        </main>
        <?php endif; ?>
    </body>
    <?php else : include("login.php") ?>
    <?php endif; ?>
</html>
