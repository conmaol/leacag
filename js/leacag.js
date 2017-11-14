//var minChars = 3;
var lexicopiaId = null; //a global variable to keep track of the current lexical ID, to be used by submitEditEntryForm()
var auth2;  //a global variable to keep track of the Google sign-in state
var entryhistory = [];
$('#englishSearchField').focus();
var bpopup;     //to store and handle the modal popup

/* EVENT HANDLERS */

$("#enToGdToggle").on("click", function() {
    $("#englishSearchField").val("");
    $("#englishSearchForm").hide();
    $(this).hide();
    $("#gaelicSearchField").attr("placeholder", "Gàidhlig");
    $("#gaelicSearchForm").show();
    $("#gdToEnToggle").show();
    $("#gaelicSearchField").focus();
    $("#suggestionsDropDown").hide();
    $("#noResultsMessage").hide();
    $("#gaelicEquivalentsList").empty();
    $("#lexicalText").empty();
    $("#addCommentFormContainer").hide();
    $("#editEntryFormContainer").hide();
    $("#homePageText").show();
    return false;
});

$("#gdToEnToggle").on("click", function() {
    $("#gaelicSearchField").val("");
    $("#gaelicSearchForm").hide();
    $(this).hide();
    $("#englishSearchField").attr("placeholder", "Beurla");
    $("#englishSearchForm").show();
    $("#enToGdToggle").show();
    $("#englishSearchField").focus();
    $("#suggestionsDropDown").hide();
    $("#noResultsMessage").hide();
    $("#gaelicEquivalentsList").empty();
    $("#lexicalText").empty();
    $("#addCommentFormContainer").hide();
    $("#editEntryFormContainer").hide();
    $("#homePageText").show();
    return false;
});

$("#randomEntryLink").on("click", function() {
    $("#englishSearchField").val("");
    $("#gaelicSearchField").val("");
    $("#noResultsMessage").hide();
    $("#gaelicEquivalentsList").empty();
    $.getJSON("php/leacag.php?action=getRandom", function(data) {
        var randomID = data.id;
        entryhistory.push(randomID);
        updateContent(randomID);
        return false;
    })
});


$(function() {

    //close the dropdown when a navbar link is clicked (mobile)
    $('.navbar-collapse a').on('click', function(){
        $(".navbar-collapse").collapse('hide');
    });

    $('#englishSearchField').on({
        click: function() {
            $(this).val("");	//clear the search field for a new query
            $(this).attr('placeholder', 'Beurla');
            $('#noResultsMessage').hide();
        }
    });

    $('#gaelicSearchField').on({
        click: function() {
            $(this).val("");	//clear the search field for a new query
            $(this).attr('placeholder', 'Gàidhlig');
            $('#noResultsMessage').hide();
        }
    });

    $( "#englishSearchField" ).autocomplete({
        autoFocus: true,
        response: function (event, ui) {
            if (ui.content.length === 0) {
                $('#noResultsMessage').show();
                updateUserSearchDB($(this).val(), 1, 'en');    //log a failed search
            } else {
                $('#noResultsMessage').hide();
            };
        },
        source: "php/leacag.php?action=getEnglish",
        minLength: 3,
        select: function( event, ui ) {
            chooseSelectedTerm(ui.item, 'en');
        }
    });

    $( "#gaelicSearchField" ).autocomplete({
        autoFocus: true,
        response: function (event, ui) {
            if (ui.content.length === 0) {
                $('#noResultsMessage').show();
                updateUserSearchDB($(this).val(), 1, 'gd');    //log a failed search
            } else {
                $('#noResultsMessage').hide();
            };
        },
        source: "php/leacag.php?action=getGaelic",
        minLength: 3,
        select: function( event, ui ) {
            chooseSelectedTerm(ui.item, 'gd');
        }
    });

    /*
     Sign out code
     */
    $('#signOutLink').hide();
    $('.signOut').hide();
    $('#newEntryLink').hide();
    $('#signOutLink').on('click', function () {
        $('.signOut').hide();
        $('#newEntryLink').hide();
        $('.abcRioButtonContents').show();
        $('.g-signin2').show();
        $('.abcRioButtonContents > span').eq(1).hide();   //hide the 'Signed In' text
        $('.abcRioButtonContents > span').eq(0).show();   //show the 'Sign In' text
        $('#loggedInStatusMessage').hide();  //hide logged-in status
        $('#editEntryLink').hide();
        $('#addCommentLink').hide();
        Cookies.remove('userEmail');
        $.ajax('ajax.php?action=logout');
        gapi.auth2.getAuthInstance().disconnect();
        console.log('User signed out.');  //debug code only
    });
});

