<?php
session_start();
require "pdo.php";
require_once "head.php";
?>

<!DOCTYPE html>
<html>

<head>
<title>Kai's Profile View</title>

</head>

<body>
<div class="container">
<h1>Profile information</h1>

<?php

 $pos_outcomes = []; // Initialize the positions array
 $edu_outcomes = []; // Initialize the educations array
 $ins_outcomes = []; // Initialize the institutions array

 $stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz");
 $stmt->execute(array(":xyz" => $_GET['profile_id']));
 $row = $stmt->fetch(PDO::FETCH_ASSOC);
 if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
 } else {
    $first_name = htmlentities($row['first_name']);
    $last_name = htmlentities($row['last_name']);
    $email = htmlentities($row['email']);
    $headline = htmlentities($row['headline']);
    $summary = htmlentities($row['summary']);

    $stmt = $pdo->prepare("SELECT * FROM education WHERE profile_id = :xyz ORDER BY rank ASC " );
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $edu_outcomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

   //  push the institution name in the education order
   $array_length = count($edu_outcomes);


   for ($i = 0; $i < $array_length; $i++) {
      $ins_id = $edu_outcomes[$i]["institution_id"];
      $stmt = $pdo->prepare("SELECT * FROM institution WHERE institution_id = :xyz" );
      $stmt->execute(array(":xyz" => $ins_id ));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $ins_outcomes[] = $row["name"];
   }

    $stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :xyz ORDER BY rank ASC " );
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $pos_outcomes = $stmt->fetchAll(PDO::FETCH_ASSOC);


 }
?>

<p>First Name: <?= $first_name ?></p>
<p>Last Name: <?= $last_name ?></p>
<p>Email: <?= $email ?></p>
<p>Headline:<br><?= $headline ?></p>
<p>Summary:<br><?= $summary ?></p>
<p id="eduList_p" style="display: none">
    Education :
</p>
<ul id="eduList_ul"></ul>
<p id="posList_p" style="display: none">
    Position :
</p>
<ul id="posList_ul"></ul>

<script>

   // add a eventlistener to the ul element for any DOM change 
   let target_pos = $('#posList_ul')[0];
   let target_edu = $('#eduList_ul')[0];

   // Create a new MutationObserver instance
   let observer_pos = new MutationObserver(function(mutations) {
   mutations.forEach(function(mutation) {
      if (mutation.type === 'childList') {
         if ($("#posList_ul").children().length == 0) {
         $("#posList_p").hide();
         } else {
         $("#posList_p").show();
         }
      }
   });
   });

    // Create a new MutationObserver instance
    let observer_edu = new MutationObserver(function(mutations) {
   mutations.forEach(function(mutation) {
      if (mutation.type === 'childList') {
         if ($("#eduList_ul").children().length == 0) {
         $("#eduList_p").hide();
         } else {
         $("#eduList_p").show();
         }
      }
   });
   });

   // Configure the observer to watch for changes to the target_pos element
   let config = { childList: true };
   observer_pos.observe(target_pos, config);
   observer_edu.observe(target_edu, config);


   let all_educations = [];
   if (<?= $edu_outcomes ?>)
   {
      let year_results = <?php echo json_encode($edu_outcomes); ?>;
      let name_results = <?php echo json_encode($ins_outcomes); ?>;
      for (prop in year_results) {
         let newEdu = $("<li>");
         newEdu.html(`${year_results[prop]["year"]}: ${name_results[prop]}`);
         $("#eduList_ul").append(newEdu[0]);
      }
   } 
   
   let all_positions = [];
   if (<?= $pos_outcomes ?>)
   {
      let temp_results = <?php echo json_encode($pos_outcomes); ?>;
      for (prop in temp_results) {
         let newPos = $("<li>");
         newPos.html(`${temp_results[prop]["year"]}: ${temp_results[prop]["description"]}`);
         $("#posList_ul").append(newPos[0]);
      }
   }


</script>

<p>
<a href="index.php">Done</a>
</p>

</div>
</body>