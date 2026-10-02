// Contact page map. Port of the original page/contact/script.js (Mapbox GL).
// The access token is NOT in this file: the page renders it from the MAPBOX_TOKEN environment variable into
// #map[data-mapbox-token]. Without a token (or without WebGL) the map panel stays empty and the page still works.
(function () {
  'use strict';

  var mapElement = document.getElementById('map');
  if (!mapElement || typeof mapboxgl === 'undefined' || !mapElement.dataset.mapboxToken) return;

  try {
    mapboxgl.accessToken = mapElement.dataset.mapboxToken;

    var center = [14.426444, 50.086136];
    var map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/dark-v10?optimize=true',
      center: center,
      zoom: 13
    });

    var popup = new mapboxgl.Popup({ offset: 25 });
    var title = document.createElement('h3');
    title.textContent = 'Find us here!';
    var address = document.createElement('h4');
    address.append('ΛΞV | Digital studio.', document.createElement('br'), 'Korunní 810, Praha 10');
    var box = document.createElement('div');
    box.append(title, address);
    popup.setDOMContent(box);
    new mapboxgl.Marker().setLngLat(center).setPopup(popup).addTo(map);

    // The location bar of the original page (links without behaviour there): fly to the city.
    var cities = {
      Prague: [14.426444, 50.086136],
      Dubai: [55.2708, 25.2048],
      Kiev: [30.5234, 50.4501],
      London: [-0.1276, 51.5072]
    };
    document.getElementById('location-bar').addEventListener('click', function (event) {
      var link = event.target.closest('.location');
      var target = link && cities[link.getAttribute('data-location')];
      if (target) map.flyTo({ center: target, zoom: target === center ? 13 : 11 });
    });
  } catch (error) {
    if (window.console) console.warn('[contact] map disabled:', error && error.message);
  }
})();
