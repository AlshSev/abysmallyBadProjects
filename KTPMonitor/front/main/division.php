<!DOCTYPE html>
<html lang="en">
    <?php include("login_check.php"); if($logged_in) : ?>
    <?php
        if (isset($_GET['rm_div'])) {
            $res = exec("python3 ../../back/redis-utils/rm_div.py " . $_GET['rm_div']);
            header("Refresh:0; url='./index.php'");
        }
    ?>
    <?php
        if (isset($_GET['div'])) :
    ?>
    <head class="">
        <meta charset="utf-8">
        <?php echo "<title>Division " . $_GET["div"] . "</title>" ?>
        <link rel="shortcut icon" href="../assets/PageLogo.png"/>
        <link rel="stylesheet" href="../css/divpage.css">
    </head>
    <body>
        <h style="font-size: 350%"><a style="text-decoration:none; color:black;" href="./index.php">Div </a>
        <?php echo $_GET["div"] . "/"; ?>
        </h>
        <br><br>
        <nav>
            <a href="./division.php?div=<?php echo $_GET["div"]; ?>" class="general">общее</a>
            <a href="./contests.php?div=<?php echo $_GET["div"]; ?>" class="contests">контесты</a>
            <a href="./students.php?div=<?php echo $_GET["div"]; ?>" class="students">студенты</a>
            <a href="./performance.php?div=<?php echo $_GET["div"]; ?>" class="performance">успеваемость</a>
        </nav>

        <h1 style="">Школьники див <?php echo $_GET["div"]; ?>.</h1>
        <p>
            Год  обучения: 2021-2022<br>
            Учеников: TODO<br>
            Текущая тема: TODO<br>
        </p>
        <div>Преподаватели:</div>
        <br>
        <div style="margin-left:10%; font-weight:bold">
            <span style="color:#000099">TODO</span>
       </div>
       <br>
       <details>
            <summary>Удалить дивизион</summary>
            <form>
                <input type="hidden" name="rm_div" value="<?php echo $_GET["div"]; ?>">
                <input type="submit" value="Удалить" method="get">
            </form>
        </details>
    </body>
    <?php endif ?>
    <?php else : include("login.php") ?>
    <?php endif ?>
</html>
