
// Define the zoom function for the zoomable svg
function zoom() {
    svgGroup.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}


// define the zoomListener which calls the zoom function on the "zoom" event constrained within the scaleExtents
var zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on("zoom", zoom);