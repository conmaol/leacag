<?php

/*
session_start();

date_default_timezone_set("Europe/London");

require_once 'vendor/autoload.php';

$client_id = "380282622225-rv86npt8t1n41etqti5a65a2mdtoig6c.apps.googleusercontent.com";
$client_secret = "z6mnwIQgWJFdqkSqXDEHUSiN";
$redirect_uri = "http://localhost/~stephenbarrett/leacag/index.php";

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("email");

$service = new Google_Service_Oauth2($client);

if (isset($_GET["code"])) {
    $client->authenticate($_GET["code"]);
    $_SESSION["access_token"] = $client->getAccessToken();
    header("Location: " . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}

if (isset($_SESSION["access_token"]) && $_SESSION["access_token"]) {
    $client->setAccessToken($_SESSION["access_token"]);
} else {
    $authUrl = $client->createAuthUrl();
}

if (isset($authUrl)) {
    echo <<<HTML
        <a href="{$authUrl}">Googe login</a>
HTML;
} else {
    $user = $service->userinfo->get();

    echo "<h1>User name: " . $user->name . "</h1>";
}
*/
?>


<!DOCTYPE html>
<html lang="gd">
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="google-signin-client_id" content="380282622225-rv86npt8t1n41etqti5a65a2mdtoig6c.apps.googleusercontent.com">
  <title>LeaCaG</title>
  <script src="https://apis.google.com/js/platform.js" async defer></script>
  <link href="css/bootstrap.min.css" rel="stylesheet"/>
  <link href="../lexicopia/lexicopia-web/code/css/lexicopia-entries.css" rel="stylesheet"/>
  <link href="css/leacag.css" rel="stylesheet"/>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
          <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container-fluid" style="padding-top: 10px;">
              <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                </button>
                <form class="navbar-form navbar-left" role="search" id="englishSearchForm">
                  <div class="form-group mainSearchBox">
                    <span class="glyphicon glyphicon-search"></span>
                    <input type="search" id="englishSearchField" placeholder="Beurla" autocomplete="off"/>
                    <!-- <a id="enToGdToggle" href="#">Gàidhlig?</a> -->
                  </div>
                </form>
                <form class="navbar-form navbar-left" role="search" id="gaelicSearchForm">
                  <div class="form-group mainSearchBox">
                    <span class="glyphicon glyphicon-search"></span>
                    <input type="search" id="gaelicSearchField" placeholder="Gàidhlig" autocomplete="off"/>
                    <!-- <a id="gdToEnToggle" href="#">Beurla?</a> -->
                  </div>
                </form>
              </div>
              <div class="collapse navbar-collapse navbar-right" id="navbar-collapse">
                <ul class="nav navbar-nav">
                  <li id="backbutton"><a href="#">air ais</a></li>
                  <li id="enToGdToggle"><a href="#" title="Search for a Gaelic word">Gàidhlig</a></li>
                  <li id="gdToEnToggle"><a href="#" title="Search for an English word">Beurla</a></li>
                  <li id="aboutLeacag"><a href="#" title="About LeaCaG">fios</a></li>
                  <li id="randomEntry"><a href="#" title="Random entry">iongnadh</a></li>
                  <li><div class="g-signin2" data-onsuccess="onSignIn">Sign In</div></li>
                </ul>
              </div>
            </div>
          </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div>
          <ul id="suggestions">
          </ul>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div id="gaelicEquivalentsList">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div id="content-div-entry">
          <p><strong>Fàilte gu siostam briathrachais LeaCaG!</strong></p>
          <p>Chì sibh bocsa teacsa aig ceann na duilleige seo, air an làimh chlì. Cuiribh a-steach na ciad litrichean dhen fhacal a tha sibh a' sireadh, agus taghaibh fear de na molaidhean.</p>
          <p class="englishTranslation">Welcome to the LeaCaG Gaelic terminology system!</p>
          <p class="englishTranslation">You will see a textbox at the top-left of this page. Type in the first few letters of the word you are looking for, and then choose one of the suggestions that appear.</p>
        </div>
      </div>
    </div>
    <a href="#" onclick="javascript:signOut();">Sign Out</a>
  </div>
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="../lexicopia/lexicopia-web/code/js/lexicopia-entries.js"></script>
  <script src="js/leacag.js"></script>
  <script type="text/javascript">
    $(function() { //close the dropdown when a navbar link is clicked (mobile)
      $('.navbar-collapse a').on('click', function(){
        $(".navbar-collapse").collapse('hide');
      });
    });
    var lang = 'gd';
    var entryhistory = [];
    $('#englishSearchField').focus();

    function onSignIn(googleUser) {
      var profile = googleUser.getBasicProfile();
      console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
      console.log('Name: ' + profile.getName());
      console.log('Image URL: ' + profile.getImageUrl());
      console.log('Email: ' + profile.getEmail());

      $.ajax({
          method: "GET",
          url: 'http://localhost/~stephenbarrett/leacag/ajax.php?action=email&user='+profile.getEmail()
      })
          .done(function (msg) {
              console.log("AJAX called : " + msg);
          });
   /*
      var id_token = googleUser.getAuthResponse().id_token;
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'http://localhost/~stephenbarrett/leacag/ajax.php?action=email&user='+profile.getEmail());
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        console.log('Email sent. Trace: ' + xhr.responseText);
      };
   //   xhr.send('idtoken' + id_token);
   */
    }

    function signOut() {
      var auth2 = gapi.auth2.getAuthInstance();
      auth2.signOut().then(function () {
        console.log('User signed out.');
      });
    }
  </script>
</body>
</html>
