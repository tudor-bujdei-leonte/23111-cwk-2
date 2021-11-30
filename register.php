<?php
$menu = array(
    'home' => array('text'=>'Home', 'url'=>'index.php'),
    'login' => array('text'=>'Login', 'url'=>'login.php'),
    'register' => array('text'=>'Register', 'url'=>'register.php'),
);

function generateMenu($items, $active) {
    $html = "<div class=\"topnav\">\n";
    foreach($items as $item) {
        if (strcmp($item['text'], $active) == 0) {
            $html .= "<a class=\"active\" href='{$item['url']}'>{$item['text']}</a>\n";
        }
        else {
            $html .= "<a href='{$item['url']}'>{$item['text']}</a>\n";
        }
    }
    $html .= "</div>\n";
    return $html;
}

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
                    $uid = trim($_POST["uid"]);
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Could not start checking. Try again later.";
        }
    }

    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } elseif (!preg_match('/^[a-zA-Z \-]+$/', trim($_POST["name"]))) {
        $name_err = "Name can only contain letters, spaces, or -";
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
                $Message = "Successfully created account!";
                header("location: login.php?Message=" . urlencode($Message));
            } else {
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Could not prepare statement. Try again later.";
        }

    } else {
        if (!empty($uid_err)) {
            echo '<script>alert("' . $uid_err . '");</script>';
        }
        if (!empty($name_err)) {
            echo '<script>alert("' . $name_err . '");</script>';
        }
        if (!empty($password_err)) {
            echo '<script>alert("' . $password_err . '");</script>';
        }
        if (!empty($confirm_password_err)) {
            echo '<script>alert("' . $confirm_password_err . '");</script>';
        }
        
        // echo $uid_err . '\n' . $name_err . '\n' . $password_err . '\n' . $confirm_password_err;
    }

    // echo $link->error . '\n' . $stmt->error;

    // mysqli_close($link);
}
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" href="styles.css">
        <title>Quizzy!</title>
    </head>
    <body>
        <?php 
        if (isset($_GET['Message'])) {
            echo '<script>alert("' . $_GET['Message'] . '");</script>';
        }

        echo GenerateMenu($menu, "Register");
        ?>

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
              
              <button type="submit">Register</button>
            </div>
          </form>
    </body>
</html>