$('.popupClose').on('click', function () {  //close the popup on click
    bpopup.close();
    return false;
});

function onSignIn(googleUser) {
    var profile = googleUser.getBasicProfile();

    //authenticate the user
    var id_token = googleUser.getAuthResponse().id_token;

  /*  $.post("ajax.php", {action: "authenticate", idtoken: id_token}, function(data) {

        if(data.userid) {*/

            //add user info to form fields
            $('.userEmail').val(profile.getEmail());
            $('#userID').val(profile.getId());

            $.ajax({
                method: "GET",
                url: 'ajax.php?action=login&email=' + profile.getEmail()
            });

            auth2 = gapi.auth2.getAuthInstance();

            //Update the button to display "Sign Out" option
            $('.g-signin2').hide();
            $('#signOutLink').show();
            $('.signOut').show();
            //Show the signed-in message
            var loggedInMsg = 'Air a chlàradh a-steach mar ' + profile.getName();

            //check for admin status
            $.getJSON("ajax.php?action=checkAdmin", function (data) {
                if (data.isAdmin) {
                    loggedInMsg += '&nbsp;&nbsp;<a href="admin.php">> rianaire</a>';
                }
            })
                .done(function () {
                    $('#loggedInStatusMessage').html(loggedInMsg).show();
                });
            //check for submitter status
            $.getJSON("ajax.php?action=checkSubmitter", function (data) {
                if (data.isSubmitter) {
                    $('#newEntryLink').show();
                    if ($('.lexicopia-headword').html()) {
                        $('#addCommentLink').show();
                    }
                }
            });
            //check for editor status
            $.getJSON("ajax.php?action=checkEditor", function (data) {
                if (data.isEditor && $('.lexicopia-headword').html()) {
                    $('#editEntryLink').show();
                }
            });
  /*      }
    }, "json");*/
}

/*
$('#englishSearchField').on({
    keyup: function (e) {
        var search = $('#englishSearchField').val();
        if (e.which === 13 && search.length >= minChars) {  //handle the submission of a query with return key
            $.getJSON("php/leacag.php?action=getEnglish&term=" + search, function (data) {
                if (data.length === 0 || data[0].value.toLowerCase() !== search.toLowerCase()) {    //no matching results
                    $('#noResults').show();
                    updateUserSearchDB(search, 1, 'en');    //log a failed search
                } else {    //there is a result
                    $('#englishSearchField').autocomplete('close');
                    chooseSelectedTerm(data[0], 'en');
                }
            });
        }
    },
    keydown: function(e) {
        if (e.which == 13) {
            e.preventDefault();
        }
    },
    click: function() {
        $(this).val("");	//clear the search field for a new query
        $(this).attr('placeholder', 'Beurla');
        $('#noResults').hide();
    }
});


$('#gaelicSearchField').on({
    keyup: function (e) {
        var search = $('#gaelicSearchField').val();
        if (e.which === 13 && search.length >= minChars) {  //handle the submission of a query with return key
            $.getJSON("php/leacag.php?action=getGaelic&term=" + search, function (data) {
                if (data.length === 0 || data[0].value.toLowerCase() !== search.toLowerCase()) {    //no matching results
                    $('#noResults').show();
                    updateUserSearchDB(search, 1, 'gd');    //log a failed search
                } else {    //there is a result
                    $('#gaelicSearchField').autocomplete('close');
                    chooseSelectedTerm(data[0], 'gd');
                }
            });
        }
    },
    keydown: function(e) {
        if (e.which == 13) {
            e.preventDefault();
        }
    },
    click: function() {
        $(this).val("");	//clear the search field for a new query
        $(this).attr('placeholder', 'Gàidhlig');
        $('#noResults').hide();
    }
});
*/

//the event handler has to be attached to the document in order to register the dynamically added elements
$(document).on('click', '.leacag-link', function() {
    var id = $(this).attr('id');
    updateContent(id);
    return false;
});

