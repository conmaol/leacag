<?php
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 22/03/2017
 * Time: 19:59
 */

session_start();
ini_set("display_errors", 1);
date_default_timezone_set("Europe/London");

require_once '../includes/include.php';

//initialise variables
define("ADMIN_ACCESS_LEVEL", 5);
if (!isset($_GET["action"])) {
  $_GET["action"] = "";
}

//run an admin check
$dbh = DB::getDatabaseHandle(DB_NAME);
$sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
$sth->execute(array(":email"=>$_COOKIE["userEmail"]));
$row = $sth->fetch();
/*
 * TODO: Make this error message more user friendly
 */
if ($row[0] != ADMIN_ACCESS_LEVEL) {
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
if ($_GET["action"] === "editUser") {
  $sth = $dbh->prepare("SELECT email, accessLevel, firstLogin, lastLogin  FROM leacag_user WHERE email = :email");
  $sth->execute(array(":email" => $_GET["user"]));
  $row = $sth->fetch();
  $accessLevelOptions = "";
  for ($i=0; $i<=ADMIN_ACCESS_LEVEL; $i++) {
    $selected = ($row["accessLevel"] == $i) ? "selected" : "";
    $accessLevelOptions .= "\n<option value=\"{$i}\" {$selected}>{$i}</option>";
  }
  $editUserHtml = <<<HTML
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
            <select id="accessLevel" name="accessLevel">
                {$accessLevelOptions}
            </select>
          </td>
        </tr>
        <tr>     
          <td>First Login:</td>
          <td>{$row["firstLogin"]}</td>
        </tr>
        <tr>     
          <td>Email:</td>
          <td>{$row["lastLogin"]}</td>
        </tr>        
      </tbody>
    </table>
    <input type="hidden" name="email" value="{$row["email"]}"/>
    <input type="submit" value="save"/>
    <p>
      <a href="admin.php"><< Back to Admin Home</a>
    </p>
  </form>
HTML;
}

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
        <td>{$user["accessLevel"]}</td>
        <td>{$user["firstLogin"]}</td>
        <td>{$user["lastLogin"]}</td>
    </tr>
HTML;
}
$userTableHtml = <<<HTML
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

<script>
  $(function () {
      $('#users').tablesorter();
  })
</script>
</html>


