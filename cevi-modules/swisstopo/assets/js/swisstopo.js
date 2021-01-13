/* global ga, ol, drupalSettings */
(function ($) {
    const $maps = $('.swisstopo-map');

    $.each($maps, function (index, map) {
        const $map = $(map);
        const id = `swisstopo-map-${index}`;
        $map.attr('id', id);

        let x = $map.data('swisstopo-x').toString();
        let y = $map.data('swisstopo-y').toString();
        const zoom = $map.data('swisstopo-zoom');
        const markerUrl = $map.data('swisstopo-marker');

        console.log(markerUrl);

        // Swisstopo does not support it's own 7-digit system...
        if (x.length > 6) {
            x = x.substr(1);
        }

        // Swisstopo does not support it's own 7-digit system...
        if (y.length > 6) {
            y = y.substr(1);
        }

        y = parseInt(y);
        x = parseInt(x);

        const gaMap = new ga.Map({
            target: id,
            view: new ol.View({
                resolution: zoom,
                center: [x, y]
            })
        });

        const layer = ga.layer.create('ch.swisstopo.pixelkarte-farbe');
        const position = [x, y];

        gaMap.addLayer(layer);

        // Create the layer with the icon
        const vectorLayer = new ol.layer.Vector({
            source: new ol.source.Vector({
                features: [new ol.Feature({
                    geometry: new ol.geom.Point(position)
                })]
            }),
            style: new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.575, 1],
                    anchorXUnits: 'fraction',
                    anchorYUnits: 'fraction',
                    src: markerUrl
                })
            })
        });

        gaMap.addLayer(vectorLayer);
    });
}(jQuery));