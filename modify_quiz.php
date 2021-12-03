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

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false || !isset($_SESSION["is_staff"]) || $_SESSION["is_staff"] === false){
    header("location: index.php?Message=" . urlencode("Only staff members can modify a quiz."));
    exit;
}

function getModifiableQuizzes($uid) {
    require_once "config.php";

    $titles = [];

    $sql = "SELECT id, title FROM quizzes WHERE author_uid = ? OR modifiable = 1"; // duplicate names are allowed but impossible to recover

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $uid);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $qid, $title);
            while (mysqli_stmt_fetch($stmt)) {
                array_push($titles, [
                    "title" => $title,
                    "id" => $qid
                ]);
            }
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    return $titles;
}

function getSelectTag($lines, $name, $title, $post) {
    $s = '';
    $s .= '<form action="' . $post . '" method="post">';
    $s .= '<div class="container">';
    $s .= '<label for="' . $name . '"><b>' . $title . '</b></label>&nbsp&nbsp&nbsp';
    $s .= '<select name="' . $name . '">';
    foreach ($lines as $line) {
        $s .= '<option value="' . $line["id"] . '">' . $line["title"] . '</option>';
    }
    $s .= '</select>';
    $s .= '<button type="submit">Next</button>';
    $s .= '</form>';
    return $s;
}

function getQuizDetailsForm($qid) {
    $details = getQuizDetailsFormFill($qid);

    $s = '<form action="modify_quiz.php" method="post">
            <div class="container">

                <label for="quiz_title"><b>Quiz title</b></label>
                <input type="text" pattern=".*\S+.*" placeholder="Enter quiz title" name="quiz_title" value="' . $details["title"] . '" required>

                <label for="quiz_time"><b>Estimated time to complete (minutes)</b></label>
                <input type="number" min="0" step="1" name="quiz_time" value="' . $_SESSION["m-quiz"]["old"]["duration"] . '" required>

                <label>
                    <input type="checkbox" name="is_visible" ' . ($_SESSION["m-quiz"]["old"]["visible"] == 1? 'checked' : '') . '>
                    <b>Visible to students?</b>
                </label>

                <label>
                    <input type="checkbox" name="is_modifiable" ' . ($_SESSION["m-quiz"]["old"]["modifiable"] == 1? 'checked' : '') . '>
                    <b>Allow other staff members to modify?</b>
                </label>

                <button type="submit" name="submitted" value="next">Edit questions</button>
                <button type="submit" name="submitted" value="delete">Delete quiz</button>
                <button type="submit" name="submitted" value="cancel">Cancel</button>

            </div>
        </form>';

    return $s;
}

// function get

// fsm to figure out which part to execute
// -1 - start form
// 0 - change basic details of quiz
// 1 <= i <= count of questions - change each question
function modify_quiz_main() {
    if (!isset($_SESSION["m-quiz-state"])) {
        $_SESSION["m-quiz-state"] = -1;
        $_SESSION["m-quiz"] = [
            "id" => -1,
            "old" => [],
            "new" => []
        ];
    }

    if ($_SESSION["m-quiz-state"] == -1) {
        $titles = getModifiableQuizzes($_SESSION["uid"]);
        // foreach($titles as $title) {
        //     echo $title . "\n";
        // }
        echo getSelectTag($titles, "quiz-option", "Select a quiz to modify:", "modify_quiz.php");
    } elseif ($_SESSION["m-quiz-state"] == 0) {
        echo getQuizDetailsForm($_SESSION["m-quiz"]["id"]); // next or delete
        foreach ($_SESSION["m-quiz"]["old"]["questions"] as $question) {
            echo $question["title"] . "<br>";
        }
    } else {
        // next or delete question
        // submit or new question
    }
}





function setOldQuizDetails($qid) {
    require_once "config.php";

    $sql = "SELECT title, duration, available, modifiable FROM quizzes WHERE id = " . strval($qid);
    $_SESSION["m-quiz"]["old"] = [
        "title" => "",
        "duration" => 0,
        "visible" => 0,
        "modifiable" => 0
    ];

    if ($stmt = mysqli_prepare($link, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result(
                $stmt, 
                $_SESSION["m-quiz"]["old"]["title"], 
                $_SESSION["m-quiz"]["old"]["duration"], 
                $_SESSION["m-quiz"]["old"]["visible"], 
                $_SESSION["m-quiz"]["old"]["modifiable"]
            );
            mysqli_stmt_fetch($stmt);
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    $sql = "SELECT id, text, a, b, c, d, answer FROM quiz_questions WHERE quiz_id = " . $_SESSION["m-quiz"]["id"];
    $_SESSION["m-quiz"]["old"]["questions"] = [];

    if ($stmt = mysqli_prepare($link, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_bind_result($stmt, $id, $text, $a, $b, $c, $d, $answer)) {
                while (mysqsli_stmt_fetch($stmt)) {
                    array_push($_SESSION["m-quiz"]["old"]["questions"], [
                        "id" => $id,
                        "text" => $text,
                        "a" => $a,
                        "b" => $b,
                        "c" => $c,
                        "d" => $d,
                        "answer" => $answer,
                        "deleted" => false
                    ]);
                }
            }
            mysqli_stmt_fetch($stmt);
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    return $details;
}

// submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submitted"]) && $_POST["submitted"] == "cancel") {
        $_SESSION["m-quiz-state"] = -1;
        header("location: index.php?Message=" . urlencode("Canceled operation."));
        exit;
    }

    if ($_SESSION["m-quiz-state"] == -1) {
        // get old details of quiz
        $_SESSION["m-quiz"]["id"] = $_POST["quiz-option"];
        setOldQuizDetails($_SESSION["m-quiz"]["id"]);

        $_SESSION["m-quiz-state"]++;
    } elseif ($_SESSION["m-quiz-state"] == 0) {
        // get new details of quiz
        $_SESSION["m-quiz"]["new"] = [
            "title" => $_POST["quiz_title"],
            "duration" => $_POST["quiz_time"],
            "available" => isset($_POST["is_visible"]) ? 1 : 0,
            "modifiable" => isset($_POST["is_modifiable"]) ? 1 : 0,
            "current question" => 1
        ];

        $_SESSION["m-quiz-state"]++;
    } else {
        // get new details of question

        // if at the last question, submit form

        // then reset/increment m-quiz-state
        $_SESSION["m-quiz-state"] = 0;
    }
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

        echo generateMenu($menu);

        modify_quiz_main();
        ?>

    </body>
</html>