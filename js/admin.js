/**
 * Created by stephenbarrett on 28/04/2017.
 */

/*
    Code to run on page load
 */
$(function () {
    $('#searchHistory').hide();
    $('#submissionHistory').hide();
    $('#hideSearchHistory').hide();
    $('#hideSubmissionHistory').hide();
    $('.toggleSearchHistory').on('click', function() {
        $('.displaySearchHistory').toggle();
    });
    $('.toggleSubmissionHistory').on('click', function() {
        $('.displaySubmissionHistory').toggle();
    });

    $('#users').tablesorter();
    $('#searchHistory').tablesorter( {sortList: [[3,1]]} );       //sort by date in reverse order
    $('#submissionHistory').tablesorter( {sortList: [[4,1]]} );    //sort by date in reverse order
})

