<?php
$menu = array(
    'home' => array('text'=>'Home', 'url'=>'index.php'),
    'login' => array('text'=>'Login', 'url'=>'login.php'),
    'register' => array('text'=>'Register', 'url'=>'register.php'),
    'take_quiz' => array('text'=>'Take quiz', 'url'=>'index.php'),
    'create_quiz' => array('text'=>'Create quiz', 'url'=>'index.php'),
    'modify_quiz' => array('text'=>'Modify quiz', 'url'=>'index.php'),
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
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" href="styles.css">
        <title>Quizzy!</title>
    </head>
    <body>
        <?php echo GenerateMenu($menu, "Home"); ?>

        <p>This is an example paragraph. Anything in the <strong>body</strong> tag will appear on the page, just like this <strong>p</strong> tag and its contents.</p>
    </body>
</html>