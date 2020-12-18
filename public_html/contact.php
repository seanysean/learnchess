<?php
session_start();
include "../include/functions.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Contact • LearnChess</title>
        <?php include "../include/head.php" ?>
        <meta name="description" content="Contact information for LearnChess.">
    </head>
    <body<?php include_once "../include/attributes.php" ?>>
        <div class="top">
        <?php include "../include/topbar.php" ?>
        </div>
        <div class="page">
            <div class="main center">
                <div class="block">
                    <h1 class="block-title"><i class="fa fa-envelope"></i> Contact LearnChess</h1>
                    <p><a rel="nofollow" href="https://lichess.org/inbox/new?user=seanysean">Message Sean on lichess <i class="fa fa-external-link"></i></a></p>
                </div>
            </div>
        </div>
        <footer>
        <?php include "../include/footer.php" ?>
        </footer>
        <script src="js/global.js"></script>
    </body>
</html>
