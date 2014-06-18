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

    $.each($('.tooltipAble'), function () {

        $(this).mouseenter(function () {
            tooltipDiv.finish();
            tooltipDiv.html($(this).data('tooltip'));
            tooltipDiv.fadeIn();
        });

        $(this).mouseleave(function () {
            tooltipDiv.finish();
            tooltipDiv.fadeOut();

        })
    })
}

/**
 * Sets or removes the loading overlay with a certain message
 * @param bool True sets overlay, False removes it
 * @param msg Message to show upon setting, only needed when bool == true
 * @param type
 */
function setOverlay(type, bool, msg) {
    var messageDiv;
    var overlay = $("#overlay");
    var content = $("#overlay_content");

    switch (type) {
        case 'loading':
            messageDiv = $("#loading_message");
            content.width('20%');
            $('#loading_image').show();
            break;
        case 'content':
            messageDiv = $("#content_message");
            $('#loading_image').hide();
            content.width('40%');
            break;
        case 'error':
            messageDiv = $("#error_message");
            content.width('100%');
            $('#loading_image').hide();
            break;
    }


    switch (bool) {
        case true:
            messageDiv.html(msg);
            overlay.show();
            messageDiv.show();
            break;
        case false:
            overlay.hide();
            messageDiv.hide();
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
    if (newScale == oldScale) return oValue;
    var scale = 1;
    if (newScale > oldScale) {
        scale = Math.pow(2, newScale - oldScale);
    } else if (oldScale > newScale) {
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

/**
 * Initializes the heat map slider. This slider takes steps of five minutes and requests an heat map image
 * accordingly.
 */
function initHeatMapSlider() {
    var slider = $('#heatMapDate');
    var timeFrame = eventEndDate.getTime() - eventStartDate.getTime();
    var steps = timeFrame / (5 * 60 * 1000);

    slider.slider({
        min: 0,
        max: steps,
        step: 1,
        change: function(event, ui) {
            var currentTime = new Date(eventStartDate.getTime() + ui.value * 5*60*1000);
            $('#heatMapDateShow').val(currentTime);

            var day = currentTime.getDayOfYear() - eventStartDate.getDayOfYear() + 1;
            var hour = currentTime.getHours();
            var minutes = currentTime.getMinutes();

            var eventId = document.URL.substr(document.URL.length -1, 1);

            heatMapMarker.symbol.url = 'http://web.insidion.com/heatmap/get/' + eventId + '/' + oZoom + '/' + day + '/' + hour + '/' + minutes;
            heatMapLayer.redraw();
        }
    })

}