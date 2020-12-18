<?php
session_start();
require "../../include/functions.php";
if ($l) {
    $userid = $_SESSION['userid'];
}

if(isset($_GET['move']) && isset($_GET['movenum']) && isset($_GET['puzzle'])) {
    $puzzle_preview = isset($_GET['preview']) && $_GET['preview'] === 'true';
    $move = secure($_GET['move']);
    $movenum = secure($_GET['movenum']);
    $puzzle = secure($_GET['puzzle']) + 0;
    $table = $puzzle_preview ? 'puzzles_to_review' : 'puzzles_approved';
    $sql = "SELECT fen,pgn FROM `$table` WHERE id='$puzzle'";
    $result = mysqli_query($connection,$sql);
    $res = $result->fetch_assoc();
    $pgn = preg_split('/[1-9][.][.][.] |[1-9][.]|\s/',$res['pgn'],null,PREG_SPLIT_NO_EMPTY);
    $fen = explode(' ',$res['fen']);
    $color = $fen[1];
    $correct = $pgn[($movenum * 2) - 2];
    $return = Array();
    $realNext = false;
    if (isset($_SESSION['nextpuzzle']) && $_SESSION['nextpuzzle'] === $puzzle) {
        $realNext = true;
    }
    if ($correct === $move) {
        $return['correct'] = true;
        if (sizeof($pgn) > $movenum * 2) {
            $return['next'] = $pgn[($movenum * 2) - 1];
        } else {
            $return = puzzle_finished(true,$return,$puzzle,$realNext);
        }
    } else {
        $return = puzzle_finished(false,$return,$puzzle,$realNext,$correct);
    }
    echo json_encode($return);
}

function puzzle_finished($success,$res_array,$puzzle_id,$rated,$correct_move='') {
    global $connection,$l,$puzzle_preview,$userid;
    $res_array['ended'] = true;
    if (!$success) {
        $res_array['correct'] = false;
        $res_array['right_move'] = $correct_move;
    }
    $res = mysqli_query($connection,"SELECT rating,explanation FROM `puzzles_approved` WHERE id='$puzzle_id'")->fetch_assoc();
    $pRating = $res['rating'];
    $explain = $res['explanation'];
    if ($explain) {
        $res_array['explanation'] = $explain;
    } else {
        $res_array['explanation'] = false;
    }
    if ($l) {
        $res_array['ratings'] = Array('user' => $_SESSION['rating'],'puzzle'=>$pRating);
        if ($rated && !$puzzle_preview) {
            $ratings = calcRatings($_SESSION['rating'],$pRating,$success);
            $res_array['ratings'] = $ratings;
            $rating_diff = $ratings['user'] - $_SESSION['rating'];
            $res_array['rating_diff'] = $rating_diff;
            $_SESSION['rating'] = $ratings['user'];
            $_SESSION['nextpuzzle'] = 0; // Means that no puzzles should be rated until user presses the next button.
            $ur = $ratings['user'];
            $pr = $ratings['puzzle'];
            $query = "UPDATE `users` SET rating='$ur',updated_history='1',nextpuzzle='0' WHERE id='$userid';";
            $query .= "UPDATE `puzzles_approved` SET rating='$pr' WHERE id='$puzzle_id';";
            $query .= "INSERT INTO `puzzles_history` (`user`,`puzzle`,`rating`,`rating_diff`,`date`) VALUES ('$userid','$puzzle_id','$ur','$rating_diff',CURRENT_TIMESTAMP)";
            mysqli_multi_query($connection,$query);
        }
    } else {
        $res_array['ratings'] = Array('puzzle'=>$pRating);
        $_SESSION['completed'] = $puzzle_id;
    }
    return $res_array;
}

function calcRatings($u,$p,$w) {
    $ratings = Array();
    if ($u > $p) {
        $diff = $u - $p;
    } else {
        $diff = $p - $u;
    }
	if ($diff > 350) {
		$diff = 350;	
	}	
	$ratings['diff'] = $diff;
    if ($w) {
		if ($u > $p) {
        	$ratings['user'] = round($u - 0.04 * $diff + 16,2);
        	$ratings['puzzle'] = round($p + 0.04 * $diff - 16,2);
		} else {
			$ratings['user'] = round($u + 0.04 * $diff + 16,2);
        	$ratings['puzzle'] = round($p - 0.04 * $diff - 16,2);
		}
    } else {
		if ($u > $p) {
        	$ratings['user'] = round($u - 0.04 * $diff - 16,2);
        	$ratings['puzzle'] = round($p + 0.04 * $diff + 16,2);
		} else {
			$ratings['user'] = round($u + 0.04 * $diff - 16,2);
        	$ratings['puzzle'] = round($p - 0.04 * $diff + 16,2);
		}
    }
    return $ratings;
}
