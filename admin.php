<?php
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 22/03/2017
 * Time: 19:59
 */

require_once '../includes/include.php';
require_once 'include.php';

//initialise variables
$editUserHtml = $submissionHtml = $noSubMessage = "";
if (!isset($_GET["action"])) {
  $_GET["action"] = "";
}

//run an admin check
$dbh = DB::getDatabaseHandle(DB_NAME);
$sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
$sth->execute(array(":email"=>$_SESSION["email"]));
$row = $sth->fetch();
$userAccessLevel = (int)$row[0];

/*
 * TODO: Make this error message more user friendly
 */
if ($userAccessLevel < EDITOR_ACCESS_LEVEL) {
  die ('You are not authorised to view this page');
}

//update the user information if form submitted
$userUpdatedMsg = "";
if (isset($_GET["accessLevel"])) {
  $sth = $dbh->prepare("UPDATE leacag_user SET accessLevel = :accessLevel WHERE email = :email");
  $sth->execute(array(":accessLevel"=>$_GET["accessLevel"], ":email"=>$_GET["email"]));
  $userUpdatedMsg = "<h2>User data saved</h2>";
}

//assemble the edit user HTML
$userFormHtml = "";

/*
 * Write the access level options
 */
if ($_GET["action"] === "editUser") {
  $sth = $dbh->prepare("SELECT email, accessLevel, firstLogin, lastLogin  FROM leacag_user WHERE email = :email");
  $sth->execute(array(":email" => $_GET["user"]));
  $row = $sth->fetch();
  if ($row["accessLevel"] == 0 || $row["accessLevel"] > $userAccessLevel) {   //anonymous cannot be promoted OR is higher level than user
      $accessLevelOptions = $accessLabels[$row["accessLevel"]];
  } else {
    $accessLevelOptions = "<select id=\"accessLevel\" name=\"accessLevel\">";
    foreach ($accessLabels as $i => $label) {
      if ($i == 0) {
        continue;                      //cannot demote to anonymous status
      }
      if ($i >= $userAccessLevel) {
        break;                        //do not show levels equal to or above this user's access level
      }
      $selected = ($row["accessLevel"] == $i) ? "selected" : "";
      $accessLevelOptions .= "\n<option value=\"{$i}\" {$selected}>{$label}</option>";

    }
    $accessLevelOptions .= "</select>";
  }

  //assemble the search history
  $sth = $dbh->prepare("SELECT searchTerm, failed, language, timestamp FROM leacag_userActivity WHERE email = :email");
  $sth->execute(array(":email" => $_GET["user"]));
  $searchHistoryHtml = "";
  while ($searchRow = $sth->fetch()) {
    $failed = ($searchRow["failed"] == 1) ? "yes" : "no";
    $tableRowStyle = ($failed === "yes") ? 'class="tableRowHighlight"' : '';
    $searchHistoryHtml .= <<<HTML
      <tr {$tableRowStyle}>
        <td>{$searchRow["searchTerm"]}</td>
        <td>{$failed}</td>
        <td>{$searchRow["language"]}</td>
        <td>{$searchRow["timestamp"]}</td>
      </tr>
HTML;
  }
  //assemble the submission history
  $sth = $dbh->prepare("SELECT en, gd, pos, related, source, notes, timestamp FROM leacag_formSubmission WHERE email = :email");
  $sth->execute(array(":email" => $_GET["user"]));
  $submissionHtml = $noSubMessage = "";
  if ($sth->rowCount() == 0) {
    $noSubMessage = "<p>There are no submissions from this user</p>";
  } else {
      while ($subRow = $sth->fetch()) {
          $submissionHtml .= <<<HTML
            <tr>
              <td>{$subRow["en"]}</td>
              <td>{$subRow["gd"]}</td>
              <td>{$subRow["pos"]}</td>
              <td>{$subRow["related"]}</td>
              <td>{$subRow["source"]}</td>
              <td>{$subRow["notes"]}</td>
              <td>{$subRow["timestamp"]}</td>
            </tr>
HTML;
      }
  }
  $editUserHtml = <<<HTML
  <h2>User Information:</h2>
  <form name="userForm">
    <table>
      <tbody>   
        <tr>     
          <td>Email:</td>
          <td>{$row["email"]}</td>
        </tr>
        <tr>     
          <td>Access Level:</td>
          <td>
            {$accessLevelOptions}
          </td>
        </tr>
        <tr>     
          <td>First Login:</td>
          <td>{$row["firstLogin"]}</td>
        </tr>
        <tr>     
          <td>Last Login:</td>
          <td>{$row["lastLogin"]}</td>
        </tr>        
      </tbody>
    </table>
    <input type="hidden" name="email" value="{$row["email"]}"/>
    <input type="submit" value="save"/>
  </form>
  <h2>User Search History</h2>
  <span id="showSearchHistory" class="displaySearchHistory"><a href="#" class="toggleSearchHistory">Show History</a></span>
  <span id="hideSearchHistory" class="displaySearchHistory"><a href="#" class="toggleSearchHistory">Hide History</a></span>
  <table name="searchHistory" id="searchHistory" class="tablesorter displaySearchHistory">
    <thead>
      <tr>
        <th>Term</th>
        <th>Failed</th>
        <th>Language</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      {$searchHistoryHtml}
    </tbody>
  </table>
  <h2>User Submission History</h2>
  {$noSubMessage}
HTML;
}

