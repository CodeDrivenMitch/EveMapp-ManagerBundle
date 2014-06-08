/*
 *   Javascript File for the EveMapp Web Application
 *   Made by Mitchell Herrijgers
 *
 *   Contains: User Interface functions; Tooltips, Buttons, other code.
 *
 */

/**
 * Calling this functions applies the data-tooltip elements to the Jquery UI tooltip
 */
function createTooltips() {
    var tooltipDiv = $('#infoTooltip');

    $.each($('.tooltipAble'), function() {

        $(this).mouseenter(function() {
            tooltipDiv.finish();
            tooltipDiv.html($(this).data('tooltip'));
            tooltipDiv.fadeIn();
        });

        $(this).mouseleave(function() {
            tooltipDiv.finish();
            tooltipDiv.fadeOut();

        })
    })
}

/**
 * Sets or removes the loading overlay with a certain message
 * @param bool True sets overlay, False removes it
 * @param msg Message to show upon setting, only needed when bool == true
 */
function setOverlay(bool, msg) {
    var overlay = $("#overlay");
    switch (bool) {
        case true:
            $('#message').html(msg);
            overlay.show();
            break;
        case false:
            overlay.hide();
            break;
    }
}

/**
 * Calculates the new size of the object when transitioning zoom levels.
 * @param oValue Old value of the size
 * @param newScale New zoom level
 * @param oldScale Old zoom level
 * @returns {*} New value of the size
 */
function resizeByScale(oValue, newScale, oldScale) {
    if(newScale == oldScale) return oValue;
    var scale = 1;
    if(newScale > oldScale) {
        scale = Math.pow(2, newScale - oldScale);
    } else if(oldScale > newScale) {
        scale = Math.pow(0.5, oldScale - newScale);
    }
    return oValue * scale;
}

/**
 * Finds an available random mapObject Id.
 * Less than 10K objects is preferrable :)
 * @returns {*} Available Id
 */
function getAvailableId() {
    var newId;
    while (newId == null || !$.inArray(newId, takenIds) == -1) {
        newId = Math.floor((Math.random() * 1000000) + 1);
    }
    takenIds[takenIds.length] = newId;
    return newId;
}