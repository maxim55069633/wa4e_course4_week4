
<?php
require_once "pdo.php";

session_start();


function validatePos() {
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['pos_year_'.$i]) ) continue;
      if ( ! isset($_POST['desc_'.$i]) ) continue;
  
      $year = $_POST['pos_year_'.$i];
      $desc = $_POST['desc_'.$i];
  
      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
        return "All fields are required";
      }
  
      if ( ! is_numeric($year) ) {
        return "Position year must be numeric";
      }
    }
    return true;
  }


  function validateEdu() {
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year_'.$i]) ) continue;
      if ( ! isset($_POST['edu_school_'.$i]) ) continue;
  
      $year = $_POST['edu_year_'.$i];
      $desc = $_POST['edu_school_'.$i];
  
      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
        return "All fields are required";
      }
  
      if ( ! is_numeric($year) ) {
        return "Education year must be numeric";
      }
    }
    return true;
  }


if ( ! isset($_SESSION['name']) ) {
    die('Not logged in');
}

 
if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}


if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) 
&& isset($_POST['headline']) && isset($_POST['summary']) 

)
 {
  if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1
  || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
  || strlen($_POST['summary']) < 1 
  ) {
      
      $_SESSION['error'] = "All fields are required";
      
      header("Location: add.php");
      return;
  }else if (strpos($_POST['email'], '@' ) ===false )
  {
      $_SESSION['error'] = "Email address must contain @";
      
      header("Location: add.php");
      return;
  } else {
    $validateEducation = validateEdu();
    if ( $validateEducation !== true) {
        $_SESSION['error'] = $validateEducation ;
        header("Location: add.php");
        return;
    }
    
    $validatePosition = validatePos();
    if ( $validatePosition !== true) {
        $_SESSION['error'] = $validatePosition ;
        header("Location: add.php");
        return;
    }
    
    try {
      $stmt = $pdo->prepare('INSERT INTO profile (first_name, last_name, email, headline, summary, user_id) VALUES (:first_name, :last_name, :email, :headline, :summary, :user_id)');
      $stmt->execute(array(
          ':first_name' => $_POST['first_name'],
          ':last_name' => $_POST['last_name'],
          ':email' => $_POST['email'],
          ':headline' => $_POST['headline'],
          ':summary' => $_POST['summary'],
          ':user_id' => $_SESSION['user_id']
      ));

      $profile_id = $pdo->lastInsertId();
      $pos_rank = 1;

    //   add related positions 
      for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['pos_year_'.$i]) ) continue;
        if ( ! isset($_POST['desc_'.$i]) ) continue;
    
        $year = $_POST['pos_year_'.$i];
        $desc = $_POST['desc_'.$i];

        $stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :pos_rank, :pos_year, :desc)');

        $stmt->execute(array(
           ':pid' => $profile_id,
           ':pos_rank' => $pos_rank,
           ':pos_year' => $year,
           ':desc' => $desc)
        );
        $pos_rank++;
      }

      $edu_rank = 1;
      //   add related educations 
      for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['edu_year_'.$i]) ) continue;
        if ( ! isset($_POST['edu_school_'.$i]) ) continue;
    
        $year = $_POST['edu_year_'.$i];

        // There are two situations. 1. the school is newly created. 2. the school has already existed. 
        


        $stmt = $pdo->prepare("SELECT * FROM institution WHERE name = :xyz");
        $stmt->execute(array(":xyz" => $_POST['edu_school_'.$i]));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
          $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :pos_rank, :edu_year)');

          $stmt->execute(array(
             ':pid' => $profile_id,
             ':iid' => $row['institution_id'],
             ':pos_rank' => $edu_rank,
             ':edu_year' => $year),
          );
        } else {

          $stmt = $pdo->prepare('INSERT INTO institution ( name ) VALUES ( :name)');
          $stmt->execute(array(
             ':name' => $_POST['edu_school_'.$i],
             )
          );

          $last_ins_id = $pdo->lastInsertId();

          $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :pos_rank, :edu_year)');
          $stmt->execute(array(
             ':pid' => $profile_id,
             ':iid' => $last_ins_id,
             ':pos_rank' => $edu_rank,
             ':edu_year' => $year),
          );
        }
        
        

        $edu_rank++;
      }




      echo 'Profile added successfully';
      $_SESSION['success'] = 'Profile added';

  } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage();
      $_SESSION['error'] = 'Error: ' . $e->getMessage();
  }

    // $_SESSION['success'] = 'Profile added';
    header( 'Location: index.php' ) ;
    return;
  }
  
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Kai's Profile Add</title>
<?php require_once "head.php"; ?>
<!-- bootstrap.php - this is HTML -->
<!-- 
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>


<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">

<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous"> -->

</head>

<body>
<div class="container"> 
    
  <?php 
  echo '
  <h1>Adding Profile for '.$_SESSION['name'].' </h1>
  ';
  

    if ( isset($_SESSION['error']) ) {
        echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
        unset($_SESSION['error']);
    }

    ?>

<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60"  /></p>
<p>Last Name:
<input type="text" name="last_name" size="60"  /></p>
<p>Email:
<input type="text" name="email" size="30" /> 
</p>
<p>Headline:<br>
<input type="text" name="headline" size="80"  > 
</p>
<p>Summary:<br>
<textarea name="summary" rows="8" cols="80"  > </textarea>
  </p>
<p>

<p>
Education: <input type="submit" id="addEdu" value="+">
</p>

<div id="education_fields">
    
</div>

<p>
Position: <input type="submit" id="addPos" value="+">
</p>

<div id="position_fields">
    
</div>
 
<input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>


<script>
    let countEdu = 0;
    let countPos = 0;
    
    $("#addEdu").click(
        function(e){
            e.preventDefault();
            countEdu++;

            if ( countEdu <=9) {

                $("#education_fields").append('\
                <div id="education_'+countEdu+'"> \
                  <p>Year: <input type="text" name="edu_year_'+countEdu+'" value=""> \
                    <input type="button" value="-" onclick="$(\'#education_'+countEdu+'\').remove();return false;"> \
                  </p> \
                  <p>School: \
                    <input type="text" size="80" name="edu_school_'+countEdu+'" class="school" value="" /> \
                  </p> \
                </div>'); 

                $('.school').autocomplete({ source: 'school.php' });

                
                
            } else {
                alert("Maximum of nine education entries exceeded");
                return false;
            }
        }        
    );


    $("#addPos").click(
        function(e){
            e.preventDefault();
            countPos++;

            if ( countPos <=9) {

                $("#position_fields").append('\
                <div id="position_'+countPos+'"> \
                  <p>Year: <input type="text" name="pos_year_'+countPos+'" value=""> \
                    <input type="button" value="-" onclick="$(\'#position_'+countPos+'\').remove();return false;"> \
                  </p> \
                  <textarea name="desc_'+countPos+'" rows="8" cols="80"></textarea> \
                </div>'); 
            } else {
                alert("Maximum of nine position entries exceeded");
                return false;
            }
        }        
    );
</script>

</body>
</html>