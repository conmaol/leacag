<?php
header('Access-Control-Allow-Origin: http://dasg.ac.uk');
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 09/02/2017
 * Time: 12:40
 */

require_once '../includes/include.php';
require_once 'include.php';
require_once 'vendor/autoload.php';

switch ($_REQUEST["action"]) {
  case "login":
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
    setcookie('userEmail', $_GET["user"], time()+14000); //userEmail cookie expires within 4 hours
    $_SESSION["email"] = $_GET["user"];
    break;
  case "logout":
    setcookie("userEmail", "", time()-3600);  //delete the cookie
    unset($_SESSION["email"]);
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
    $sth = $dbh->prepare("INSERT INTO leacag_formSubmission (email, en, gd, pos, related, source, notes) VALUES (:email, :en, :gd, :pos, :related, :source, :notes)");
    if ($sth->execute(array(":email"=>$_POST["userEmail"], ":en"=>$_POST["en"], ":gd"=>$_POST["gd"],
        ":pos"=>$_POST["pos"], ":related"=>$_POST["related"], ":source"=>$_POST["source"],  ":notes"=>$_POST["notes"]))) {
      echo "Form data added to DB...";
      //assign the ID
      $id = str_replace(" ", "_", $_POST["gd"]);
      $id = $id . '-' . time();
      //write the XML
      $lexicon = simplexml_load_file(USERFILE_PATH);
      $lexicon = getEntryXml($lexicon, $_POST, $id);
      $lexicon->asXml(USERFILE_PATH);
      //write the Target JSON
      $targetFile = file_get_contents(TARGET_INDEX_PATH);
      $targetJson = json_decode($targetFile, true);
      array_push($targetJson["target_index"], getTargetEntry($_POST, $id));
      file_put_contents(TARGET_INDEX_PATH, json_encode($targetJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
      //write the English JSON
      $englishFile = file_get_contents(ENGLISH_INDEX_PATH);
      $englishJson = json_decode($englishFile, true);
      array_push($englishJson["english_index"], getEnglishEntry($_POST, $id));
      file_put_contents(ENGLISH_INDEX_PATH, json_encode($englishJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
      $to       = "mark.mcconville@glasgow.ac.uk";
      $message  = getFormEmailText($_POST);
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
    $sth->execute(array(":email"=>$_SESSION["email"]));
    $row = $sth->fetch();
    $result = array("isAdmin" => $row[0] >= EDITOR_ACCESS_LEVEL);
    echo json_encode($result);
    break;
  case "checkSubmitter":
    $dbh = DB::getDatabaseHandle(DB_NAME);
    $sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
    $sth->execute(array(":email"=>$_SESSION["email"]));
    $row = $sth->fetch();
    $result = array("isSubmitter" => $row[0] >= SUBMIT_ACCESS_LEVEL);
    echo json_encode($result);
    break;
  case "checkEditor":
    $result = checkEditor();
    echo json_encode($result);
    break;
  case "updateHeadword":
    $result = checkEditor();
    if ($result["isEditor"]) {
      $id = $_GET["id"];
      $form = str_replace(" ", "_", $_GET["form"]);
      $cmd = exec('php ../lexicopia/lexicopia-web/code/php/setheadword.php gd ' . $id . ' '  . $form .' 2>&1', $output, $return_var);
      echo $cmd; print_r($output); echo $return_var;
      //update the English JSON file
      $englishFile = file_get_contents(ENGLISH_INDEX_PATH);
      $englishJson = json_decode($englishFile);
      foreach ($englishJson->english_index as $entry) {
        foreach ($entry->gds as $gds) {
          if ($gds->id == $id) {
            $gds->form = $_GET["form"];
          }
        }
      }
      file_put_contents(ENGLISH_INDEX_PATH, json_encode($englishJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
      //update the Target JSON file
      $targetFile = file_get_contents(TARGET_INDEX_PATH);
      $targetJson = json_decode($targetFile);
      foreach ($targetJson->target_index as $entry) {
        if ($entry->id == $id) {
          $entry->word = $_GET["form"];
        }
      }
      file_put_contents(TARGET_INDEX_PATH, json_encode($targetJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    $to       = "mark.mcconville@glasgow.ac.uk";

    $to       = "mail@steviebarrett.com";


    $message  = <<<TEXT
      The headword {$_GET["id"]} has been updated to {$_GET["form"]}
TEXT;

    $subject  = "LEACAG Headword Update";
    $from     = "stephen.barrett@glasgow.ac.uk";
    $email    = new Email($to, $subject, $message, $from);
    echo "Attempting email...";
    if ($email->send()) {
      echo " Email sent.";
    } else {
      echo " The email could not be sent.";
    }

    break;
}

function getEnglishEntry($fields, $id) {
  $entry = array("en" => $fields["en"], "gds" => array(array("id" => "{$id}")));
  return $entry;
}

function getTargetEntry($fields, $id) {
  $entry = array("word" => $fields["gd"], "id" => "{$id}", "en" => $fields["en"]);
  return $entry;
}

function getEntryXml($element, $fields, $id) {
  $timestamp = time();
  $sign = $element->addChild("sign");
  $sign->addAttribute('id', $id);
  $sign->addChild('form', $fields["gd"]);
  $sign->addChild('syntax');
  $posOptions = array("ainmear"=>"noun", "buadhair"=>"adjective", "gnìomhair"=>"verb", "eile"=>"other");
  $sign->syntax->addAttribute('ref', $posOptions[$fields["pos"]]);
  $sign->addChild('trans', $fields["en"]);
  $sign->trans->addAttribute('lang', 'en');
  $sign->addChild('note', $fields["related"]);
  $sign->addChild('note', $fields["source"]);
  $noteText = "Contributed by user {$fields["userEmail"]} at {$timestamp}";
  $sign->addChild('note', $noteText);
  return $element;
}

function getFormEmailText($fields) {
  $text = <<<TEXT
User {$fields["userEmail"]} submitted the following entry to LEACAG:<br/>
- English Term: {$fields["en"]}<br/> 
- Gaelic Term: {$fields["gd"]}<br/>
- Part of Speech: {$fields["pos"]}<br/>
- Related Forms: {$fields["related"]}<br/>
- Source: {$fields["source"]}<br/>
- Notes: {$fields["notes"]}
TEXT;
  return $text;
}

function checkEditor() {
  $dbh = DB::getDatabaseHandle(DB_NAME);
  $sth = $dbh->prepare("SELECT accessLevel FROM leacag_user WHERE email = :email");
  $sth->execute(array(":email"=>$_SESSION["email"]));
  $row = $sth->fetch();
  $result = array("isEditor" => $row[0] >= EDITOR_ACCESS_LEVEL);
  return $result;
}
