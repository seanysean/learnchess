<?php
session_start();
require "../../include/functions.php";
if (!$l && !isset($_SESSION['nextpuzzle'])) {
    $_SESSION['nextpuzzle'] = 1;
    $_SESSION['completed'] = 0;
}
$next = $_SESSION['nextpuzzle'];
if ($l) {
    $me = $_SESSION['userid'];
    $sql = "SELECT rating FROM `puzzles_history` WHERE user='$me' AND puzzle='$next'";
    $result = mysqli_query($connection,$sql);
    if (mysqli_num_rows($result) > 0 || $next === 0) {
        //echo "Hello!";
        $n = mysqli_query($connection,"SELECT puzzle FROM `puzzles_history` WHERE user='$me' AND `profile`='0' ORDER BY id DESC LIMIT 1")->fetch_assoc()['puzzle'];
        //echo $n;
        $limit = $n+20;
        $found = false;
        while($n < $limit && !$found) {
            $n++;
            $sql = "SELECT id FROM `puzzles_approved` WHERE removed='0' AND id='$n' AND author_id!='$me'";
            //echo $sql."<br />";
            $result = mysqli_query($connection,$sql);
            if (mysqli_num_rows($result) > 0) {
                $candidate = mysqli_query($connection,$sql)->fetch_assoc()['id'];
                //echo $candidate;
                $sql = "SELECT rating FROM `puzzles_history` WHERE puzzle='$candidate' AND user='$me'";
                $count = mysqli_num_rows(mysqli_query($connection,$sql));
                if ($count === 0) {
                    //echo "Yes!$candidate";
                    $found = true;
                    $_SESSION['nextpuzzle'] = $candidate + 0;
                    $sql = "UPDATE `users` SET nextpuzzle='$candidate' WHERE id='$me'";
                    $result = mysqli_query($connection,$sql);
                    if ($result) {
                        header("Location: /puzzles/view/$candidate");
                    } else {
                        echo "Something went wrong while trying to set your next puzzle. Please <a href=\"https://github.com/seanysean/LearnChess/issues\" target=\"_blank\">open an issue</a> so that this can get fixed.</a> <br />";
                    }
                }
            }
        }
        if (!$found) {
            $_SESSION['nextpuzzle'] = 0; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>No more puzzles • LearnChess</title>
        <?php include_once "../../include/head.php" ?>
    </head>
    <body<?php include_once "../../include/attributes.php"?>>
        <div class="top"><?php include_once "../../include/topbar.php" ?></div>
        <div class="page">
            <div class="main full">
                <div class="block">
                    <h1 class="block-title">No more puzzles!</h1>
                    <p>Good job on finishing all of the puzzles!</p>
                    <p>But please check back again soon, because other users can contribute puzzles.</p>
                </div>
            </div>
        </div>
        <footer><?php include_once "../../include/footer.php" ?></footer>
        <script src="/js/global.js"></script>
    </body>
</html>
<?php }
    } else {
        header("Location: /puzzles/view/$next");
    }
} else {
    if (!$l && $_SESSION['completed'] == $_SESSION['nextpuzzle']) {
        $_SESSION['nextpuzzle'] = ++$next;
    }
    header("Location: /puzzles/view/$next");
    //echo $next.' '.$_SESSION['nextpuzzle'];
}