function chooseSelectedTerm(item, lang) {
    updateUserSearchDB(item.value, 0, lang);           //records the search as successful in the server DB
    $('#englishSearchField').val("");
    $('#gaelicSearchField').val("");
    $('#gaelicEquivalentsList').empty();
    if (lang=='en') {
        var gds = item.item.gds;
        if (gds.length > 1) {
            $('#gaelicEquivalentsList').append("Faclan Gàidhlig airson <i>" + item.value + "</i>: ");
            for(var i = 0;i < gds.length;i++) {
                $('#gaelicEquivalentsList').append('<a class="leacag-link" href="#" id="' + gds[i].id + '">' + gds[i].form + '</a>');
                if (i<(gds.length - 1)) {
                    $('#gaelicEquivalentsList').append(', ');
                }
            }
            $('#homePageText').hide();
        }
        else {
            entryhistory.push(gds[0].id);
            updateContent(gds[0].id);
        }
    } else if (lang=='gd') {
        entryhistory.push(item.id);
        updateContent(item.id);
    }
}



/*
$('#backbutton').on("click", function() {
    goBack();
    return false;
});
*/

function updateContent(id) {
    $('#homePageText').hide();
    // update the content panel when a new lexical entry is selected
    $('#lexicalText').load("../lexicopia/code/php/generateLexicalEntry.php?lang=gd&id=" + id);
    //check for editor status and show edit link
    $.getJSON("ajax.php?action=checkEditor", function(data) {
        if (data.isEditor) {
            $('#editEntryLink').show();
        }
    });
    //check for submitter status and show comment link
    $.getJSON("ajax.php?action=checkSubmitter", function(data) {
        if (data.isSubmitter) {
            $('#addCommentLink').show();
        }
    });
    lexicopiaId = id;
    if (entryhistory.length > 1) {
        //document.getElementById("backbutton").style.display = 'block';
        $('#backbutton').show();
    }
    else {
        //document.getElementById("backbutton").style.display = 'none';
        $('#backbutton').hide();
    }
    $('#englishSearchField').attr('placeholder', '');
    $('#gaelicSearchField').attr('placeholder', '');
}

/*
 * Add the user email and search term to the database
 */
function updateUserSearchDB(searchTerm, failed, language) {
    var userProfile = getUser();
    var userId = "anonymous";
    var userEmail = "anonymous";
    if (userProfile !== false) {    //user logged-in
        userId = userProfile.getId();
        userEmail = userProfile.getEmail();
    }
    $.ajax({
        method: "GET",
        url: 'ajax.php?action=logSearchTerm&searchTerm='+searchTerm+'&failed='+failed+'&language='+language+'&id='+userId+'&email='+userEmail
    })
    .done(function (msg) {
        console.log("Attempted DB update : " + msg);
    });
}

/*
 * Get signed-in Google user
 */
function getUser() {
    if (!auth2) {
        return false;
    }
    var user = auth2.currentUser.get();
    var profile = user.getBasicProfile();
    if (profile) {
        return profile;
    } else {
        return false;
    }
}

/*
 Form submission code
 */
//show the forms

$("#newEntryLink a").on("click", function () {
    bpopup = $("#newEntryFormContainer").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#newEntryForm").show();
});

$("#addCommentLink").on("click", function () {
    bpopup = $("#addCommentFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#addCommentForm").show();
});

$("#editEntryLink").on("click", function () {
    bpopup = $("#editEntryFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#editHeadword").val($(".lexicopia-headword").html());
    $("#editEntryForm").show();
});

/* Process data submitted by contributors for new entry */

$("#newEntryForm").on("submit", function () {
    var formData = $(this).serialize();
    $.post("ajax.php", formData, function (data) {
        console.log(data);
    });
    $("#newEntryForm").hide();
    $("#newEntryForm").trigger('reset');
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});

/* Process comment form */

$("#addCommentForm").on("submit", function () {
    $("#lexId").val(lexicopiaId);
    var formData = $(this).serialize();
    $.post("ajax.php", formData, function (data) {
        console.log(data);
    });
    $("#addCommentForm").hide();
    $("#addCommentForm").trigger('reset');
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});

/*
    Process data submitted by editor to update an entry
 */
$('#editEntryForm').on('submit', function () {
    var newHeadword = $('#editHeadword').val();
    $('.lexicopia-headword').html(newHeadword);
    $.ajax('ajax.php?action=updateHeadword&id='+lexicopiaId+'&form='+newHeadword);
    $('#editEntryForm').hide();
    $('#editEntryForm').trigger('reset');
    bpopup.close();
    bpopup = $('#submitThanksPopUp').bPopup({
        modal: true
    });
    return false;
});
