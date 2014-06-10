/**
 * Saves the currently selected object to the database.
 */
function saveObject(object) {
    var latLongPoint = esri.geometry.webMercatorToGeographic(object.geometry);

    $.post("/editor/object/save", {
        object: {
            table_id: object.eveMappTableId,
            object_id: object.eveMappObjectId,
            object_type: object.eveMappObjectType,
            desc: object.eveMappObjectInfo.desc,
            height: resizeByScale(object.symbol.height, 19, map.getZoom()),
            width: resizeByScale(object.symbol.width, 19, map.getZoom()),
            angle: object.symbol.angle,
            image_url: object.symbol.url,
            lat: latLongPoint.x,
            lng: latLongPoint.y
        }
    }).done(function (data) {
        object.eveMappObjectId = data;
    }).error(function (xhr) {
        setOverlay('error', true, xhr.responseText);
    });
}

function deleteObject(graphic) {
    $.post("/editor/object/delete", { id: graphic.eveMappTableId })
        .beforeSend(function() {
            layer.remove(graphic);
        })
        .error(function(xhr) {
            setOverlay('error', true, xhr.responseText);
        })
}