<?php
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 09/02/2017
 * Time: 12:40
 */

switch ($_GET["action"]) {
  case "email":
    $message = $_GET["user"] . " signed in on LeaCaG";
    $headers = "From: stephen.barrett@glasgow.ac.uk\r\nReply-To: stephen.barrett@glasgow.ac.uk";
    echo "Attempting email...";
    $sent = mail("mark.mcconville@glasgow.ac.uk", "LeaCaG sign-in", $message, $headers);
    if ($sent) {
      echo " Email sent.";
    } else {
      echo " The email could not be sent.";
    }
}