if ($noSubMessage === "") {     //only write the table if it has entries
    $editUserHtml .= <<<HTML
  <span id="showSubmissionHistory" class="displaySubmissionHistory"><a href="#" class="toggleSubmissionHistory">Show History</a></span>
  <span id="hideSubmissionHistory" class="displaySubmissionHistory"><a href="#" class="toggleSubmissionHistory">Hide History</a></span>
  <table name="submissionHistory" id="submissionHistory" class="tablesorter displaySubmissionHistory">
    <thead>
      <tr>
        <th>English</th>
        <th>Gaelic</th>
        <th>POS</th>
        <th>Related Forms</th>
        <th>Source</th>
        <th>Notes</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      {$submissionHtml}
    </tbody>
  </table>
HTML;
}

$editUserHtml .= <<<HTML
  <p>
    <a href="admin.php"><< Back to Admin Home</a>
  </p>
HTML;


//get all users
$users = array();
$sth = $dbh->prepare("SELECT email, accessLevel, firstLogin, lastLogin  FROM leacag_user ORDER BY email ASC");
$sth->execute();
while ($row = $sth->fetch()) {
  $users[] = $row;
}
//assemble the table HTML
$tableHtml = "";
foreach ($users as $user) {
  $tableHtml .= <<<HTML
    <tr>
        <td><a href="?action=editUser&user={$user["email"]}">{$user["email"]}</a></td>
        <td>{$accessLabels[$user["accessLevel"]]}</td>
        <td>{$user["firstLogin"]}</td>
        <td>{$user["lastLogin"]}</td>
    </tr>
HTML;
}
$userTableHtml = <<<HTML
 <a href="index.php">< Back to site</a>
 <table id="users" class="tablesorter">
    <thead>
      <tr>
        <th>Email</th>
        <th>Access Level</th>
        <th>First Login</th>
        <th>Last Login</th>
      </tr>
    </thead>
    <tbody>
        {$tableHtml}
    </tbody>
  </table>
HTML;

?>

<!DOCTYPE html>
<html lang="gd" xmlns="http://www.w3.org/1999/html">
<head>
  <title>LEACAG Admin</title>
  <link href="css/leacag.css" rel="stylesheet"/>
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="../js/jquery.tablesorter.min.js"></script>
</head>
<body>
  <h1>LEACAG Admin</h1>

  <?php
//    echo <<<HTML
//        <a href="index.php">< Back to site</a>
//HTML;

    echo $userUpdatedMsg;

    switch($_GET["action"]) {
      case "editUser":
        echo $editUserHtml;
        break;
      default:
        echo $userTableHtml;
    }
  ?>

</body>

<script src="js/admin.js"></script>
</html>


