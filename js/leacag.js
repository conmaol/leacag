/* GLOBAL VARIABLES */

var securityClearance = 0; // global variable for user privileges
var lexicopiaId = null; // global variable to keep track of the current lexical ID, to be used by submitEditEntryForm()
var auth2;  // global variable to keep track of the Google sign-in state
var entryhistory = [];
var bpopup;     // to store and handle the modal popup

$('#englishSearchField').focus();

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
    $("#addCommentLink").hide();
    $("#addEnglishLink").hide();
    $("#addFormOrthLink").hide();
    $("#authEnglishLink").hide();
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
    $("#addCommentLink").hide();
    $("#addEnglishLink").hide();
    $("#addFormOrthLink").hide();
    $("#authEnglishLink").hide();
    $("#homePageText").show();
    return false;
});

$("#randomEntryLink").on("click", function() {
    //$("#englishSearchField").val("");
    //$("#gaelicSearchField").val("");
    $("#noResultsMessage").hide();
    $("#gaelicEquivalentsList").empty();
    $.getJSON("php/leacag.php?action=getRandom", function(data) {
        var randomID = data.id;
        entryhistory.push(randomID);
        updateContent(randomID);
        return false;
    })
});

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

$("#addEnglishLink").on("click", function () {
    bpopup = $("#addEnglishFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#addEnglishForm").show();
});

$("#addFormOrthLink").on("click", function () {
    bpopup = $("#addFormOrthFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#addFormOrthForm").show();
});

$("#authEnglishLink").on("click", function () {
    bpopup = $("#authEnglishFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#authEnglishForm").show();
});

/*
$("#editEntryLink").on("click", function () {
    bpopup = $("#editEntryFormPopup").bPopup({
        modal: true
    });
    $("#submitThanksPopUp").hide();
    $("#editHeadword").val($(".lexicopia-headword").html());
    $("#editEntryForm").show();
});
*/

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

