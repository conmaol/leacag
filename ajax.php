<?php
header('Access-Control-Allow-Origin: http://dasg.ac.uk');
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 09/02/2017
 * Time: 12:40
 */

session_start();
ini_set("display_errors", 1);
date_default_timezone_set("Europe/London");

define("ADMIN_ACCESS_LEVEL", 5);    //move to sitewide include file?
define("SUBMIT_ACCESS_LEVEL", 2);   //move to sitewide include file?
define("USERFILE_PATH", "../lexicopia/lexicopia-xml/gd/terminology/user-generated.xml");  //move to sitewide include file?
define("ENGLISH_INDEX_PATH", "../lexicopia/lexicopia-cache/gd/english-index.json");       //move to sitewide include file?
define("TARGET_INDEX_PATH", "../lexicopia/lexicopia-cache/gd/target-index.json");         //move to sitewide include file?

/*
 * commented out for development
 */
require_once '../includes/include.php';

require_once 'vendor/autoload.php';

switch ($_REQUEST["action"]) {
  case "email":
  	//debug
  		echo "Email turned off for development";
  	/*
    $to       = "mail@steviebarrett.com";
    $message  = $_GET["user"] . " signed in on LeaCaG";
    $subject  = "LeaCaG sign-in";
    $from     = "stephen.barrett@glasgow.ac.uk";
    $email    = new Email($to, $message, $subject, $from);
    echo "Attempting email...";
    if ($email->send()) {
      echo " Email sent.";
    } else {
      echo " The email could not be sent.";
    }
    */
    //check if user already exists in system
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("SELECT firstLogin FROM leacag_user WHERE email = :email");
    $sth->execute(array(":email"=>$_GET["user"]));
    $row = $sth->fetch();
    if (!empty($row[0])) {
      //user exists so just update the lastLogin
      $timestamp = date('Y-m-d H:i:s', time());
      $sth = $dbh->prepare("UPDATE leacag_user SET lastLogin = '{$timestamp}' WHERE email = :email");
      $sth->execute(array(":email"=>$_GET["user"]));
    } else {
      //new user, create a new DB entry
      $sth = $dbh->prepare("INSERT INTO leacag_user (email, name, firstLogin) VALUES (:email, :name, :firstLogin)");
      $sth->execute(array(":email"=>$_GET["user"], ":name"=>$_GET["name"], "firstLogin"=>date('Y-m-d H:i:s', time())));
    }
    setcookie('userEmail', $_GET["user"]);
    break;
  case "logSearchTerm":
  	//add the data to the leacag_userActivity table
  	try {
  		$dbh = DB::getDatabaseHandle(DB_NAME);
  		$sth = $dbh->prepare("INSERT INTO leacag_userActivity (googleId, email, failed, language, searchTerm) VALUES (:id, :email, :failed, :language, :searchTerm)");
  		$sth->execute(array(":id"=>$_GET["id"], ":email"=>$_GET["email"], ":failed"=>$_GET["failed"], ":language"=>$_GET["language"], ":searchTerm"=>$_GET["searchTerm"]));
		echo "Database updated";
  	} catch (PDOException $e) {
  		echo $e->getMessage();
  	}
  	break;
  case "authId":
    $id_token = $_POST["idtoken"];
    $client = new Google_Client(['client_id' => '1067716944598-u8oj6j87j4ho6lm726au2ap3spf5d508.apps.googleusercontent.com']);
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
      $userid = $payload['sub'];
      $_SESSION["userid"] = $userid;
      $_COOKIE["userid"] = $userid;
      echo "User verified : {$userid}";
    } else {
      echo "Not verified";
    }
    break;
  case "processForm":
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("INSERT INTO leacag_formSubmission (email, en, gd, pos, notes) VALUES (:email, :en, :gd, :pos, :notes)");
    if ($sth->execute(array(":email"=>$_POST["userEmail"], ":en"=>$_POST["en"], ":gd"=>$_POST["gd"], ":pos"=>$_POST["pos"], ":notes"=>$_POST["notes"]))) {
      echo "Form data added to DB...";
      //assign the ID
      $id = $_POST["gd"] . '-' . time();
      //write the XML
      $xml = getEntryXml($_POST, $id);
      file_put_contents(USERFILE_PATH, $xml.PHP_EOL, FILE_APPEND | LOCK_EX);
      //write the Target JSON
      $targetFile = file_get_contents(TARGET_INDEX_PATH);
      $targetJson = json_decode($targetFile, true);
      array_push($targetJson["target_index"], getTargetEntry($_POST, $id));
      file_put_contents(TARGET_INDEX_PATH, json_encode($targetJson), LOCK_EX);
      //write the English JSON
      $englishFile = file_get_contents(ENGLISH_INDEX_PATH);
      $englishJson = json_decode($englishFile, true);
      array_push($englishJson["english_index"], getEnglishEntry($_POST, $id));
      file_put_contents(ENGLISH_INDEX_PATH, json_encode($englishJson), LOCK_EX);
      $to       = "mail@steviebarrett.com";             //update this to Mark's email for live site
      $message  = "A LEACAG user has submitted a new form entry.";
      $subject  = "LEACAG Form Submission";
      $from     = "stephen.barrett@glasgow.ac.uk";
      $email    = new Email($to, $subject, $message, $from);
      echo "Attempting email...";
      if ($email->send()) {
        echo " Email sent.";
      } else {
        echo " The email could not be sent.";
      }
    } else {
      echo "There was a problem saving the form data";
    }
    break;
  case "checkAdmin":
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
    $sth->execute(array(":email"=>$_COOKIE["userEmail"]));
    $row = $sth->fetch();
    $result = array("isAdmin" => $row[0] == ADMIN_ACCESS_LEVEL);
    echo json_encode($result);
    break;
  case "checkSubmitter":
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
    $sth->execute(array(":email"=>$_COOKIE["userEmail"]));
    $row = $sth->fetch();
    $result = array("isSubmitter" => $row[0] >= SUBMIT_ACCESS_LEVEL);
    echo json_encode($result);
    break;
}

function getEnglishEntry($fields, $id) {
  $entry = array("en" => $fields["en"], "gds" => array(array("id" => "{$id}")));  //change ID from ellipsis
  return $entry;
}

function getTargetEntry($fields, $id) {
  $entry = array("word" => $fields["gd"], "id" => "{$id}", "en" => $fields["en"]);  //change ID from ellipsis
  return $entry;
}

function getEntryXml($fields, $id) {
  $timestamp = time();
  $xml = <<<XML
    <sign id="{$id}">
        <form>{$fields["gd"]}</form>
        <syntax ref="{$fields["pos"]}"/>
        <trans lang="en">{$fields["en"]}</trans>
        <note>Contributed by user {$fields["userEmail"]} at {$timestamp}</note>
    </sign>
XML;
  return $xml;
}

