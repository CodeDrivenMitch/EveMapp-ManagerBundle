$(document).ready(function() {
    centerPage();


});


function centerPage() {
    var container = $('.pageContainer');
    var heightPage = $(window).height();
    var heightContainer = container.height();

    var newtop = (heightPage - heightContainer) / 2;
    if(newtop < 0) newtop = 0;

    $('#apply').css('min-height', $('#login').css('height'));

    container.css('top', newtop + "px");
}