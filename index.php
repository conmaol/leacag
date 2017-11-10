<?php

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
  <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet"/>
  <link href="../lexicopia/code/css/lexicopia-entries.css" rel="stylesheet"/>
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
                  <li id="newEntry"><a href="#" title="Contribute an entry">moladh</a></li>
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
          <span id="noResults">-- Chan eil toradh ann don cheist seo --</span>
          <ul id="suggestions" tabindex="0">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-xs-12" id="loggedInStatus">    <!-- displays the logged-in name -->
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div id="gaelicEquivalentsList">
        </div>
      </div>
    </div>
    <div id="formContainer">
      <form id="newEntryForm">
          <p>
              Briathar Beurla:
              <input type="text" class="formField" name="en"/>
          </p>
          <p>
              Briathar Gàidhlig:
              <input type="text" class="formField" name="target"/>
          </p>
          <!--p>
              Mìr-cainnte:
              <select name="pos">
                  <option>ainmear</option>
                  <option>buadhair</option>
                  <option>gnìomhair</option>
                  <option>eile</option>
              </select>
          </p-->
          <p>
              Cruthan Co-cheangailte:<br/>
              <textarea name="related" id="relatedNotesField" class="formField"></textarea>
          </p>
          <!--p>
              Tùs:<br/>
              <textarea name="source" id="sourceNotesField" class="formField"></textarea>
          </p-->
          <p>
              Tùs/Nòtaichean:<br/>
              <textarea name="notes" id="formNotesField" class="formField"></textarea>
          </p>
          <p>
              <input type="hidden" name="userEmail" id="userEmail"/>
              <input type="hidden" name="userID" id="userID"/>
              <input type="hidden" name="action" value="processNewEntryForm"/>
              <input type="hidden" name="lang" value="gd"/>
              <button class="popupClose">cuir às</button>
              <input type="submit" id="submitNewEntry" value="cuir a-steach"/>
          </p>
      </form>
      <!-- thank you message on form submission -->
      <div id="submitThanks">
          <h2>Mòran taing!</h2>
          <button type="button" class="popupClose">dùin</button>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
          <div id="content-div-entry">
              <div id="lexicalText"></div>
              <div id="homePageText">
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
              </div>
          </div>
          <div id="addCommentDiv">
              <a href="#" id="addCommentLink" title="Add a comment to this entry">Add comment to this entry</a>
                  <div id="addCommentFormContainer">
                  <form id="addCommentForm">
                      <h3>
                          Add comment:
                      </h3>
                      <p>
                          <textarea id="editHeadword" class="formField"></textarea>
                      </p>
                      <p>
                          <input type="hidden" name="action" value="addComment"/>
                          <button class="popupClose">cuir às</button>
                          <input type="submit" value="cuir a-steach"/>
                      </p>
                  </form>
              </div>
          </div>
          <div id="editEntryLink">
              <a href="#" id="editEntryButton" title="Edit this entry">Deasaich an innteart seo</a>
              <div id="editFormContainer">
                  <form id="editEntryForm" onsubmit="return submitEditEntryForm();">
                      <h3>
                          Ceann-fhacal
                      </h3>
                      <p>
                          <input type="text" id="editHeadword" class="formField" value=""/>
                      </p>
                      <p>
                          <input type="hidden" name="action" value="editEntry"/>
                          <button class="popupClose">cuir às</button>
                          <input type="submit" value="cuir a-steach"/>
                      </p>
                  </form>
              </div>
          </div>
      </div>
    </div>
  </div>
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>         <!-- whole library - change to downloaded subset later -->
  <script src="js/js.cookie.js"></script>
  <script src="js/jquery.bpopup.min.js"></script>
  <script src="../lexicopia/code/js/lexicopia-entries.js"></script>
  <script src="js/leacag.js"></script>

</body>
</html>

