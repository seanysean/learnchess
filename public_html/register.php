<?php 
session_start();
include "../include/functions.php";
if($l) {
    header('Location: home');
}
function verify($input,$min,$max,$type) {
    if (strtolower($input) === 'index' && $type === 'username') {
        return false;
    }
    if ((strlen($input) > $min) and (strlen($input) < $max)) {
        return true;
    } else {
        return false;
    }
}
if (isset($_POST['username']) and isset($_POST['password'])) {
    $username = preg_replace("/[^a-z1-9\-\_]/i", "",secure($_POST['username']));
    $password = preg_replace("/[^a-z1-9\-\_]/i", "",secure($_POST['password']));
    $includes_email = isset($_POST['email']) && strlen($_POST['email']) > 0;
    $email = '';
    if ($includes_email) {
        $email = secure($_POST['email']);
    }
    if(verify($username,3,18,'username') and verify($password,4,21,'password')) {
        $sqlAddition = ''; // So, if a user doesn't input a email, then no need to check if that unspecified email == '' in the db.
        if ($includes_email) {
            $sqlAddition = " OR email='$email'";
        }
        $sql = "SELECT username FROM `users` WHERE username='$username'$sqlAddition";
        $result = mysqli_query($connection,$sql);
        $duplicate = mysqli_num_rows($result);
        if ($duplicate === 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $permisssions = '{"edit_account":false,"puzzle_review":false}';
            $sql = "INSERT INTO `users` (username,password,permissions,email) VALUES ('$username','$hashed_password','$permisssions','$email')";
            $result = mysqli_query($connection,$sql);
            $getLastIdSQL = "SELECT id FROM `users` ORDER BY id DESC LIMIT 1";
            $getLastIdQ = mysqli_query($connection,$getLastIdSQL);
            $lastID = $getLastIdQ->fetch_assoc()['id'];
            $lowerUsername = strtolower($username);
            $memberProfile = fopen("member/$lowerUsername.php",'x');
            $pageCode = "<?php
\$account = Array('username'=>'$username','id'=>'$lastID');
require \"../../templates/profile.php\";";
            fwrite($memberProfile,$pageCode);
            fclose($memberProfile);
            if ($result) {
                $smsg = '<p>Account successfully created! Thank you for joining, you can now <a href="/login">Log in</a>.</p>';
                $login = true;
            }
        } else {
            $fmsg = '<p>The username or email already exists. Please try using a different username or email</p>';
        }
    } else {
        $fmsg = '<p>Sorry, your username or password was not valid. Perhaps your password or username was too long or too short. Did you use invalid characters?';
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Register • LearnChess</title>
        <?php include_once "../include/head.php" ?>
        <meta name="description" content="Join LearnChess, a free, no ads site made for helping you get better at chess.">
        <link href="css/material.css" type="text/css" rel="stylesheet">
    </head>
    <body<?php include_once "../include/attributes.php" ?>>
        <?php if (isset($login) and $login) {
            echo "<script src=\"js/register.js\"></script><script>login('$username','$password'); console.log('loaded 2');</script>";
        } ?>
        <div class="top">
            <?php include_once "../include/topbar.php" ?>
        </div>
        <div class="page center">
            <div class="main">
                <div class="block">
                    <h1 class="block-title">Register<span class="alternate">
                        <span class="alt-text">Already have a account?</span>
                            <a class="button nocolor" href="/login">
                                <span>
                                    <i class="fa fa-sign-in"></i>
                                    Login
                                </span>
                            </a>
                        </span>
                    </h1>
                    <?php if(isset($smsg)) { 
                        echo $smsg;
                    } else { 
                        if (isset($fmsg)) {
                            echo $fmsg;
                        }
                    ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                        <div class="input-container">
                            <input type="text" name="username" minlength="4" id="username" maxlength="17" pattern="[a-zA-Z1-9_-]{4,17}" title="4 to 17 Characters. Allowed characters: 1-9a-z_-" spellcheck="false" autocomplete="off"<?php if(isset($_GET['username'])) { echo " value=\"".$_GET['username']."\""; } else { echo " autofocus"; } ?> required>
                            <label for="username">Username</label>
                            <span class="line"></span>
                        </div>
                        <p id="usernameResponse" class="input-response"></p>
                        <div class="input-container">
                            <input type="password" name="password" minlength="5" id="password" maxlength="20" pattern="[a-zA-Z1-9_-]{5,20}" title="5 to 20 Characters. Allowed characters: 1-9a-z_-"<?php if(isset($_GET['username'])) { echo " autofocus"; } ?> required>
                            <label for="password">Password</label>
                            <span class="line"></span>
                        </div>
                        <p id="passwordResponse" class="input-response"></p>
                        <div class="input-container">
                            <input type="email" name="email" id="email">
                            <label for="email">Email (optional)</label>
                            <span class="line"></span>
                        </div>
                        <p>Allowed username and password characters are letters, numbers, dashes, and underscores. Anything else will be removed. We recommend you use a password you don't use anywhere else.</p>
                        <p>Example valid username: Agood_user-name1</p>
                        <button class="button blue" type="submit"><span><i class="fa fa-check"></i> Register</span></button>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
        <footer><?php include_once "../include/footer.php" ?></footer>
        <script src="js/global.js"></script>
        <script src="js/register.js"></script>
    </body>
</html>
