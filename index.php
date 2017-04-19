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
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

?>

<!DOCTYPE html>
<html lang="gd" xmlns="http://www.w3.org/1999/html">
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="google-signin-client_id" content="1067716944598-u8oj6j87j4ho6lm726au2ap3spf5d508.apps.googleusercontent.com">
  <title>LeaCaG</title>
  <script src="https://apis.google.com/js/platform.js" async defer></script>
  <link href="css/bootstrap.min.css" rel="stylesheet"/>
  <link href="../lexicopia/lexicopia-web/code/css/lexicopia-entries.css" rel="stylesheet"/>
  <link href="css/leacag.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
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
                  <h1 class="navbar-left"><a href="index.php">LEACAG</a></h1>
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
                  <li id="randomEntry"><a href="#" title="Random entry">iongnadh</a></li>
                  <li id="enToGdToggle"><a href="#" title="Search for a Gaelic word">Gàidhlig</a></li>
                  <li id="gdToEnToggle"><a href="#" title="Search for an English word">Beurla</a></li>
                  <li id="formLink"><a href="#" title="Contribute an entry">moladh</a></li>
                  <li id="loginButtons">
                      <div class="g-signin2" data-onsuccess="onSignIn">Sign In</div>
                      <div class="signOut">
                          <div class="googleIcon">
                              <img src="images/btn_google.png" width="28" height="28">
                          </div>
                          <a href="#" id="signOutLink" class="loginLink">Sign Out</a>
                      </div>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div>
          <span id="noResults">-- There are no results for this query --</span>
          <ul id="suggestions" tabindex="0">
          </ul>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-xs-12 loggedInStatus">    <!-- displays the logged-in name -->
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div id="gaelicEquivalentsList">
        </div>
      </div>
    </div>
    <div id="formContainer">
      <form id="userForm" onsubmit="return processForm();">
          <p>
              English term:
              <input type="text" class="formField" name="en"/>
          </p>
          <p>
              Gaelic term:
              <input type="text" class="formField" name="gd"/>
          </p>
          <p>
              Part-of-speech:
              <select name="pos">
                  <option>noun</option>
                  <option>adjective</option>
                  <option>verb</option>
                  <option>other</option>
              </select>
          </p>
          <p>
              Notes:<br/>
              <textarea name="notes" id="formNotesField" class="formField"></textarea>
          </p>
          <p>
              <input type="hidden" name="userEmail" id="userEmail"/>
              <input type="hidden" name="userID" id="userID"/>
              <input type="hidden" name="action" value="processForm"/>
              <button class="popupClose">cancel</button>
              <input type="submit"/>
          </p>
      </form>
      <!-- thank you message on form submission -->
      <div id="submitThanks">
          <h2>Mòran taing!</h2>
          <button type="button" class="popupClose">close</button>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
          <div id="content-div-entry">
              <span id="lexicalText"></span>
              <span id="homePageText">
              <p><strong>Fàilte gu co-ionad briathrachais LEACAG!</strong></p>
              <p>Chì sibh bocsa teacsa aig ceann na duilleige seo, air an làimh chlì. Cuiribh a-steach na ciad litrichean den fhacal a tha sibh a' sireadh, agus taghaibh fear de na molaidhean.</p>
              <p class="englishTranslation">Welcome to the LEACAG Gaelic terminology hub!</p>
              <p class="englishTranslation">You will see a textbox at the top-left of this page. Type in the first few letters of the word you are looking for, and then choose one of the suggestions that appear.</p>
              <hr/>
              <p>
                  Seo co-ionad air-loidhne a bhios a' cuideachadh daoine briathrachas feumail Gàidhlig a lorg agus a sgaoileadh.
                  Chaidh a innleachadh le Mark McConville, a tha ag obair ann an sgioba rannsachaidh DASG (Dachaidh
                  airson Stòras na Gàidhlig) aig Oilthigh Ghlaschu, fo stiùireadh an Àrd-ollamh Roibeard Ó Maolalaigh, agus
                  le taic coimpiùtarachd bho Stephen Barrett.
              </p>
              <p>
                  Is e toradh pròiseict LEACAG (Leasachadh Corpais na Gàidhlig) a tha anns a' ghoireas seo. Tha LEACAG air a mhaoineachadh le
                  Bòrd na Gàidhlig agus MG Alba.
              </p>
              <p>
                  Airson tuilleadh fiosrachaidh, nach cuir sibh post dealain thugainn? Mark.McConville@glasgow.ac.uk.
              </p>
              <p class="englishTranslation">
                  This is an online hub for finding and distributing useful Gaelic terminology. It has been developed by Mark McConville, a member of the
                  DASG (Digital Archive of Scottish Gaelic) research team at Glasgow
                  University, under the leadership of Professor Roibeard Ó Maolalaigh, and with systems development support from Stephen Barrett.
              </p>
              <p class="englishTranslation">
                  This resource is an output of the LEACAG (Gaelic Corpus Development) project, funded by Bòrd na Gàidhlig and MG Alba.
              </p>
              <p class="englishTranslation">
                  If you have any questions, you can email the editor: Mark.McConville@glasgow.ac.uk
              </p>
              <p>
                  <a href="http://dasg.ac.uk/" title="DASG" target="_blank"><img src="http://dasg.ac.uk/images/logo.png" height="70px" alt="DASG"/></a>
                  <a href="http://www.glasgow.ac.uk/" title="Glasgow University" target="_blank"><img src="http://www.gla.ac.uk/media/media_446862_en.png" height="70px" alt="Glasgow University"/></a>
                  <a href="http://www.gaidhlig.org.uk/" title="Bòrd na Gàidhlig" target="_blank"><img src="http://www.gaidhlig.scot/wp-content/uploads/2016/11/logo_bng.png" height="70px" alt="Bòrd na Gàidhlig"/></a>
                  <a href="http://mgalba.com/" title="MG Alba" target="_blank"><img src="http://mgalba.com/images/logo-new-80x67.png" height="70px" alt="MG Alba"/></a>
                  <a href="http://www.soillse.ac.uk/" title="Soillse" target="_blank"><img src="http://www.soillse.ac.uk/wp-content/themes/soillse/images/logo.png" height="70px" alt="Soillse"/></a>
              </p>
              </span>
          </div>
      </div>
    </div>
  </div>
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/js.cookie.js"></script>
  <script src="js/jquery.bpopup.min.js"</script>
  <script src="../lexicopia/lexicopia-web/code/js/lexicopia-entries.js"></script>
  <script>
	var id_token = null;	//needs to be defined before leacag.js is loaded
  </script>
  <script src="js/leacag.js"></script>
  <script type="text/javascript">
    $(function() { //close the dropdown when a navbar link is clicked (mobile)
      $('.navbar-collapse a').on('click', function(){
        $(".navbar-collapse").collapse('hide');
      });
      /*
        Sign out code
      */
      $('#signOutLink').hide();
      $('.signOut').hide();
      $('#signOutLink').on('click', function () {
          Cookies.remove('userEmail');
          gapi.auth2.getAuthInstance().disconnect();
          console.log('User signed out.');  //debug code only
          $('.signOut').hide();
          $('#formLink').hide();
          $('.abcRioButtonContents').show();
          $('.g-signin2').show();
          $('.abcRioButtonContents > span').eq(1).hide();   //hide the 'Signed In' text
          $('.abcRioButtonContents > span').eq(0).show();   //show the 'Sign In' text
          $('.loggedInStatus').hide();  //hide logged-in status
      });
    });

    var lang = 'gd';
    var entryhistory = [];
    $('#englishSearchField').focus();
    var bpopup;     //to store and handle the modal popup
    $('.popupClose').on('click', function () {  //close the popup on click
        bpopup.close();
        return false;
    });

    function onSignIn(googleUser) {
      var profile = googleUser.getBasicProfile();
      console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
      //add user info to form fields
      $('#userEmail').val(profile.getEmail());
      $('#userID').val(profile.getId());

      $.ajax({
          method: "GET",
          url: 'ajax.php?action=email&user='+profile.getEmail()+'&name='+profile.getName()
      })
      .done(function (msg) {
          console.log("AJAX called : " + msg);
          auth2 = gapi.auth2.getAuthInstance();

          //Update the button to display "Sign Out" option
          $('.g-signin2').hide();
          $('#signOutLink').show();
          $('.signOut').show();
          //Show the signed-in message
          var loggedInMsg = 'Signed in as ' + profile.getName();
          //check for admin status
          $.getJSON("ajax.php?action=checkAdmin", function(data) {
              if (data.isAdmin) {
                  loggedInMsg += '&nbsp;&nbsp;<a class="leacag-link" href="admin.php">> admin</a>';
              }
          })
              .done(function() {
                  $('.loggedInStatus').html(loggedInMsg).show();
          });
      });

      /*
 	//User ID authentication via HTTPS with Google Token ID
 	//Requires PHP 5.5.9
      id_token = googleUser.getAuthResponse().id_token;
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'https://dasg.ac.uk/leacag/ajax.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
       	console.log('Signed in as: ' + xhr.responseText);
      };
      xhr.send('action=authId&idtoken='+id_token);
  */
    }

    /*
     Form submission code
     */
    //show the form link if user is submitter
    if (Cookies.get("userEmail")) {
        //check for submitter status
        $.getJSON("ajax.php?action=checkSubmitter", function(data) {
            if (data.isSubmitter) {
                $('#formLink').show();
                $('#formLink a').on('click', function () {
                    bpopup = $('#formContainer').bPopup({
                        modal: true
                    });
                    $('#submitThanks').hide();
                    $('#userForm').show();
                });
            }
        });
    }
    
    function processForm() {
        var formData = $('#userForm').serialize();
        $.post('ajax.php', formData, function (data) {
            console.log(data);
        });
        //display a thank you message
        $('#userForm').hide();
        $('#submitThanks').show();
        return false;
    }
  </script>
</body>
</html>

