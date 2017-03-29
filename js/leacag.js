var listIndex;		//the position of the cursor within the suggested list
var suggestedTerms;	//the array of suggested result objects loaded via AJAX
var minChars = 3;
var auth2;

$('#englishSearchField').on({
	keyup: function(e) {
		var m = false;
		if (e.which == 38 || e.which == 40 || e.which == 13 || e.which == 27) {
			m = navigateList(e, m, 'en');
			return;
		}
		$('#suggestions').empty(); //clear any previous selections
		listIndex = -1;
		$('.chosen').removeClass('chosen');
		var searchString = $(this).val();
		if (searchString.length >= minChars) {
			//get the list of suggestions from the server
			$.getJSON("php/leacag.php?action=getEnglish&q=" + searchString, function(data) {
				suggestedTerms = data.results;	//save the results for later use
				$.each(data.results, function(k, v) {
					//assemble the suggested items list
					$('#suggestions').append($('<li>' + v.en + '</li>'));
				});
                if ($('#suggestions li').length === 0) {    //there were no results for this search
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                    $("#suggestions").show();
                }
				$('#suggestions li').on('click', function () {
					$(this).addClass('chosen');
					chooseSelectedTerm($(this).html(),'en');
				})
			})
		}
		else {
			$("#suggestions").hide(); // hide when backspace is pressed and just one character in field
		}
    },
	keydown: function(e) {
		if (e.which == 38 || e.which == 40 || e.which == 13) {
			e.preventDefault();
		}
		/*
		    code for alternate 'enter' key behaviour
		    : if a search term is in the suggestion list hitting enter will take the user to the entry
		 */
		if (e.which == 13) {
		    var search = $('#englishSearchField').val();
		    $('#suggestions').each(function () {
                $(this).find('li').each(function () {
                    if (search === $(this).text()) {
                        $(this).addClass('chosen');
                        chooseSelectedTerm($(this), 'en');
                    }
                });
            });
        }
        /*
            //end alternate 'enter' key code
         */
	},
	click: function() {     
		$(this).val("");	//clear the search field for a new query
	}
});

function navigateList(e, m, lang) {
	if (e.which == 38) {    	//Up arrow
		listIndex--;
		if (listIndex < 0) {
			listIndex = 0;
		}
		m = true;
        $('#suggestions li').eq(listIndex).show();
	}
	else if (e.which == 40) {   //Down arrow
        if (listIndex > $('#suggestions li').length - 2) {  //stop at the final item
            listIndex = $('#suggestions li').length - 1;
        } else {
            listIndex++;
            m = true;
            $('#suggestions li.chosen').hide();
        }
	}
	if (m) {
		$('#suggestions li.chosen').removeClass('chosen');
		$('#suggestions li').eq(listIndex).addClass('chosen');
	} else if (e.which == 27) {     //ESC key
		$('#suggestions').hide();
	} else if (e.which == 13) {  	//Enter key
		var n = $('.chosen').index();
		if (n != -1) { // some list item is selected
            var selectedItem = $('option.chosen');
            chooseSelectedTerm(selectedItem.html(),lang);
		}
	}
	return m;
}

//the event handler has to be attached to the document in order to register the dynamically added elements
$(document).on('click', '.leacag-link', function() {
	var id = $(this).attr('id');
	updateContent(id);
	return false;
});

