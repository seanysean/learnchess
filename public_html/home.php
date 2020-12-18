<?php session_start();
include "../include/functions.php";
if ($l) {
    $myid = $_SESSION['userid'];
    $puzzle_count = mysqli_num_rows(mysqli_query($connection,"SELECT id FROM `puzzles_approved` WHERE removed='0' AND author_id='$myid'"));
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Home • LearnChess</title>
        <?php include_once "../include/head.php" ?>
        <link href="css/chessground.css" type="text/css" rel="stylesheet">
        <link href="css/home.css" type="text/css" rel="stylesheet">
        <style>
        #puzzleOfTheDay {
            width: 200px;
            height: 200px;
            transition: 0.25s opacity ease;
        }
        #puzzleOfTheDay:hover {
            opacity: 0.5;
        }
        </style>
    </head>
    <body<?php include_once "../include/attributes.php" ?>>
        <div class="top">
            <?php include_once "../include/topbar.php" ?>
        </div>
        <div class="page">
            <div class="main">
                <div class="block">
                <?php if ($puzzle_count > 0) { ?>
                    <h1 class="block-title">Your most popular puzzles</h1>
                    <div>
                    <?php $sql = "SELECT id,fen,trophies FROM `puzzles_approved` WHERE author_id='$myid' AND removed='0' ORDER BY trophies DESC LIMIT 3";
                        $result = mysqli_query($connection,$sql);
                        while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
                            $id = $row['id'];
                            $fen = $row['fen'];
                            $trophies = $row['trophies']; ?>
                        <div class="board-container">
                            <a href="<?php echo "puzzles/view/$id" ?>" class="board" data-fen="<?php echo $fen ?>"></a>
                            <div class="credits"><?php echo $trophies ?> <i class="fa fa-trophy"></i></div>
                        </div>
                    <?php } ?>
                    </div>
                    <a href="puzzles/new" class="button blue more"><span><i class="fa fa-puzzle-piece"></i> Create new puzzle</span></a>
                <?php } else { // If they haven't contributed, assume they are new ?>
                    <h1 class="block-title">Welcome to LearnChess!</h1>
                    <p>Here are some features you might enjoy:</p>
                    <ul>
                        <li><a href="/computer">Play with computer</a></li>
                        <li><a href="/puzzles">Tactics training</a></li>
                        <li><a href="/coordinates">Coordinates trainer</a></p></li>
                    </ul>
                    <p>Have fun! If you have questions, <a href="/contact">contact me</a>.</p>
                <?php } ?>
                </div>
            </div>
            <div class="right-area">
                <!--<div class="block">
                    <h1 class="block-title center">Puzzle of the day</h1>
                    <a href="puzzles/view/3"><div id="puzzleOfTheDay"></div></a>
                </div>-->
                <div class="block no-padding do-stuff">
                    <h1 class="block-title center">Links</h1>
                    <a href="/puzzles/new"><i class="fa fa-puzzle-piece"></i> Submit a puzzle</a>
                    <a href="/settings/profile"><i class="fa fa-edit"></i> Edit profile</a>
                    <a href="https://github.com/seanysean/learnchess/issues/new" target="_blank">
                        <i class="fa fa-bug"></i> Report a bug
                    </a>
                </div>
            </div>
        </div>
        <footer>
            <?php include_once "../include/footer.php" ?>
        </footer>
        <script src="js/global.js"></script>
        <script src="js/home.js"></script>
        <script src="js/chessground.min.js"></script>
        <script src="js/loadposition.js"></script>
        <script>
        //loadPosition(document.getElementById('puzzleOfTheDay'),'rnbqkbnr/pppp2pp/5p2/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R');
        </script>
    </body>
</html>
<?php } else {
    header('Location: login');
} ?>
