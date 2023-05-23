<?php
require_once "pdo.php";
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Kai 9b2910e1's Resume Registry</title>
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
<h1>Kai's Resume Registry</h1>
<?php

            if ( isset($_SESSION['success']) ) {
                echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
                unset($_SESSION['success']);
            }


            if ( isset($_SESSION['name']) ) {
                echo '<br>
                <a href="logout.php">Logout</a> <br><br>
                ';
            } else {
                echo '<br>
                <a href="login.php">Please log in</a>
                ';
            }

            
            $stmt = $pdo->query("SELECT profile_id, user_id, first_name, last_name, headline FROM profile");

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( $row)
            {
            echo('<table border="1">'."\n");
            echo "<tr><td>";
                echo("<b>Name</b>");
                echo("</td><td>");
                echo("<b>Headline</b>");
                echo("</td>");

                if (isset($_SESSION["name"]))
                {
                    
                echo ("<td>");
                echo("<b>Action</b>");
                echo("</td>");

                }



                echo ("</tr>\n");

                echo "<tr><td>";
                echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
                echo($row["first_name"]);
                echo(" ");
                echo($row["last_name"]);
                echo('</a>');
                echo("</td><td>");
                echo(htmlentities($row['headline']));
                echo("</td>");
                if ( isset($_SESSION['name']) ) {
                    echo("<td>");
                    echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
                    echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
                    echo("</td>");    
                }

                echo("</tr>\n");

                while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                    echo "<tr><td>";
                    echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
                    echo($row["first_name"]);
                    echo(" ");
                    echo($row["last_name"]);
                    echo('</a>');
                    echo("</td><td>");
                    echo(htmlentities($row['headline']));
                    echo("</td>");
                    if ( isset($_SESSION['name']) ) {
                        echo("<td>");
                        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
                        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
                        echo("</td>");    
                    }

                    echo("</tr>\n");
                }
                echo "</table>";

            }

            if ( isset($_SESSION['name']) ) {
                echo '<br>
                <a href="add.php">Add New Entry</a> <br><br>
                ';
            }
   
?>

</div>
</body>