function showEnglish() {
    document.getElementById("en-minus").style.display = "inline"; // display the [-eng]
    document.getElementById("en-plus").style.display = "none"; // hide the [+eng]
    document.getElementById("en-text").style.display = "inline"; // display the English text
}

function hideEnglish() {
    document.getElementById("en-minus").style.display = "none"; // hide the [-eng]
    document.getElementById("en-plus").style.display = "inline"; // show the [+eng]
    document.getElementById("en-text").style.display = "none"; // hide the English text
}

function updateContent(id) {
    // update the content panel when a new lexical entry is selected
    $('#content-div-entry').load("../../lexicopia-entries/" + lang + "/html/" + id + ".html");
    if (entryhistory.length > 1) {
        document.getElementById("backbutton").style.display = 'block';
    }
    else {
        document.getElementById("backbutton").style.display = 'none';
    }
}

function goBack() {
    entryhistory.pop();
    var newid = entryhistory.pop();
    entryhistory.push(newid);
    updateContent(newid);
}