$("#addCommentForm").on("submit", function () {
    $("#lexId").val(lexicopiaId);
    console.log($("#lexId"));
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

$("#addEnglishForm").on("submit", function () {
    $("#lexId").val(lexicopiaId);
    var formData = $(this).serialize();
    $.post("ajax.php", formData, function (data) {
        console.log(data);
    });
    $("#addEnglishForm").hide();
    $("#addEnglishForm").trigger('reset');
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});

$("#addFormOrthForm").on("submit", function () {
    $("#lexId").val(lexicopiaId);
    var formData = $(this).serialize();
    $.post("ajax.php", formData, function (data) {
        console.log(data);
    });
    $("#addFormOrthForm").hide();
    $("#addFormOrthForm").trigger('reset');
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});

$("#authEnglishForm").on("submit", function () {
    $("#lexId").val(lexicopiaId);
    var formData = $(this).serialize();
    $.post("ajax.php", formData, function (data) {
        console.log(data);
    });
    $("#authEnglishForm").hide();
    $("#authEnglishForm").trigger('reset');
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});

/*
$("#editEntryForm").on("submit", function () {
    var newHeadword = $("#editHeadword").val();
    $(".lexicopia-headword").html(newHeadword);
    $.ajax("ajax.php?action=updateHeadword&id="+lexicopiaId+"&form="+newHeadword);
    $("#editEntryForm").hide();
    $("#editEntryForm").trigger("reset");
    bpopup.close();
    bpopup = $("#submitThanksPopUp").bPopup({
        modal: true
    });
    return false;
});
*/

$(".popupClose").on("click", function () {  //close the popup on click
    bpopup.close();
    return false;
});

// event handler has to be attached to the document in order to register the dynamically added elements
$(document).on("click", ".leacag-link", function() {
    var id = $(this).attr("id");
    updateContent(id);
    return false;
});

$("#englishSearchField").on({
    click: function() {
        $(this).val("");	//clear the search field for a new query
        $(this).attr("placeholder", "Beurla");
        $("#noResultsMessage").hide();
    }
});

$("#gaelicSearchField").on({
    click: function() {
        $(this).val("");	//clear the search field for a new query
        $(this).attr("placeholder", "Gàidhlig");
        $("#noResultsMessage").hide();
    }
});

$("#englishSearchField").autocomplete({
    autoFocus: true,
    response: function (event, ui) {
        if (ui.content.length === 0) {
            $("#noResultsMessage").show();
            updateUserSearchDB($(this).val(), 1, "en");    //log a failed search
        }
        else {
            $("#noResultsMessage").hide();
        };
    },
    source: "php/leacag.php?action=getEnglish",
    minLength: 3,
    select: function( event, ui ) {
        chooseSelectedTerm(ui.item, "en");
    }
});

$("#gaelicSearchField").autocomplete({
    autoFocus: true,
    response: function (event, ui) {
        if (ui.content.length === 0) {
            $("#noResultsMessage").show();
            updateUserSearchDB($(this).val(), 1, "gd");    //log a failed search
        }
        else {
            $("#noResultsMessage").hide();
        };
    },
    source: "php/leacag.php?action=getGaelic",
    minLength: 3,
    select: function( event, ui ) {
        chooseSelectedTerm(ui.item, "gd");
    }
});

/* HELPER FUNCTIONS */

function updateContent(id) {
    //$('#englishSearchField').attr('placeholder', '');
    //$('#gaelicSearchField').attr('placeholder', '');
    $("#englishSearchField").val("");
    $("#gaelicSearchField").val("");
    $("#homePageText").hide();
    // update the content panel when a new lexical entry is selected
    $("#lexicalText").load("../lexicopia/code/php/generateLexicalEntry.php?lang=gd&id=" + id);
    //check for Editor status and show edit link
    console.log("Security clearance: " + securityClearance);
    if (securityClearance > 2) {
        $("#authEnglishLink").show();
    }
    if (securityClearance > 1) {
        $("#addCommentLink").show();
        $("#addEnglishLink").show();
        $("#addFormOrthLink").show();
    }
    /*
    $.getJSON("ajax.php?action=checkEditor", function(data) {
        if (data.isEditor) {
            $("#editEntryLink").show();
        }
    });
    */
    //check for Contributor status and show comment link
    /*
    $.getJSON("ajax.php?action=checkSubmitter", function(data) {
        if (data.isSubmitter) {
            $("#addCommentLink").show();
        }
    });
    */
    lexicopiaId = id;
    $('#lexId').val(lexicopiaId);
    /*
    if (entryhistory.length > 1) {
        $("#backbutton").show();
    }
    else {
        $("#backbutton").hide();
    }
    */
}

function onSignIn(googleUser) {
    var profile = googleUser.getBasicProfile();
    //add user info to form fields
    $(".userEmail").val(profile.getEmail());
    $("#userID").val(profile.getId());
    $.ajax({
        method: "GET",
        url: "ajax.php?action=login&email=" + profile.getEmail()
    });
    auth2 = gapi.auth2.getAuthInstance();
    //Update the button to display "Sign Out" option
    $(".g-signin2").hide();
    $("#signOutLink").show();
    $(".signOut").show();

    $.getJSON("ajax.php?action=checkSecurityClearance", {}).done(function (data) {
        if (data.level) {
            securityClearance = data.level;
            var loggedInMsg = "Air a chlàradh a-steach mar " + profile.getName();
            if (securityClearance >= 4) {
                loggedInMsg += '&nbsp;&nbsp;<a href="admin.php">> rianaire</a>';
            }
            $("#loggedInStatusMessage").html(loggedInMsg).show();
            if (securityClearance > 1) {
                $("#newEntryLink").show();
                if ($(".lexicopiaHeadWord").html()) { // What does this mean?
                    $("#addCommentLink").show();
                    $("#addEnglishLink").show();
                    $("#addFormOrthLink").show();
                }
            }
            if (securityClearance > 2 && $(".lexicopiaHeadWord").html()) {
                $('#authEnglishLink').show();
            }
        }
    });
    //
    // $.getJSON("ajax.php?action=checkSubmitter", function (data) {
    //     if (data.isSubmitter) {
    //         securityClearance++;
    //     }
    // });
    // $.getJSON("ajax.php?action=checkEditor", function (data) {
    //     if (data.isEditor) {
    //         securityClearance++;
    //     }
    // });
    // $.getJSON("ajax.php?action=checkAdmin", function (data) {
    //     if (data.isAdmin) {
    //         securityClearance++;
    //     }
    // });
    // Show the signed-in message

    /*      }
      }, "json");*/
}

function getUser() {     /* Get signed-in Google user */
    if (!auth2) {
        return false;
    }
    var user = auth2.currentUser.get();
    var profile = user.getBasicProfile();
    if (profile) {
        return profile;
    }
    else {
        return false;
    }
}


/* MISC */

$(function() {

    //close the dropdown when a navbar link is clicked (mobile)
    $('.navbar-collapse a').on('click', function(){
        $(".navbar-collapse").collapse('hide');
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
        $('#authEnglishLink').hide();
        $('#addCommentLink').hide();
        $('#addEnglishLink').hide();
        $('#addFormOrthLink').hide();
        Cookies.remove('userEmail');
        $.ajax('ajax.php?action=logout');
        gapi.auth2.getAuthInstance().disconnect();
        securityClearance = 0;
        console.log("Security clearance: " + securityClearance);
        console.log('User signed out.');  //debug code only
    });
});







function chooseSelectedTerm(item, lang) {
    updateUserSearchDB(item.value, 0, lang);           //records the search as successful in the server DB
    $('#englishSearchField').val("");
    $('#gaelicSearchField').val("");
    $('#gaelicEquivalentsList').empty();
    if (lang=='en') {
        var gds = item.item.targets;
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
/*
$('#backbutton').on("click", function() {
    goBack();
    return false;
});
*/
