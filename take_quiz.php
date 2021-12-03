<?php
require_once "config.php";
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

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: index.php?Message=" . urlencode("You must be logged in to take a quiz."));
    exit;
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

function getAccessibleQuizzes() {
    if ($_SESSION["is_staff"])
        $sql = "SELECT id, title, duration FROM quizzes WHERE available = 1 OR modifiable = 1 OR author_uid = '" . $_SESSION["uid"] . "'";
    else $sql = "SELECT id, title, duration FROM quizzes WHERE available = 1";

    $quizzes = [];

    if ($stmt = mysqli_prepare($link, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $qid, $title, $duration);
            while (mysqli_stmt_fetch($stmt)) {
                array_push($titles, [
                    "title" => $title,
                    "id" => $qid,
                    "duration" => $duration
                ]);
            }
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    return $quizzes;
}

function chooseQuizMenu() {
    $lines = getAccessibleQuizzes();
    $s = '';
    $s .= '<form action="take_quiz.php" method="post">';
    $s .= '<div class="container">';
    $s .= '<label for="quiz-option"><b>Select quiz to take:</b></label>&nbsp&nbsp&nbsp';
    $s .= '<select name="quiz-option">';
    foreach ($lines as $line) {
        $s .= '<option value="' . $line["id"] . '">' . $line["title"] . "(" . strval($line["duration"]) . ' min)</option>';
    }
    $s .= '</select>';
    $s .= '<button type="submit">Take quiz</button>';
    $s .= '</form>';
    return $s;
}

function answerQuestionMenu() {
    // pop question
    $question = array_pop($_SESSION["t-quiz"]["questions"]);
    $_SESSION["t-quiz"]["answer to current question"] = $question["answer"];
    $submit_button = count($_SESSION["t-quiz"]["questions"]) == 0 ?
        '<button type="submit" name="submitted" value="save">Submit attempt</button>' :
        '<button type="submit" name="submitted" value="next">Next question</button>';
    return '
        <form action="modify_quiz.php" method="post">
            <div class="container">
                <label><h2>' . $question["text"] . '</h2></label>
        
                <label><h4>a. ' . $question["a"] . '</h4></label>
                <label><h4>b. ' . $question["b"] . '</h4></label>
                <label><h4>c. ' . $question["c"] . '</h4></label>
                <label><h4>d. ' . $question["d"] . '</h4></label>
        
                <label for="uid"><b>Your answer:</b></label>
                <input type="text" pattern="^[a-d]$" placeholder="Enter letter corresponding to answer" name="answer" required>
        
                ' . $submit_button . '
        
            </div>
        </form>
            ';
}

function printResultsMenu() {
    return '
    <form>
    <div class="container">
    <label><h1>' . $_SESSION["t-quiz"]["title"] . '</h1></label>

    <label><h3>You answered correctly a total of ' . $_SESSION["t-quiz"]["correct answers"] . '/' . $_SESSION["t-quiz"]["answers"] . ' questions.</h3></label>
    <label><h3>Final grade: ' . strval(100.0 * $_SESSION["t-quiz"]["correct answers"] / $_SESSION["t-quiz"]["answers"]) . '%</h3></label>

    <button type="submit" name="submitted" value="return">Return</button>
    </div>
    </form>
    ';
}

function mainTakeQuiz() {
    if (!isset($_SESSION["t-quiz-state"])) {
        $_SESSION["t-quiz-state"] = -1;
        $_SESSION["t-quiz"] = [
            "id" => -1,
            "questions" => [],
            "correct answers" => 0,
            "answers" => 0
        ]
    }

    if ($_SESSION["t-quiz-state"] == -1) {
        // choose quiz
        echo chooseQuizMenu();
    } elseif ($_SESSION["t-quiz-states"] == 0) {
        // answer question
        echo answerQuestionMenu();
    } else {
        // print results
        echo printResultsMenu();
    }
}






function setQuizDetails($qid) {
    $sql = "SELECT title, duration, available, modifiable FROM quizzes WHERE id = " . strval($qid);
    $_SESSION["t-quiz"] = [
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
                $_SESSION["t-quiz"]["title"], 
                $_SESSION["t-quiz"]["duration"], 
                $_SESSION["t-quiz"]["visible"], 
                $_SESSION["t-quiz"]["modifiable"]
            );
            mysqli_stmt_fetch($stmt);
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);

    $sql = "SELECT id, text, a, b, c, d, answer FROM quiz_questions WHERE quiz_id = " . $_SESSION["m-quiz"]["id"];
    $_SESSION["t-quiz"]["questions"] = [];

    if ($stmt = mysqli_prepare($link, $sql)) {
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_bind_result($stmt, $id, $text, $a, $b, $c, $d, $answer)) {
                while (mysqli_stmt_fetch($stmt)) {
                    array_push($_SESSION["t-quiz"]["questions"], [
                        "id" => $id,
                        "text" => $text,
                        "a" => $a,
                        "b" => $b,
                        "c" => $c,
                        "d" => $d,
                        "answer" => $answer
                    ]);
                }
            }
            mysqli_stmt_fetch($stmt);
        } else echo "An error occurred. Please try again later.";
    } else echo "An error occurred. Please try again later.";
    mysqli_stmt_close($stmt);
}

function getAnswerQuiz() {
    if ($_POST["answer"] == $_SESSION["t-quiz"]["answer to current question"]) {
        $_SESSION["t-quiz"]["correct answers"]++; 
    }
    $_SESSION["t-quiz"]["answers"]++;

    if ($_POST["submitted"] == "next") {
        header("location: take_quiz.php");
        exit;
    } else {
        // insert results into db
        $uid = $_SESSION["uid"];
        $qid = $_SESSION["t-quiz"]["id"];
        $score = 1.0 * $_SESSION["t-quiz"]["correct answers"] / $_SESSION["t-quiz"]["answers"];
        $sql = "INSERT INTO quiz_attempts SET uid = '$uid', quiz_id = $qid, score = $score, date = NOW";

        if ($stmt = mysqli_prepare($link, $sql)) {
            if (mysqli_stmt_execute($stmt)) {
                // results page
                $_SESSION["t-quiz-state"]++;
                header("location: take_quiz.php");
                exit;
            } else echo "An error occurred. Please try again later." . mysqli_error($link);
        } else echo "An error occurred. Please try again later." . mysqli_error($link);
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submitted"]) && $_POST["submitted"] == "cancel") {
        $_SESSION["t-quiz-state"] = -1;
        header("location: index.php?Message=" . urlencode("Canceled operation."));
        exit;
    } elseif (isset($_POST["submitted"]) && $_POST["submitted"] == "return") {
        $_SESSION["t-quiz-state"] = -1;
        header("location: index.php"));
        exit;
    }

    if ($_SESSION["t-quiz-state"] == -1) {
        // choose quiz
        $_SESSION["t-quiz-state"]++;
        setQuizDetails($_POST["quiz-option"]);

    } elseif ($_SESSION["t-quiz-state"] == 0) {
        // answer question
        getAnswerQuiz();
    } else echo "Oopsie! This should have been unreachable.";
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
        ?>

        mainTakeQuiz();
    </body>
</html>