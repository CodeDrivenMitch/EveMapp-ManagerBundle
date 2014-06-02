/*
 *   Javascript File for the EveMapp Web Application
 *   Made by Mitchell Herrijgers
 *
 *   All Functions except library functions are found here.
 *
 */

/**
 * Global Variables
 */
var map;
var layer;
var extent;
var selectedTool;
var selectedSubTool;
var selectedMarker;
var lastDeletedMarker;
var takenIds = [];
var deletedIds = [];
var previousZoom;
var originalZoom;

/**
 * Code which needs Dojo/Esri Arcgis SDK
 */

require([
    "esri/map",
    "esri/graphic",
    "esri/symbols/SimpleMarkerSymbol",
    "esri/symbols/PictureMarkerSymbol",
    "esri/layers/GraphicsLayer",
    "esri/geometry/Point",
    "dojo/domReady!"
], function (Map, Graphic, SimpleMarkerSymbol, PictureMarkerSymbol, GraphicsLayer, Point) {

    // Set overlay for loading first chance we get..
    setOverlay(true, "Loading the map..");
    // Hide the sub toolbar for now
    $('#toolSubchoice').hide();




    // Create the map
    map = new Map("map", {
        basemap: "streets   ",
        center: [4.53681008, 51.88391507],
        zoom: 10
    });


    /**
     * Map load function.
     */
    map.on("load", function () {
        loadMapData();
        createTooltips();
        setEventHandlers();
        setOverlay(false);
    });

    /**
     * Retrieves all map info and applies it to the application.
     */
    function loadMapData() {
        layer = new GraphicsLayer();
        $.ajax({
            url: "http://web.insidion.com/event/map/edit/request/bounds",
            dataType: 'json',
            async: false,
            success: function(data) {
                // Set map extent and apply it
                console.log(data);
                previousZoom =  data.zoom;
                originalZoom = data.zoom;
                extent = new esri.geometry.Extent(data.bounds);
                map.setExtent(extent);


                // Add graphics to the map
                $.each(data.objects, function (index, value) {
                    // Make symbol
                    var symbol = new PictureMarkerSymbol(value.image_url,
                        value.width,
                        value.height);
                    symbol.setAngle(value.angle);

                    // Make graphic
                    var geom = new Point(value.lat, value.lng);
                    var graphic = new Graphic(esri.geometry.geographicToWebMercator(geom), symbol);
                    graphic.eveMappObjectId = value.object_id;
                    graphic.eveMappObjectType = value.object_type;

                    // Add the graphic
                    layer.add(graphic);
                    // set zoom

                });
                map.addLayer(layer);


            }
        });
    }


    /**
     * INFO TOOL HANDLERS
     *
     * Handles all UI stuff for the info tool, such as the sliders.
     * @param event
     */
    function infoToolLayerMousedown(event) {
        selectedMarker = event.graphic;

        infoToolInitSliders();
        refreshImages();
        $("#toolSubchoice").show();

    }

    function infoToolInitSliders() {
        $('#accordion').accordion({
                collapsible: true,
                heightStyle: "content"
        });
        $("#slider_height").slider({
            min: 1,
            max: 300,
            value: selectedMarker.symbol.height,
            slide: function (event, ui) {
                selectedMarker.symbol.setHeight(ui.value);
                layer.redraw();
            }
        });
        $("#slider_width").slider({
            min: 1,
            max: 300,
            value: selectedMarker.symbol.width,
            slide: function (event, ui) {
                selectedMarker.symbol.setWidth(ui.value);
                layer.redraw();
            }
        });
        $("#slider_angle").slider({
            min: 1,
            max: 360,
            value: selectedMarker.symbol.angle,
            slide: function (event, ui) {
                selectedMarker.symbol.setAngle(ui.value);
                layer.redraw();
            }
        });
        $('#imageUploadForm').ajaxForm(function() {
            refreshImages();
        });


    }

    /**
     *  DRAG TOOL HANDLERS
     */

    /**
     * On mouse move, drag tool is selected and selectedMarker is set, move the marker to the mouse position.
     * @param event
     */
    function dragToolMapMouseMove(event) {
        if (selectedMarker != null) {
            selectedMarker.setGeometry(event.mapPoint);
        }
    }

    /**
     * On mouse down, if drag tool is selected, release or set the marker clicked on as the selectedMarker
     * @param event
     */
    function dragToolLayerMouseDown(event) {
        if (selectedMarker == null) {
            selectedMarker = event.graphic;
        } else {
            selectedMarker = null;
        }
    }

    /**
     * DELETE TOOL HANDLERS
     *
     *
     * Deletes an object from the map and sets the undo button.
     * @param event
     */

    function deleteToolLayerMouseDown(event) {
        deletedIds[deletedIds.length] = event.graphic.eveMappObjectId;
        layer.remove(event.graphic);
        lastDeletedMarker = event.graphic;
        $("#toolSubchoice").show().html("<div id='undoButton' class='subToolButton'>Undo</div>");
        $("#undoButton").click(function () {
            layer.add(lastDeletedMarker);
            deletedIds[$.inArray(lastDeletedMarker.eveMappObjectId, deletedIds)] = null;
            $("#toolSubchoice").hide().html("");
        });
    }

    /**
     * MAP EVENT HANDLERS
     */

    /**
     * Triggered on the Map Mouse-Down event
     * Creates a new MapObject
     * @param event
     */
    function createToolMapMouseDown(event) {
        //create needed vars
        var imageUrl = selectedSubTool.children().first().attr('src');
        var symbol = new PictureMarkerSymbol(imageUrl, 24, 24);
        var graphic = new Graphic(event.mapPoint, symbol);

        // set values
        graphic.eveMappObjectId = getAvailableId();
        graphic.eveMappObjectType = selectedSubTool.data('objectType');
        symbol.setAngle(0);
        console.log(graphic);

        // add to map
        layer.add(graphic);
    }

    function setSubTool(event) {
        if (selectedSubTool !== null &&
            selectedSubTool.hasClass("activeToolButton")) selectedSubTool.toggleClass("activeToolButton");
        selectedSubTool = $('#' + event.currentTarget.id);
        selectedSubTool.toggleClass("activeToolButton");

    }




    /**
     * Resizes all graphics in the main layer to the new scale.
     */
    function resizeGraphics() {
        // Loop over Graphics of the Layer and scale them
        $.each(layer.graphics, function (index, value) {
            value.symbol.width = resizeByScale(value.symbol.width, map.getZoom(), previousZoom);
            value.symbol.height = resizeByScale(value.symbol.height, map.getZoom(), previousZoom);
        });

        // Also reload sliders
        if(selectedMarker != null) {
            infoToolInitSliders();
        }

        // Redraw the layer and set the previousZoom to the current for the next cycle.
        layer.redraw();
        previousZoom = map.getZoom();

    }


    /**
     * Sequence executed when the reset button is clicked.
     */
    function reset() {
        setOverlay(true, "Resetting the map..");
        map.removeLayer(layer);
        layer = null;
        selectedMarker = null;
        deletedIds = [];
        loadMapData();
        resizeGraphics();
        setOverlay(false);
    }

    /**
     * TOOL BUTTON HANDLERS
     */

    /**
     * Fired when a toolButton is clicked.
     * Selects the toolButton and loads the appropriate subTool choices.
     * @param event
     */
    function toolButtonClickHandler(event) {
        if (selectedTool === event.currentTarget.id) return;

        // Make sure the right tools are selected
        selectedSubTool = null;
        selectedMarker = null;
        $("#" + selectedTool).toggleClass("activeToolButton");
        selectedTool = event.currentTarget.id;
        $("#" + selectedTool).toggleClass("activeToolButton");
        $("#toolSubchoice").hide();
        // Retrieve subTools with an Ajax request
        $.ajax({
            url: "http://web.insidion.com/event/map/edit/request/subtool/" + selectedTool
        }).done(function (data) {
            if (data == "false") {
                data = "";
            }
            $("#toolSubchoice").html(data);
            $(".subToolButton").click(function (event) {
                setSubTool(event);

            });

            if(selectedTool == "createToolButton") {
                $("#toolSubchoice").show();
            }

            // Set the tooltips again
            createTooltips();

        })
    }

    function refreshImages() {
        $.ajax({
            url: "http://web.insidion.com/event/map/edit/request/image/get"
        }).done(function (data) {
            $('#accordion_image_chooser').html(data);
            $('#mapObjectImage_type').val(selectedMarker.eveMappObjectType);

            var count = 0;
            $.each($("div.mapObjectImage"), function() {

               var element = $(this);
                if(element.data('type') == selectedMarker.eveMappObjectType) {
                    element.click(function() {
                        infoToolSetImage(element);
                    });
                    count++;
                } else {
                    element.hide();
                }
            });

            if(count == 0) {
                $('#accordion_image_chooser').html("You have not uploaded any images for this type of object yet!");
            }
        });
    }

    function infoToolSetImage(element) {

        selectedMarker.symbol.url = element.data('url');
        layer.redraw();
    }

    /**
     * SAVE BUTTON HANDLERS
     */

    /**
     * Fired when the save button is clicked.
     * Gathers all data of the map and sends it in an Ajax request to the server.
     * @param event
     */
    function saveButtonClick(event) {
        var allGraphics = layer.graphics;
        var data = {
            deleted: deletedIds,
            objects: []

        };
        $.each(allGraphics, function (index, value) {
            setOverlay(true, "Saving map..");
            var latLongPoint = esri.geometry.webMercatorToGeographic(value.geometry);

            data.objects[data.objects.length] = {
                object_id: value.eveMappObjectId,
                object_type: value.eveMappObjectType,
                height: resizeByScale(value.symbol.height, originalZoom, map.getZoom()),
                width: resizeByScale(value.symbol.width, originalZoom, map.getZoom()),
                angle: value.symbol.angle,
                image_url: value.symbol.url,
                lat: latLongPoint.x,
                lng: latLongPoint.y
            };
        });

        $.post("http://web.insidion.com/event/map/edit/request/save", {saveData: JSON.stringify(data)})
            .done(function (data) {
                if (data != 'true') {
                    setOverlay(true, data);
                }
                setOverlay(false);

            });

    }


    /**
     * CLICK HANDLER SPECIFICATION
     * All events should just specify a further handler, no logic executed.
     */

    function setEventHandlers() {
        /**
         * ZOOM HANDLER SPECIFICATION
         * Note: Using Zoom-end instead of Zoom, or things will get messed up.
         */
        map.on("zoom-end", function () {
            resizeGraphics();
        });

        /**
         * TOOLBAR BUTTONS HANDLER SPECIFICATION
         */

        $('.toolButton').click(function (event) {
            toolButtonClickHandler(event);
        });

        $('#saveButton').click(function (event) {
            saveButtonClick(event);
        });

        $('#resetButton').click(function() {
            reset();
        });

        /**
         * MAP LISTENER SPECIFICATION
         */
        map.on("mouse-down", function (event) {
            switch (selectedTool) {
                case "createToolButton":
                    createToolMapMouseDown(event);
                    break;


            }
        });

        map.on("mouse-move", function (event) {
            switch (selectedTool) {
                case "dragToolButton":
                    dragToolMapMouseMove(event);
                    break;
            }

        });

        /**
         * LAYER LISTENER SPECIFICATION
         */
        layer.on("mouse-down", function (event) {

            switch (selectedTool) {
                case "dragToolButton":
                    dragToolLayerMouseDown(event);
                    break;
                case "deleteToolButton":
                    deleteToolLayerMouseDown(event);
                    break;
                case "infoToolButton":
                    infoToolLayerMousedown(event);
                    break;

            }

        });

    }

});

