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
      $sth = $dbh->prepare("INSERT INTO leacag_user (email, firstLogin) VALUES (:email, :firstLogin)");
      $sth->execute(array(":email"=>$_GET["user"], "firstLogin"=>date('Y-m-d H:i:s', now())));
    }
    setcookie('userEmail', $_GET["user"]);
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
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("INSERT INTO leacag_formSubmission (email, en, gd, pos, notes) VALUES (:email, :en, :gd, :pos, :notes)");
    if ($sth->execute(array(":email"=>$_POST["userEmail"], ":en"=>$_POST["en"], ":gd"=>$_POST["gd"], ":pos"=>$_POST["pos"], ":notes"=>$_POST["notes"]))) {
      echo "Form data added to DB...";
    } else {
      echo "There was a problem saving the form data";
    }
    break;
}
