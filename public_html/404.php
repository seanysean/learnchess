<?php
session_start();
include "../include/functions.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Page not found • LearnChess</title>
        <?php include "../include/head.php" ?>
        <style>
        .block {
            text-align: center;
            margin-bottom: 0 !important;
        }
        .num {
            font-size: 7em;
        }
        .links {
            margin-top: 30px;
        }
        .links a {
            font-weight: 100 !important;
            color: #888;
            font-family: sans-serif !important;
            font-size: 14px;
            margin-left: 10px;
        }
        .links a:hover {
            color: #1d7bd3;
        }
        .block .error {
            font-size: 20px;
            margin: 10px 0;
        }
        .block .report {
            margin: 3px 0;
        }
        </style>
    </head>
    <body<?php include_once "../include/attributes.php" ?>>
        <div class="top">
        <?php include "../include/topbar.php" ?>
        </div>
        <div class="page full">
            <div class="main">
                <div class="block">
                    <h1 class="block-title center num">404</h1>
                    <p class="error">Page not found</p>
                    <p class="report">You can report this <a href="/contact">here</a>.</p>
                    <div class="links">
                        <a href="https://github.com/seanysean/LearnChess" target="_blank"><i class="fa fa-github"></i> Open source</a>
                    </div>
                </div>
            </div>
        </div>
        <footer>
        <?php include "../include/footer.php" ?>
        </footer>
        <script src="/js/global.js"></script>
    </body>
</html>
