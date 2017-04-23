var auth2;
var minChars = 3;

$('#englishSearchField').on({
    keyup: function (e) {
        var search = $('#englishSearchField').val();
        if (e.which === 13 && search.length >= minChars) {  //handle the submission of a query with return key
            $.getJSON("php/leacag.php?action=getEnglish&term=" + search, function (data) {
                if (data.length === 0 || data[0].value !== search.toLowerCase()) {    //no matching results
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
        $('#noResults').hide();
    }
});

$('#gaelicSearchField').on({
    keyup: function (e) {
        var search = $('#gaelicSearchField').val();
        if (e.which === 13 && search.length >= minChars) {  //handle the submission of a query with return key
            $.getJSON("php/leacag.php?action=getGaelic&term=" + search, function (data) {
                if (data.length === 0 || data[0].value !== search.toLowerCase()) {    //no matching results
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
        $('#noResults').hide();
    }
});

//the event handler has to be attached to the document in order to register the dynamically added elements
$(document).on('click', '.leacag-link', function() {
	var id = $(this).attr('id');
	updateContent(id);
	return false;
});

$('#randomEntry').on("click", function() {
    $('#noResults').hide();
	$('#englishSearchField').val("");
	$('#gaelicSearchField').val("");
	$('#gaelicEquivalentsList').html("");
	$.getJSON("php/leacag.php?action=getRandom", function(data) {
		var randomid = data.randomEntry.id; // THIS DOESN'T WORK
		entryhistory.push(randomid);
        //entryhistory=[randomid];
        updateContent(randomid);
        return false;
	})
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
            $('#content-div-entry').empty();
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

$('#enToGdToggle').on("click", function() {
	$('#suggestions').hide();
	$('#englishSearchField').val("");
	$('#gaelicEquivalentsList').empty();
	$('#mainContent').empty();
	$("#englishSearchForm").hide();
	$(this).hide();
	$("#gaelicSearchForm").show();
	$("#gdToEnToggle").show();
	$('#gaelicSearchField').focus();
	return false;
});

$('#gdToEnToggle').on("click", function() {
	$('#suggestions').hide();
	$('#gaelicSearchField').val("");
	$("#englishSearchForm").show();
	$("#gaelicSearchForm").hide();
	$(this).hide();
	$("#enToGdToggle").show();
	$('#mainContent').empty();
	$('#englishSearchField').focus();
	return false;
});

$('#backbutton').on("click", function() {
	goBack();
	return false;
});

function updateContent(id) {
	// update the content panel when a new lexical entry is selected
	$('#content-div-entry').load("../lexicopia/lexicopia-web/code/php/generatelexicalentry.php?lang=" + lang + "&id=" + id);
	if (entryhistory.length > 1) {
		//document.getElementById("backbutton").style.display = 'block';
		$('#backbutton').show();
	}
	else {
		//document.getElementById("backbutton").style.display = 'none';
		$('#backbutton').hide();
	}
}

function showEnglish() {
    $('.en-span').show();
    $('#en-plus').hide();
    $('#en-minus').show();
}

function hideEnglish() {
    $('.en-span').hide();
    $('#en-plus').show();
    $('#en-minus').hide();
}

/*
 * Add the user email and search term to the database
 */
function updateUserSearchDB(searchTerm, failed, language) {
	var userProfile = getUser();
    $.ajax({
      method: "GET",
      url: 'ajax.php?action=logSearchTerm&searchTerm='+searchTerm+'&failed='+failed+'&language='+language+'&id='+userProfile.getId()+'&email='+userProfile.getEmail()
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

