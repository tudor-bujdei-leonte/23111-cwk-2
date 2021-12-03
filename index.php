<?php
session_start();

$menu = array(
    'home' => array('text'=>'Home', 'url'=>'index.php'),
    'login' => array('text'=>'Login', 'url'=>'login.php'),
    'register' => array('text'=>'Register', 'url'=>'register.php'),
    'take_quiz' => array('text'=>'Take quiz', 'url'=>'take_quiz.php'),
    'create_quiz' => array('text'=>'Create quiz', 'url'=>'create_quiz.php'),
    'modify_quiz' => array('text'=>'Modify quiz', 'url'=>'modify_quiz.php'),
    'logout' => array('text'=>'Log out', 'url'=>'logout.php'),
);

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["is_staff"] === true) {
        $menu = array(
            'home' => array('text'=>'Home', 'url'=>'index.php'),
            'take_quiz' => array('text'=>'Take quiz', 'url'=>'take_quiz.php'),
            'create_quiz' => array('text'=>'Create quiz', 'url'=>'create_quiz.php'),
            'modify_quiz' => array('text'=>'Modify quiz', 'url'=>'modify_quiz.php'),
            'logout' => array('text'=>'Log out', 'url'=>'logout.php'),
        );
    } else {
        $menu = array(
            'home' => array('text'=>'Home', 'url'=>'index.php'),
            'take_quiz' => array('text'=>'Take quiz', 'url'=>'take_quiz.php'),
            'logout' => array('text'=>'Log out', 'url'=>'logout.php'),
        );
    }
} else {
    $menu = array(
        'home' => array('text'=>'Home', 'url'=>'index.php'),
        'login' => array('text'=>'Login', 'url'=>'login.php'),
        'register' => array('text'=>'Register', 'url'=>'register.php'),
    );
}

function isSuffix($s1, $s2)
{
    $n1 = ($s1);
    $n2 = strlen($s2);
    if ($n1 > $n2)
    return false;
    for ($i = 0; $i < $n1; $i++)
    if ($s1[$n1 - $i - 1] != $s2[$n2 - $i - 1])
        return false;
    return true;
}

function generateMenu($items) {
    $html = "<div class=\"topnav\">\n";
    foreach($items as $item) {
        if (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) {
            $html .= "<a class=active href='{$item['url']}'>{$item['text']}</a>\n";
        } else {
            $html .= "<a href='{$item['url']}'>{$item['text']}</a>\n";
        }
        
    }
    $html .= "</div>\n";
    return $html;
}

function displayTakenQuizzes() {
    require_once "config.php";

    if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true))
        return "<p>Log in to see your attempt history.</p>";

    $sql = 'SELECT quizzes.title, quiz_attempts.score
            FROM quiz_attempts
            INNER JOIN quizzes
            ON quiz_attempts.quiz_id = quizzes.id
            WHERE quiz_attempts.uid = \'' . $_SESSION["uid"] . "'";
    
    $result = "<p>Your quiz attempts:</p><br>";

    if ($stmt = mysqli_prepare($link, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_bind_result($stmt, $title, $score)) {
                while (mysqli_stmt_fetch($stmt)) {
                    $result .= "<p>Quiz \"" . $title . "\" | Score " . strval($score * 100) . "%</p><br>";
                }
            }
            mysqli_stmt_fetch($stmt);
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    if ($result == "<p>Your quiz attempts:</p><br>") return "<p>You have not attempted any quiz.</p>";
    return $result;
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
            echo '<p>' . $_GET['Message'] . '</p>';
        }

        echo GenerateMenu($menu);

        if (isset($_SESSION["name"]) && isset($_SESSION["loggedin"])) {
            echo "<p>Welcome back, <b>{$_SESSION['name']}</b>!</p>";
        } else {
            echo "<p>You are not logged in.</p>";
        }

        // if (isset($_SESSION["quiz"])) {
        //     echo strval(count($_SESSION["quiz"]["questions"])) . "/" . strval($_SESSION["quiz"]["num questions"]) . " questions:<br>";
        //     foreach ($_SESSION["quiz"]["questions"] as $question) {
        //         echo "Question text: " . $question["text"] . "<br>";
        //         echo "Answer a: " . $question["a"] . "<br>";
        //         echo "Answer b: " . $question["b"] . "<br>";
        //         echo "Answer c: " . $question["c"] . "<br>";
        //         echo "Answer d: " . $question["d"] . "<br>";
        //         echo "Correct answer: " . $question["answer"] . "<br>";
        //     }
        // }

        echo displayTakenQuizzes();
        ?>

        <!-- <p>This is an example paragraph. Anything in the <strong>body</strong> tag will appear on the page, just like this <strong>p</strong> tag and its contents.</p> -->
    </body>
</html>