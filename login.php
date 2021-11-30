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

$uid = $password = "";
$uid_err = $password_err = "";

# TODO check session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["uid"]))) {
        $uid_err = "Please enter UID.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', trim($_POST["uid"]))) {
        $uid_err = "UID can only contain alphanumeric characters.";
    } else {
        $uid = trim($_POST["uid"]);
    }

    if (empty($_POST["psw"])) {
        $password_err = "Please enter a password.";
    } else {
        $password = $_POST["psw"];
    }

    if (empty($uid_err) && empty($password_err)) {
        $sql = "SELECT uid FROM users WHERE uid == ? AND password == ?";
        
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_uid, $param_psw);

            $param_uid = $uid;
            $param_psw = password_hash($password, PASSWORD_DEFAULT);

            if (mysqli_stmt_execute($stmt)) {
                echo '<script>alert("Successfully logged in!");</script>';
                header("location: index.php");
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
        if (!empty($password_err)) {
            echo '<script>alert("' . $password_err . '");</script>';
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
        <?php echo GenerateMenu($menu, "Register"); ?>

        <form action="login.php" method="post">
          
            <div class="container">
              <label for="uid"><b>University ID</b></label>
              <input type="text" placeholder="Enter username" name="uid" required>
              
              <label for="psw"><b>Password</b></label>
              <input type="password" placeholder="Enter password" name="psw" required>
          
              <button type="submit">Login</button>
            </div>
          </form>
    </body>
</html>