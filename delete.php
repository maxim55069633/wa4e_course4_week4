<?php
require_once "pdo.php";
session_start();


if( ! isset($_SESSION['name']))
{
    die("Not logged in");
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id'] ) ) ;
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
$first_name=htmlentities($row["first_name"]);
$last_name= htmlentities($row["last_name"]);
$profile_Id=$row["profile_id"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kai 9b2910e1's Delete</title>
</head>
<body>
  

<h1>Deleteing Profile</h1>

<form method="post">
<br>
<p>
First Name: <?= $first_name ?></p> 
<br>
<p>
Last Name: <?= $last_name ?> </p>


<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete">
<a href="index.php">Cancel</a>
</form>

</body>
</html>