//close the suggestions link on click outside search
$(document).mouseup(function(e) {
	if (!$('#suggestions').is(e.target) && $('#suggestions').has(e.target).length === 0) {
		$('#suggestions').hide();
	}
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

/**
 *
 * @param word: the string value of the selected word
 * @param arrayIdx: the position of the selected word within the file's array
 */
function chooseSelectedTerm(term, lang) {
	updateUserSearchDB(term);           //records the search term in the server DB
	$('#englishSearchField').val("");
    $('#gaelicSearchField').val("");
	$('#suggestions').hide();
	$('#gaelicEquivalentsList').empty();
    if (lang=='en') {
        var gds = suggestedTerms[$('.chosen').index()].gds;
        if (gds.length > 1) {
            $('#gaelicEquivalentsList').append("Faclan Gàidhlig airson <i>" + term + "</i>: ");
            for(var i = 0;i < gds.length;i++) {
                $('#gaelicEquivalentsList').append('<a class="leacag-link" href="#" id="' + gds[i].id + '">' + gds[i].form + '</a>');
				//$('#gaelicEquivalentsList').append('<a class="lexicopia-link" href="#" onclick="entryhistory.push("' + gds[i].id + '");updateContent("' + gds[i].id + '"); return false;">' + gds[i].form + '</a>');
                if (i<(gds.length - 1)) {
                    $('#gaelicEquivalentsList').append(', ');
                }
            }
            $('#content-div-entry').empty();
        }
        else {
			entryhistory.push(gds[0].id);
			//entryhistory=[gds[0].id];
            updateContent(gds[0].id);
        }
    }
	else if (lang=='gd') {
		entryhistory.push(suggestedTerms[$('.chosen').index()].id);
		//entryhistory=[suggestedTerms[$('.chosen').index()].id];
        updateContent(suggestedTerms[$('.chosen').index()].id);
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

$('#gaelicSearchField').on({
	keyup: function (e) {
		var m = false;
		if (e.which == 38 || e.which == 40 || e.which == 13 || e.which == 27) {
			m = navigateList(e, m, 'gd');
			return;
		}
		$('#suggestions').empty(); //clear any previous selections
		listIndex = -1;
		$('.chosen').removeClass('chosen');
		var searchString = $(this).val();
		if (searchString.length >= minChars) {
			//get the list of suggestions from the server
			$.getJSON("php/leacag.php?action=getGaelic&q=" + searchString, function(data) {
				suggestedTerms = data.results;	//save the results for later use
				$.each(data.results, function(k, v) {
					//assemble the suggested items list
					$('#suggestions').append($('<li>' + v.word + '</li>'));
				});
				$("#suggestions").show();
				$('#suggestions li').on('click', function () {
					$(this).addClass('chosen');
					chooseSelectedTerm($(this).html(), 'gd'); // this needs to be done later
				})
			})
		}
		else {
			$("#suggestions").hide(); // hide when backspace is pressed and just one character in field
		}
	},
	keydown: function(e) {
		if (e.which == 38 || e.which == 40 || e.which == 13) {
			e.preventDefault();
		}
        /*
         'enter' key behaviour
         : if a search term is in the suggestion list hitting enter will take the user to the entry
         */
        if (e.which == 13) {
            var search = $('#gaelicSearchField').val();
            $('#suggestions').each(function () {
                $(this).find('li').each(function () {
                    if (search === $(this).text()) {
                        $(this).addClass('chosen');
                        chooseSelectedTerm($(this), 'gd');
                    }
                });
            });
        }
        /*
         //end alternate 'enter' key code
         */
	},
	click: function() {
		$(this).val("");	//clear the search field for a new query
	}
});

function updateContent(id) {
	// update the content panel when a new lexical entry is selected
	$('#content-div-entry').load("../lexicopia/lexicopia-entries/" + lang + "/html/" + id + ".html");
	if (entryhistory.length > 1) {
		document.getElementById("backbutton").style.display = 'block';
	}
	else {
		document.getElementById("backbutton").style.display = 'none';
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
function updateUserSearchDB(searchTerm) {
	var userProfile;
	if (userProfile = getUser()) {
	      $.ajax({
	          method: "GET",
	          url: 'ajax.php?action=logSearchTerm&searchTerm='+searchTerm+'&id='+userProfile.getId()+'&email='+userProfile.getEmail()
	      })
	      .done(function (msg) {
	          console.log("Attempted DB update : " + msg);
	      }); 
	}
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
