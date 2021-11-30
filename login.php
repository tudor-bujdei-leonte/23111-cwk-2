<!doctype html>
<html>
    <head>
        <link rel="stylesheet" href="styles.css">
        <title>Quizzy!</title>
    </head>
    <body>
        <div class="topnav">
            <a class="active" href="index.html">Home</a>
            <a href="login.html">Log in</a>
            <a href="#contact">Register</a>
        </div>

        Welcome <?php echo $_POST["name"]; ?><br>
        Your UID address is: <?php echo $_POST["uid"]; ?><br>
        Your password is: <?php echo $_POST["psw"] ?>
        Are you staff: <?php echo $_POST["is_staff"] ?>
        Remember details: <?php echo $_POST["remember"] ?>
    </body>
</html>