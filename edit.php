<?php // line 1 added to enable color highlight
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

if( ! isset($_SESSION['name']))
{
    die("Not logged in");
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
      
      header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
      return;
  }else if (strpos($_POST['email'], '@' ) ===false )
  {
      $_SESSION['error'] = "Email address must contain @";
      
      header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
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
          
    $sql = "UPDATE profile SET first_name = :first_name,  last_name = :last_name, email = :email, headline = :headline,
    summary=:summary    WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute(array(
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':profile_id' => $_POST['profile_id']
        
        
        ))
        ;

    // Clear the old education entries only when you submit new entries
    $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Clear the old position entries only when you submit new entries
    $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    $pos_rank = 1;
    $edu_rank = 1;

    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year_'.$i]) ) continue;
      if ( ! isset($_POST['edu_school_'.$i]) ) continue;
  
      $year = $_POST['edu_year_'.$i];

      $stmt = $pdo->prepare("SELECT * FROM institution WHERE name = :xyz");
      $stmt->execute(array(":xyz" => $_POST['edu_school_'.$i]));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);


      if ($row) {
        $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :edu_rank, :edu_year)');

        $stmt->execute(array(
           ':pid' => $_REQUEST['profile_id'],
           ':iid' => $row['institution_id'],
           ':edu_rank' => $edu_rank,
           ':edu_year' => $year),
        );

        echo ($row['institution_id']);
      } else {

        $stmt = $pdo->prepare('INSERT INTO institution ( name ) VALUES ( :name)');
        $stmt->execute(array(
           ':name' => $_POST['edu_school_'.$i],
           )
        );

        $last_ins_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :edu_rank, :edu_year)');
        $stmt->execute(array(
           ':pid' => $_REQUEST['profile_id'],
           ':iid' => $last_ins_id,
           ':edu_rank' => $edu_rank,
           ':edu_year' => $year),
        );
      }
      $edu_rank++;
    }
        
    // Insert the position entries

    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['pos_year_'.$i]) ) continue;
      if ( ! isset($_POST['desc_'.$i]) ) continue;

      $year = $_POST['pos_year_'.$i];
      $desc = $_POST['desc_'.$i];
      $stmt = $pdo->prepare('INSERT INTO position
        (profile_id, rank, year, description)
        VALUES ( :pid, :rank, :year, :desc)');

      $stmt->execute(array(
      ':pid' => $_REQUEST['profile_id'],
      ':rank' => $pos_rank,
      ':year' => $year,
      ':desc' => $desc)
      );

      $pos_rank++;

    }
    
    $_SESSION['success'] = 'Profile updated';
    header( 'Location: index.php' ) ;
    return;
  }
  
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));

$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}


$first_name = htmlentities($row['first_name']);
$last_name = htmlentities($row['last_name']);
$email = htmlentities($row['email']);
$headline = htmlentities($row['headline']);
$summary = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

// check the positions
$stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :xyz ORDER BY rank ASC " );
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$pos_outcomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// check the educations
$stmt = $pdo->prepare("SELECT * FROM education WHERE profile_id = :xyz ORDER BY rank ASC " );
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$edu_outcomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ins_outcomes = []; // Initialize the institutions array
//  push the institution name in the education order
$array_length = count($edu_outcomes);

for ($i = 0; $i < $array_length; $i++) {
   $ins_id = $edu_outcomes[$i]["institution_id"];
   $stmt = $pdo->prepare("SELECT * FROM institution WHERE institution_id = :xyz" );
   $stmt->execute(array(":xyz" => $ins_id ));
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
   $ins_outcomes[] = htmlentities($row["name"]);
}

?>


<!DOCTYPE html>
<html>
<head>
<title>Kai's Profile Edit</title>
<!-- bootstrap.php - this is HTML -->
<?php require_once "head.php"; ?>
</head>

<body>
<div class="container"> 

  <?php 
  echo '
  <h1>Editting Profile for '.$_SESSION['name'].' </h1>
  ';
  

if ( isset($_SESSION['error']) ) {
        echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
        unset($_SESSION['error']);
    }

    ?>

<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60" value=<?= $first_name ?> /></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value=<?= $last_name ?> /></p>
<p>Email:
<input type="text" name="email" size="30" value=<?= $email ?>> 
</p>
<p>Headline:<br>
<input type="text" name="headline" size="80" value=<?= $headline ?> > 
</p>
<p>Summary:<br>
<textarea name="summary" rows="8" cols="80"  > <?php echo ($summary); ?>  </textarea>
</p>
<input type="hidden" name="profile_id" value="<?= $profile_id ?>">


<p>
Education: <input type="submit" id="addEdu" value="+">
</p>

<div id="education_fields"></div>

<p>
Position: <input type="submit" id="addPos" value="+">
</p>

<div id="position_fields"></div>


<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>


<script>

    // get the length of the education array
    let temp_educations = <?php echo json_encode($edu_outcomes); ?>;
    let countEdu = temp_educations.length;

    let temp_institutions = <?php echo json_encode($ins_outcomes); ?>;


    let temp_positions = <?php echo json_encode($pos_outcomes); ?>;
    let countPos = temp_positions.length;

    
    // display all the educations that have already existed
    for(let i=0; i<countEdu; i++ ){
      $("#education_fields").append('\
        <div id="education_'+(i+1)+'"> \
          <p>Year: <input type="text" name="edu_year_'+(i+1)+'" value="'+temp_educations[i]["year"]+'"> \
            <input type="button" value="-" onclick="$(\'#education_'+(i+1)+'\').remove();return false;"> \
          </p> \
          <p>School: <input type="text" size="80" name="edu_school_'+(i+1)+'" class="school" value="'+temp_institutions[i]+'" /></p> \
        </div>'); 
    }
    $('.school').autocomplete({ source: 'school.php' });
    
    // display all the positions that have already existed 
    for(let i=0; i<countPos; i++ ){
      $("#position_fields").append('\
        <div id="position_'+temp_positions[i]["position_id"]+'"> \
          <p>Year: <input type="text" name="pos_year_'+(i+1)+'" value="'+temp_positions[i]["year"]+'"> \
            <input type="button" value="-" onclick="$(\'#position_'+temp_positions[i]["position_id"]+'\').remove();return false;"> \
          </p> \
          <textarea name="desc_'+(i+1)+'" rows="8" cols="80">'+temp_positions[i]["description"]+'</textarea> \
        </div>'); 
      
    }

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
                  <p>School: <input type="text" size="80" name="edu_school_'+countEdu+'" class="school" value="" /></p> \
                </div>'); 

                $('.school').autocomplete({ source: 'school.php' });
                
            } else {
                alert("Maximum of nine education entries exceeded");
                return false;
            }

        }        
    )

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
