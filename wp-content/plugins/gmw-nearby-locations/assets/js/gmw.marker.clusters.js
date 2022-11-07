/**
 * The minified version of the file loads in 
 *
 * class-gmw-nearby-locations.php file. 
 * 
 * @param  {[type]} ) {		if        ( gmwVars.mapsProvider [description]
 * @return {[type]}   [description]
 */
jQuery( document ).ready( function() {
	// Add Google Marker Cluster
	if ( gmwVars.mapsProvider === 'google_maps' ) {

		GMW.add_filter( 'gmw_map_init', function( map ) {

			if ( typeof( map.markerGroupingTypes.markers_clusterer ) !== 'undefined' ) {
				return map;
			}

			map.markerGroupingTypes.markers_clusterer = {
				'init' : function( mapObject ) {
					// initialize markers clusterer if needed and if exists
					if ( typeof MarkerClusterer === 'function' ) {
						// init new clusters object
						mapObject.clusters = new MarkerClusterer( 
							mapObject.map, 
							mapObject.markers,
							{
								imagePath    : mapObject.clustersPath,
								clusterClass : mapObject.prefix + '-cluster cluster',
								maxZoom 	 : 15 
							}
						);
					} 
				},

				'clear' : function( mapObject ) {
					// initialize markers clusterer if needed and if exists
					if ( typeof MarkerClusterer === 'function' ) {
						// remove existing clusters
						if ( mapObject.clusters != false ) {		
							mapObject.clusters.clearMarkers();
						}
					} 
				},

				'addMarker' : function( marker, mapObject ) {
					mapObject.clusters.addMarker( marker );	
				},

				'markerClick' : function( marker, mapObject ) {
					google.maps.event.addListener( marker, 'click', function() {
						mapObject.markerClick( this );
					});	
				}
			};
			return map;
		} );
	}

	// Add Google Marker Cluster
	if ( gmwVars.mapsProvider === 'leaflet' ) { 

		GMW.add_filter( 'gmw_map_init', function( map ) {

			if ( typeof( map.markerGroupingTypes.markers_clusterer ) !== 'undefined' ) {
				return map;
			}
			map.markerGroupingTypes.markers_clusterer = {

				// initiate clusters.
				'init' : function( mapObject ) {
					// initialize markers clusterer if needed and if exists
					if ( typeof L.markerClusterGroup === 'function' ) {
						mapObject.clusters = L.markerClusterGroup( mapObject.options.markerClustersOptions );
						mapObject.map.addLayer( mapObject.clusters );
					} 
				},

				// Clear clusters.
				'clear' : function( mapObject ) {
					if ( typeof L.markerClusterGroup === 'function' ) {
						// remove existing clusters
						if ( mapObject.clusters != false ) {		
							mapObject.clusters.clearLayers();
						}
					} 
				},

				// Add marker to the cluster.
				'addMarker' : function( marker, mapObject ) {
					mapObject.clusters.addLayer( marker );	
				},

				// marker click action to open info-window.
				'markerClick' : function( marker, mapObject ) {
					marker.on( 'click', function() {
						mapObject.markerClick( this );
					});
				}
			};
			return map;
		} );
	}
} );
