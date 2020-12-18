<?php
session_start();
include "../../include/functions.php";
include "../../include/config.php";
$sql = "SELECT id FROM `puzzles_to_review`";
$result = mysqli_query($connection,$sql);
if ($result) {
    $unreviewedCount = mysqli_num_rows($result);
}
function generatePuzzlePositions($sql) { // Why am I using camel case... too much JS I guess.
    global $connection;
    $result = mysqli_query($connection,$sql);
    $boards = '';
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            $fen = $row['fen'];
            $pID = $row['id'];
            $trophies = $row['trophies'];
            $authorID = $row['author_id'];
            $authorLink = createUserLink($authorID);
            $boards .= "<div class=\"board-container\">
                <a href=\"view/$pID\" class=\"board\" data-fen=\"$fen\"></a>
                <div class=\"credits\">$trophies <i class=\"fa fa-trophy\"></i><br />Created by<br />$authorLink</div>
            </div>";
        }
    } else {
        return false;
    }
    return $boards;
}
if ($l) {
    $myID = $_SESSION['userid'];
    $sql2 = "SELECT id,fen FROM `puzzles_approved` WHERE author_id='$myID' AND removed='0' ORDER BY id DESC";
    $result2 = mysqli_query($connection,$sql2);
    $contributed_count = mysqli_num_rows($result2);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Approved puzzles • LearnChess</title>
        <?php include_once "../../include/head.php" ?>
        <meta name="description" content="Improve your chess skills by solving chess tactics created by the community. It's 100% free, no ads, and open source. No registration required.">
        <link href="../css/puzzles.css" rel="stylesheet" type="text/css">
        <link href="../css/chessground.css" rel="stylesheet" type="text/css">
    </head>
    <body<?php include_once "../../include/attributes.php" ?>>
        <div class="top">
            <?php include_once "../../include/topbar.php" ?>
        </div>
        <div class="page">
            <div class="main">
                <div class="block">
                    <h1 class="block-title center">Recent puzzles</h1>
                    <?php
                    $sql = "SELECT * FROM `puzzles_approved` WHERE removed='0' ORDER BY id DESC LIMIT 6";
                    echo "<div class=\"boards\">".generatePuzzlePositions($sql)."</div>";
                    ?>
                </div>
                <div class="block">
                    <h1 class="block-title center">Top puzzles</h1>
                    <?php
                    $sql = "SELECT * FROM `puzzles_approved` WHERE removed='0' ORDER BY trophies DESC LIMIT 6";
                    $boards = generatePuzzlePositions($sql);
                    $result = mysqli_query($connection,$sql);
                    if ($boards) {
                        echo "<div class=\"boards\">$boards</div>";
                    } else {
                        echo "<p class=\"nothing-to-see lower center\">No approved puzzles. <a href=\"new\">Why not make one?</a></p>";
                    } ?>
                </div>
            </div>
            <div class="right-area">
                <div class="block start-container start">
                    <a href="next" class="flat-button blue full transition"><span><i class="fa fa-check"></i> Start</span></a>
                </div>
                <div class="block">
                <?php if($l && $contributed_count) { ?>
                    <h1 class="block-title">Submitted puzzles</h1>
                    <div class="your-puzzles">
                        <?php
                        $sql = "SELECT id FROM `puzzles_to_review` WHERE author_id='$myID'";
                        $result = mysqli_query($connection,$sql);
                        if (mysqli_num_rows($result) > 0) {
                            $rows = mysqli_num_rows($result);
                            $s = 's';
                            if ($rows === 1) {
                                $s = '';
                            }
                            echo "<h3>$rows puzzle$s in review</h3>";
                        }
                        $s = 's';
                        if ($contributed_count === 1) {
                            $s = '';
                        }
                        echo "<h3>$contributed_count approved puzzle$s</h3>";
                        $rowCount = 0;
                        while($row = mysqli_fetch_array($result2,MYSQLI_ASSOC)) {
                            $puzzleID = $row['id'];
                            $fen = $row['fen'];
                            if ($rowCount === 6) {
                                break;
                            }
                            $rowCount++;
                            ?>
                            <a href="view/<?php echo $puzzleID ?>" data-fen="<?php echo $fen ?>" class="puzzle">Puzzle <?php echo $puzzleID ?></a>
                        <?php } ?>
                    </div>
                    <a href="new" class="button green"><span>Create new puzzle <i class="fa fa-plus"></i></span></a>
                <?php } else if (!$l) { ?>
                    <h1 class="block-title">Contribute</h1>
                    <p><a href="/register">Register</a> to start creating puzzles!</p>
                <?php } else { ?>
                    <a href="new" class="full button blue"><span><i class="fa fa-plus"></i> New puzzle</span></a>
                <?php } ?>
                </div>
                <?php if (false && $devMode) { // Waiting for more users before bringing out the Leaderboard. Or perhaps it will never come ?><div class="block">
                    <h1 class="block-title">Leaderboard</h1>
                    <ol class="leaderboard">
                    <?php $sql = "SELECT rating,id FROM `users` WHERE NOT active='0' ORDER BY rating DESC LIMIT 5";
                        $result = mysqli_query($connection,$sql);
                        $n = 1;
                        while ($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) { 
                            $lId = $row['id'];
                            $lRating = $row['rating'];
                            $uiLink = createUserLink($lId,true); ?>
                        <li><?php echo '<span><span class="rank">' . $n++ . '</span>' . $uiLink . '</span><span class="rating">' . round($lRating) . '</span>'; ?></li>
                        <?php } ?>
                    </ol>
                </div>
                <?php } ?>
            </div>
        </div>
        <footer>
            <?php include_once "../../include/footer.php" ?>
        </footer>
        <script src="../js/global.js"></script>
        <script src="../js/chessground.min.js"></script>
        <script src="../js/loadposition.js"></script>
    </body>
</html>
