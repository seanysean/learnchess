<?php
session_start();
$is_preview = isset($type) && $type === 'preview';
if ($is_preview) {
    $more_js_info = '"puzzlePreview":true';
}
$url_extension = $is_preview ? '' : '../';
include "$url_extension../../include/functions.php";
$table = $is_preview ? 'puzzles_to_review' : 'puzzles_approved';
$sql = "SELECT * FROM `$table` WHERE id='$pID'";
$result = mysqli_query($connection,$sql);
if ($result) {
    $res = $result->fetch_assoc();
    $pgn = $res['pgn'];
    $fen = $res['fen'];
    $trophies = 0;
    $moreThan1 = "0 times";
    $removed = false;
    if (!$is_preview) {
        $trophies = $res['trophies'];
        $moreThan1 = $trophies > 1 ? "$trophies times":'once';
        $removed = $res['removed'] === '0' ? false : true;
    }
    $authorid = $res['author_id'];
    $author = mysqli_query($connection,"SELECT username FROM `users` WHERE id='$authorid'")->fetch_assoc()['username'];
}
if ($pgn === NULL) {
    $removed = true;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php if ($is_preview) { echo 'Preview '; } ?> Puzzle <?php echo $pID ?> • LearnChess</title>
        <?php include_once "$url_extension../../include/head.php"; ?>
        <?php if (!$is_preview) { ?>
        <meta name="description" content="Solve a chess puzzle created by <?php echo $author ?> on LearnChess.<?php if ($trophies > 0) { echo " This puzzle has been given a trophy $moreThan1."; } ?>">
        <?php } ?>
        <link href="/css/chessground.css" type="text/css" rel="stylesheet">
        <link href="/css/puzzles.css" type="text/css" rel="stylesheet">
        <link href="/css/popup.css" type="text/css" rel="stylesheet">
    </head>
    <body<?php include_once "$url_extension../../include/attributes.php" ?>>
        <div class="top">
            <?php include_once "$url_extension../../include/topbar.php"; ?>
        </div>
        <div class="page <?php if($removed) { echo "center"; } else { echo "has-header"; } ?>">
            <?php if(!$removed) { ?>
            <div class="block">
                <h1 class="block-title center"><i class="fa fa-puzzle-piece"></i> Puzzle <?php echo $pID; if ($is_preview) { echo '<span style="opacity:0.7"> Preview</span>'; } ?></h1>
            </div>
            <?php } ?>
            <div class="main" id="main">
                <?php if ($removed) { ?>
                <div class="block">
                    <h1 class="block-title center"><i class="fa fa-puzzle-piece"></i> Puzzle <?php echo $pID ?></h1>
                </div>
                <?php } ?>
                <div class="block">
                <?php if (!$removed) { ?>
                    <div id="chessground"></div>
                <?php } else {
                    $_SESSION['nextpuzzle'] = $pID + 1;
                    $form = '';
                    $button = '';
                    if (!$is_preview) {
                        $button = '<a class="flat-button continue-training transition" href="../next">Continue practicing</a>';
                        if (isAllowed('puzzle')) {
                            $form = '<form method="post" action="../remove"><input type="hidden" value="1" name="undo"><input type="hidden" value="'.$pID.'" name="puzzle"><button class="flat-button" type="submit"><i class="fa fa-undo"></i> Bring puzzle back</button>';
                        }
                    }
                    echo "<p class=\"nothing-to-see removed\"><i class=\"fa fa-ban\"></i> This puzzle was removed, or never existed.</p>$button$form"; 
                }
                ?>
                </div>
            </div>
            <?php if (!$removed) { ?>
            <div class="right-area">
                <div class="block feedback" id="res-container">
                    <div id="response" class="neutral loading"><div class="loader"></div></div>
                </div>
                <div class="block start-container hidden" id="next">
                    <a href="../next" class="flat-button green full transition"><span><i class="fa fa-check"></i> Next</span></a>
                </div>
                <div class="credits block hidden" id="credits"></div>
                <div class="block copyings hidden" id="copyings">
                    <?php if(isAllowed('puzzle') && !$is_preview) { ?>
                    <form action="/puzzles/remove" method="post">
                        <input type="hidden" value="<?php echo $pID ?>" name="puzzle">
                        <button class="flat-button" type="submit"><i class="fa fa-close"></i> Remove puzzle</button>
                    </form>
                    <?php } ?>
                    <h3>Copy URL <span id="copy-1" class="copy-badge"><i class="fa fa-check"></i> Copied</span></h3>
                    <input class="copy-on-click" id="puzzleURL" readonly />
                    <h3>Copy FEN <span id="copy-2" class="copy-badge"><i class="fa fa-check"></i> Copied</span></h3>
                    <input class="copy-on-click" value="<?php echo $fen ?>" readonly />
                </div>
            </div>
            <?php } ?>
        </div>
        <footer>
            <?php include_once "$url_extension../../include/footer.php"; ?>
        </footer>
        <?php if (!$removed) { ?>
        <script>
        const fen = '<?php echo $fen ?>',
              pID = '<?php echo $pID ?>',
              author = '<?php echo $author ?>',
              trophies = '<?php echo $trophies ?>';
        </script>
        <?php } ?>
        <script src="/js/global.js"></script>
        <?php if (!$removed) { ?>
        <script src="/js/chess.min.js"></script>
        <script src="/js/chess-functions.js"></script>
        <script src="/js/chessground.min.js"></script>
        <script src="/js/popup.js"></script>
        <script src="/js/puzzles.js"></script>
        <?php } ?>
    </body>
</html>
