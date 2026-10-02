/*
var MarkerAnnotation = mapkit.MarkerAnnotation, clickAnnotation;
var work = new mapkit.Coordinate(50.0751, 14.4565);

mapkit.init({
    authorizationCallback: function (done) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "/services/jwt");
        xhr.addEventListener("load", function () {
            done(this.responseText);
        });
        xhr.send();
    }
});

let map = new mapkit.Map("map", {
    mapType: mapkit.Map.MapTypes.Hybrid,
    colorScheme: mapkit.Map.ColorSchemes.Dark
});

// Setting properties after creation:
var workAnnotation = new MarkerAnnotation(work);
workAnnotation.color = "#969696";
workAnnotation.title = "Office";
workAnnotation.subtitle = "ΛΞV | Digital studio.";
workAnnotation.selected = "true";
workAnnotation.glyphText = "ΛΞV";

// Add and show both annotations on the map
map.showItems([workAnnotation]);

// Drop an annotation where a Shift-click is detected:
map.element.addEventListener("click", function (event) {
    if (!event.shiftKey) {
        return;
    }

    if (clickAnnotation) {
        map.removeAnnotation(clickAnnotation);
    }

    var coordinate = map.convertPointOnPageToCoordinate(new DOMPoint(event.pageX, event.pageY));
    clickAnnotation = new MarkerAnnotation(coordinate, {
        title: "Click!",
        color: "#c969e0"
    });
    map.addAnnotation(clickAnnotation);
});
*/



// MapKit 

// The legacy file embedded a Mapbox token. Removed: the token comes from the MAPBOX_TOKEN environment
// variable (rendered by the page as data-mapbox-token) when the Contact page is ported.
mapboxgl.accessToken = (document.querySelector('[data-mapbox-token]') || {}).dataset?.mapboxToken || '';
// Basic map instance
const center = [14.426444, 50.086136];
const map = new mapboxgl.Map({
  container: "map",
  style: "mapbox://styles/mapbox/dark-v10?optimize=true",
  center: center,
  zoom: 13
});
const el = document.createElement("div");
el.className = "marker";
new mapboxgl.Marker()
  .setLngLat(center)
  .setPopup(
    new mapboxgl.Popup({ offset: 25 }).setHTML( // add popups
    `
      <h3>Find us here!</h3>
      <h4>
        ΛΞV | Digital studio.<br/>
        Korunní 810, Praha 10
      </h4>`)
  )
  .addTo(map);

function flyToStore(cordinates) {
  map.flyTo({
    center: cordinates,
    zoom: 15
  });
}

window.addEventListener("load", (event) => {
  const listing = document.getElementById("listing");
  listing.addEventListener("click", (event) => {
    flyToStore(center);
  });
});