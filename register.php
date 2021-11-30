<?php
require_once "config.php";

$uid = $name = $password = "";
$uid_err = $name_err = $password_err = $confirm_password_err = "";
$is_staff = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["uid"]))) {
        $uid_err = "Please enter UID.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', trim($_POST["uid"]))) {
        $uid_err = "UID can only contain alphanumeric characters.";
    } else {
        $sql = "SELECT uid FROM users WHERE uid = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_uid);
            $param_uid = trim($_POST["uid"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $uid_err = "UID already has an associated account.";
                } else {
                    $uid = trim($_POST["username"]);
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
        $stmt->close;
    }

    if (empty(trim($_POST["name"]))) {
        $uid_err = "Please enter your name.";
    } elseif (!preg_match('/^[a-zA-Z \-]+$/', trim($_POST["name"]))) {
        $name_err = "Name can only contain letters, spaces, or \"-\".";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty($_POST["psw"])) {
        $password_err = "Please enter a password.";
    } else {
        $password = $_POST["psw"];
    }

    if (strcmp($_POST["psw_conf"], $password)) {
        $confirm_password_err = "The passwords must match.";
    }

    if (isset($_POST["is_staff"])) { // ?
        $is_staff = 1;
    }

    if (empty($uid_err) && empty($name_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (uid, name, password, is_staff) VALUES (?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $param_uid, $param_name, $param_psw, $param_is_staff);

            $param_uid = $uid;
            $param_name = $name;
            $param_psw = password_hash($password, PASSWORD_DEFAULT);
            $param_is_staff = $is_staff;

            if (mysqli_stmt_execute($stmt)) {
                header("location: login.php");
                echo "Successfully created account.";
            } else {
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Could not prepare statement. Try again later.";
        }

    } else {
        echo $uid_err . '\n' . $name_err . '\n' . $password_err . '\n' . $confirm_password_err;
    }

    mysqli_close($link);
}
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" href="styles.css">
        <title>Quizzy!</title>
    </head>
    <body>
        <div class="topnav">
            <a href="index.html">Home</a>
            <a href="login.html">Log in</a>
            <a class="active" href="register.html">Register</a>
        </div>

        <form action="register.php" method="post">
          
            <div class="container">
              <label for="uid"><b>University ID</b></label>
              <input type="text" placeholder="Enter username" name="uid" required>
              
              <label for="name"><b>Name</b></label>
              <input type="text" placeholder="Enter full name" name="name" required>
          
              <label for="psw"><b>Password</b></label>
              <input type="password" placeholder="Enter password" name="psw" required>
          
              <label for="psw_conf"><b>Confirm password</b></label>
              <input type="password" placeholder="Confirm password" name="psw_conf" required>

              <label>
                  <input type="checkbox" name="is_staff">
                  <b>Are you a staff member?</b>
              </label>
              
              <button type="submit">Login</button>
            </div>
          
            <div class="container" style="background-color:#f1f1f1">
              <button type="button" class="cancelbtn">Cancel</button>
              <span class="psw"><a href="recover_password.html">Forgot password?</a></span>
            </div>
          </form>
    </body>
</html>