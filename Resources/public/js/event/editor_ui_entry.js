function getEditorRowType() {
    if ($.inArray(selectedMarker.eveMappObjectType, ["MarketStall", "Toilet", "FoodStand"]) != -1) {
        return 'Prices';
    }

    if ($.inArray(selectedMarker.eveMappObjectType, ["Stage"]) != -1) {
        return 'Timetable';
    }

    return 'none';
}

function createPriceEditor() {
    $('#overlayCloser').click(function () {
        saveRowPriceEditor();
    });
    $('#btAddEntry').click(function () {
        addNewRowPriceEditor(selectedMarker.eveMappObjectInfo.entries.length);
        selectedMarker.eveMappObjectInfo.entries[selectedMarker.eveMappObjectInfo.entries.length] = {
            id: -1,
            name: "",
            price: 0
        }
    });

}

function createLineupEditor() {
    $('#overlayCloser').click(function () {
        saveRowLineupEdtitor();
    });
    $('#btAddEntry').click(function () {
        addNewRowLineUpEditor(selectedMarker.eveMappObjectInfo.entries.length);
        selectedMarker.eveMappObjectInfo.entries[selectedMarker.eveMappObjectInfo.entries.length] = {
            id: -1,
            performer: "",
            startTime: {
                date: "Start Time"
            },
            endTime: {
                date: "End Time"
            }
        }
    });
}

function addNewRowPriceEditor(index) {
    $.post("/editor/template/entry/price", {
        index: index
    }, function (data) {
        $('#mapObjectEditorPrices').append(data);
        setChangeListenersPriceEditor();
    });
}

function addNewRowLineUpEditor(index) {

    $.post("/editor/template/entry/time", {
        index: index
    }, function (data) {
        $('#mapObjectEditorPrices').append(data);
        setChangeListenersTimeEditor();
    });
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
        format: 'Y-m-d H:i'
    });

    $('.entryInputEndTime').change(function () {
        selectedMarker.eveMappObjectInfo.entries[$(this).data('entry')].endTime.date = $(this).val();
    });
    $('.entryInputEndTime').datetimepicker({
        format: 'Y-m-d H:i'
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

    getObjectInformation();
    setOverlay('content', false);
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

    getObjectInformation();
    setOverlay('content', false);
}

function getObjectInformation() {
    $.ajax({
        url: "/editor/object_info/show/" + selectedMarker.eveMappTableId
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
    $.ajax("/editor/object_editor/" + getEditorRowType() + "/" + selectedMarker.eveMappTableId)
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
                case 'Prices':
                    createPriceEditor();
                    break;
                case 'Timetable':
                    createLineupEditor();
                    break;
            }
        });
}