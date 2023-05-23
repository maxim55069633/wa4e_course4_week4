<?php
session_start();
require "pdo.php";

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        
        $_SESSION['error'] = "User name and password are required";
        
        header("Location: login.php");
        return;
    }else if(strpos($_POST['email'], '@' ) ===false )
    {
        $_SESSION['error'] = "Email must have an at-sign (@)";
        
        header("Location: login.php");
        return;
    } else {
                    
            $salt = 'XyZzy12*_';
            $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123
            // Check to see if we have some POST data, if we do process it
                
            $check = hash('md5', $salt.$_POST['pass']);

            $stmt = $pdo->prepare('SELECT user_id, name FROM users

                WHERE email = :em AND password = :pw');

            $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $row !== false ) {

                $_SESSION['name'] = $row['name'];

                $_SESSION['user_id'] = $row['user_id'];

                // Redirect the browser to index.php

                header("Location: index.php");

                return;

            } else {
                
                $_SESSION['error'] = "Incorrect password";

                header("Location: login.php");

                return;


            }


    }
    
}


?>

<!DOCTYPE html>
<html>
<head>
<title>Kai 9b2910e1's Login Page</title>
<!-- bootstrap.php - this is HTML -->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">

</head>

<body>
<div class="container">
<h1>Please Log In</h1>
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>

<form method="POST" action="login.php">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
For a password hint, view source and find an account and password hint
in the HTML comments.
<!-- Hint: 
The account is umsi@umich.edu
The password is the three character name of the 
programming language used in this class (all lower case) 
followed by 123. -->
</p>
<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>

</div>
</body>