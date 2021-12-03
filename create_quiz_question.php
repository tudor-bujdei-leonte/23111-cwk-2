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
    header("location: index.php?Message=" . urlencode("Only staff members can create a quiz."));
    exit;
}
if(!isset($_SESSION["quiz"]) || $_SESSION["quiz"]["num questions"] === 0){
    header("location: index.php?Message=" . urlencode("No quiz questions to complete."));
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    # preamble for question
    # field checking is already performed in the HTML
    # except for correct answer
    $ans = $_POST["anscorrect"];
    if (empty($_POST["ans" . $ans])) {
        header("location: create_quiz_question.php?Message=" . urlencode('The correct answer must be one of the possible answers.'));
        exit;
    } else {
        // echo "<script>alert(\"" . strval(count($_SESSION["quiz"]["questions"])) . "\");</script>";

        # insert question into list
        array_push($_SESSION["quiz"]["questions"], [
            "text" => $_POST["qtext"],
            "a" => empty($_POST["ansa"]) ? NULL : $_POST["ansa"],
            "b" => empty($_POST["ansb"]) ? NULL : $_POST["ansb"],
            "c" => empty($_POST["ansc"]) ? NULL : $_POST["ansc"],
            "d" => empty($_POST["ansd"]) ? NULL : $_POST["ansd"],
            "answer" => $_POST["anscorrect"]
        ]);

        // echo "<script>alert(\"" . strval(count($_SESSION["quiz"]["questions"])) . "\");</script>";

        # if have more questions to complete, move to the next one
        if (count($_SESSION["quiz"]["questions"]) != $_SESSION["quiz"]["num questions"]){
            header("location: create_quiz_question.php"); # ?Message=" . urlencode(strval(count($_SESSION["quiz"]["questions"]) - $_SESSION["quiz"]["num questions"])));
            exit;
        } else { # else insert quiz
            $success = true;
            $sql = "INSERT INTO quizzes SET author_uid = ?, title = ?, duration = ?, available = ?, modifiable = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssiii", 
                    $_SESSION["quiz"]["author"], 
                    $_SESSION["quiz"]["name"], 
                    $_SESSION["quiz"]["duration"], 
                    $_SESSION["quiz"]["available"],
                    $_SESSION["quiz"]["non-author modifiable"]
                );

                if ($quiz_id = mysqli_stmt_execute($stmt)) {
                    // great! But I don't even know if the opposite returns 0, null, -1, nullptr, or something else.
                } else { 
                    echo "Something went wrong. Please try again later. Code: 1";
                    $success = false;
                }
            } else { 
                echo "Something went wrong. Please try again later. Code: 2";
                $success = false;
            }
            mysqli_stmt_close($stmt);

            foreach ($_SESSION["quiz"]["questions"] as $question) {
                $sql = "INSERT INTO quiz_questions SET quiz_id = ?, text = ?, a = ?, b = ?, c = ?, d = ?, answer = ?";
                
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param(
                        $stmt, "issssss",
                        $quiz_id,
                        $question["text"],
                        $question["a"],
                        $question["b"],
                        $question["c"],
                        $question["d"],
                        $question["answer"],
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        // cool! keep it up!
                    } else {
                        echo "Quiz id: " . strval($quiz_id) . "\n;";
                        echo "Something went wrong. Please try again later. Code: 3";
                        $success = false;
                    }
                } else { 
                    echo "Something went wrong. Please try again later. Code: 4";
                    $success = false;
                }
                mysqli_stmt_close($stmt);
            }

            if ($success) {
                header("location: index.php?Message=" . urlencode("Successfully created quiz!"));
                exit;
            }
        }
    }
}

// if (isset($_SESSION["quiz"])) {
//     echo strval($_SESSION["quiz"]["num questions"]) . " questions:<br>";
//     foreach ($_SESSION["quiz"]["questions"] as $question) {
//         echo "Question text: " . $question["text"] . "<br>";
//         echo "Answer a: " . $question["ansa"] . "<br>";
//         echo "Answer b: " . $question["ansb"] . "<br>";
//         echo "Answer c: " . $question["ansc"] . "<br>";
//         echo "Answer d: " . $question["ansd"] . "<br>";
//         echo "Correct answer: " . $question["anscorrect"] . "<br>";
//     }
// }
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
        ?>

        <form action="create_quiz_question.php" method="post">
            <div class="container">

                <label><h3>Question <?php echo strval(count($_SESSION["quiz"]["questions"]) + 1) . "/" . strval($_SESSION["quiz"]["num questions"]); ?></h3></label>

                <label for="qtext"><b>Question text</b></label>
                <input type="text" pattern=".*\S+.*" placeholder="Enter question text" name="qtext" required>

                <label for="ansa"><b>Answer a</b></label>
                <input type="text" placeholder="Enter answer" name="ansa" required>

                <label for="ansb"><b>Answer b</b></label>
                <input type="text" placeholder="Enter answer" name="ansb">

                <label for="ansc"><b>Answer c</b></label>
                <input type="text" placeholder="Enter answer" name="andc">

                <label for="ansd"><b>Answer d</b></label>
                <input type="text" placeholder="Enter answer" name="ansd">
                
                <p>Note: Only multiple choice quizzes are supported at the moment. If the question has fewer than 4 possible answers, leave the remaining fields blank.</p><br>

                <label for="uid"><b>Correct answer:</b></label>
                <input type="text" pattern="^[a-d]$" placeholder="Enter letter corresponding to correct answer" name="anscorrect" required>

                <button type="submit"><?php if(count($_SESSION["quiz"]["questions"]) + 1 === $_SESSION["quiz"]["num questions"]) {echo "Submit";} else {echo "Next";} ?></button>
                <!-- <button type="submit">Next</button> -->

            </div>
        </form>

    </body>
</html>