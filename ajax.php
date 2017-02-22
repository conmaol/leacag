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

/*
 * commented out for development
 */
//require_once '../includes/include.php';

require_once 'vendor/autoload.php';

switch ($_REQUEST["action"]) {
  case "email":
  	//debug
  		echo "Email turned off for development";
  		break;
  	//
      /*
       * TODO: update the email code to use DASG Email class?
       */
    $message = $_GET["user"] . " signed in on LeaCaG";
    $headers = "From: stephen.barrett@glasgow.ac.uk\r\nReply-To: stephen.barrett@glasgow.ac.uk";
    echo "Attempting email...";
    $sent = mail("mail@steviebarrett.com", "LeaCaG sign-in", $message, $headers);
    if ($sent) {
      echo " Email sent.";
    } else {
      echo " The email could not be sent.";
    }
    break;
  case "logSearchTerm":
  	//add the data to the leacag_userActivity table
  	try {
  		$dbh = DB::getDatabaseHandle(DB_NAME);
  		$sth = $dbh->prepare("INSERT INTO leacag_userActivity (googleId, email, searchTerm) VALUES (:id, :email, :searchTerm)");
  		$sth->execute(array(":id"=>$_GET["id"], ":email"=>$_GET["email"], ":searchTerm"=>$_GET["searchTerm"]));
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
    /*
     * TODO: update the email code to use DASG Email class?
     */
    $message = "User " . $_POST["userEmail"] . ", ID: " . $_POST["userID"] . "submitted the following:\n";
    $message = <<<text
      User {$_POST["userEmail"]} (ID: {$_POST["userID"]} submitted the following:\n
      Gaelic Form: {$_POST["gaelic-form"]}\n
      Part of Speech: {$_POST["pos"]}\n
      English Translation: {$_POST["english-translation"]}\n
      Notes:  {$_POST["notes"]}
text;
    $headers = "From: stephen.barrett@glasgow.ac.uk\r\nReply-To: stephen.barrett@glasgow.ac.uk";
    echo "Attempting email...";
    $sent = mail("mail@steviebarrett.com", "LeaCaG Form Submission", $message, $headers);
    if ($sent) {
      echo " Email sent.";
    } else {
      echo " The email could not be sent.";
    }
    break;
}
