function getEditorRowType() {
    if ($.inArray(selectedMarker.eveMappObjectType, ["MarketStall", "Toilet", "FoodStand"]) != -1) {
        return 'prices';
    }

    if ($.inArray(selectedMarker.eveMappObjectType, ["Stage"]) != -1) {
        return 'times';
    }

    return 'none';
}

function createPriceEditor() {
    $.each(selectedMarker.eveMappObjectInfo.entries, addNewRowPriceEditor);
    $('#overlayCloser').click(function () {
        $('#loading_image').show();
        getObjectInformation();
        setOverlay('content', false);
        saveRowPriceEditor();
    });
    $('#btAddEntry').click(function () {
        var newIndex = selectedMarker.eveMappObjectInfo.entries.length;
        selectedMarker.eveMappObjectInfo.entries[newIndex] = {
            id: -1,
            name: "",
            price: 0.01
        };

        addNewRowPriceEditor(newIndex, selectedMarker.eveMappObjectInfo.entries[newIndex]);
    });

}

function createLineupEditor() {
    $.each(selectedMarker.eveMappObjectInfo.entries, addNewRowLineUpEditor);
    $('#overlayCloser').click(function () {
        saveRowLineupEdtitor();
        getObjectInformation();
        setOverlay('content', false);
    });
    $('#btAddEntry').click(function () {
        var newIndex = selectedMarker.eveMappObjectInfo.entries.length;

        selectedMarker.eveMappObjectInfo.entries[newIndex] = {
            id: -1,
            performer: "",
            startTime: {
                date: "Start Time"
            },
            endTime: {
                date: "End Time"
            }
        };

        addNewRowLineUpEditor(newIndex, selectedMarker.eveMappObjectInfo.entries[newIndex]);
    });
}

function addNewRowPriceEditor(index, value) {
    if (value != null) {
        var div = $('#mapObjectEditorPrices');
        var inputString = "<input  class='entryInputName' type='text' data-entry='" + index + "' value='" + value.name + "'/>" +
            "<input type='number' class='entryInputPrice' data-entry='" + index + "' value='" + value.price + "'>";
        div.append(inputString);

        setChangeListenersPriceEditor();
    }
}

function addNewRowLineUpEditor(index, value) {
    if (value != null) {
        var div = $('#mapObjectEditorPrices');
        // Ugly HTML in JS, might fix this later on
        var inputString = "<input class='entryInputPerformer' type='text' data-entry='" + index + "' value='" + value.performer + "' />" +
            "<input class='entryInputStartTime' type='text' data-entry='" + index + "' value='" + value.startTime.date + "' />" +
            "<input class='entryInputEndTime' type='text' data-entry='" + index + "' value='" + value.endTime.date + "' />";

        div.append(inputString);

        setChangeListenersTimeEditor();

    }
}

function setChangeListenersPriceEditor() {
    $('.entryInputName').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].name = $(this).val();
    });

    $('.entryInputPrice').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].price = $(this).val();
    });
}

/**
 *  Need date validation here!
 *
 */
function setChangeListenersTimeEditor() {
    $('.entryInputPerformer').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].performer = $(this).val();
    });

    $('.entryInputStartTime').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].startTime.date = $(this).val();
    });
    $('.entryInputStartTime').datetimepicker({
        format: 'Y-m-d H:i:s'
    });

    $('.entryInputEndTime').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].endTime.date = $(this).val();
    });
    $('.entryInputEndTime').datetimepicker({
        format: 'Y-m-d H:i:s'
    });

}

function saveRowPriceEditor() {
    // Save the object (description)
    saveObject(selectedMarker);

    // And its entries
    $.each(selectedMarker.eveMappObjectInfo.entries, function (index, value) {
        if (value != null) {
            if (value.name == "" && value.id != -1) {
                $.post("/editor/entry/delete/price", {entry: JSON.stringify(value)})
                    .done(function (data) {
                        if (data == 'true') {
                            selectedMarker.eveMappObjectInfo.entries[index] = null;
                        }
                    }).error(function (xhr) {
                        setOverlay('error', true, xhr.responseText);
                    });
            } else if (value.name == "" && value.id == -1) {
                selectedMarker.eveMappObjectInfo.entries[index] = null;
            } else if (value.id == -1) {
                value.object_id = selectedMarker.eveMappTableId;
                console.log(value);
                $.post("/editor/entry/save/price", {entry: JSON.stringify(value)})
                    .done(function (data) {
                        value.id = data;
                    }).error(function (xhr) {
                        setOverlay('error', true, xhr.responseText);
                    });
            }

        }
    });
}

function saveRowLineupEdtitor() {
    // Save the object (description)
    saveObject(selectedMarker);

    // And its entries
    $.each(selectedMarker.eveMappObjectInfo.entries, function (index, value) {
        if (value != null) {
            if (value.performer == "" && value.id != -1) {
                $.post("/editor/entry/delete/time", {entry: JSON.stringify(value)})
                    .done(function (data) {
                        if (data != 'false') {
                            selectedMarker.eveMappObjectInfo.entries[index] = null;
                        }
                    }).error(function (xhr) {
                        setOverlay('error', true, xhr.responseText);
                    });
            } else if (value.performer == "" && value.id == -1) {
                selectedMarker.eveMappObjectInfo.entries[index] = null;
            } else if (value.id == -1) {
                value.object_id = selectedMarker.eveMappTableId;
                $.post("/editor/entry/save/time", {entry: JSON.stringify(value)})
                    .done(function (data) {
                        value.id = data;
                    }).error(function (xhr) {
                        setOverlay('error', true, xhr.responseText);
                    });
            }

        }
    });
}

function getObjectInformation() {
    $.ajax({
        type: "POST",
        url: "http://web.insidion.com/event/map/edit/request/object_info/show",
        data: {
            object_type: selectedMarker.eveMappObjectType,
            object_info: selectedMarker.eveMappObjectInfo
        }
    }).done(function (data) {
        $('#accordion_information').html(data);
        $('#editMapObjectInfo').click(function () {
            openMapObjectEditor();
        });

    }).error(function (xhr) {
        setOverlay('error', true, xhr.responseText);
    })
}

/**
 *
 */

function openMapObjectEditor() {
    $.ajax("http://web.insidion.com/event/map/edit/request/map_object_editor")
        .done(function (data) {
            setOverlay('content', true, data);
            $('#loading_image').hide();


            // onchange listener for description
            var desc = $('#mapObjectEditorDescription');
            desc.val(selectedMarker.eveMappObjectInfo.desc);
            desc.change(function () {
                selectedMarker.eveMappObjectInfo.desc = $(this).val();
            });

            // Depending on info type create the editor and its listeners
            switch (getEditorRowType()) {
                case 'prices':
                    createPriceEditor();
                    break;
                case 'times':
                    createLineupEditor();
                    break;
            }
        });
}