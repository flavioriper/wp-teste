/**
 * Popup info window.
 * 
 * @param  {[type]} options [description]
 * @return {[type]}         [description]
 */
function gmwPopupInfoWindow( options ) {

	/**
	 * Class tag
	 * @type {[type]}
	 */
	this.class = options.class || 'gmw-info-window popup';

	/**
	 * Content 
	 * @type {[type]}
	 */
	this.content = options.content || '';

	/**
	 * Enable / disable calose button
	 * 
	 * @type {boolean}
	 */
	this.closeButton = options.closeButton || false;

	/**
	 * Close button icon - using GMW font icons
	 * 
	 * @type {[type]}
	 */
	this.closeButtonIcon = options.closeButtonIcon || 'gmw-icon-cancel-circled';

	/**
	 * Draggable window button icon - using GMW font icons
	 * 
	 * @type {[type]}
	 */
	this.draggableButtonIcon = options.draggableButtonIcon || 'gmw-icon-menu';

	/**
	 * enable / disable loader icon
	 * 
	 * @type {[type]}
	 */
	this.loader = options.loader || false;
	
	/**
	 * Draggable window
	 * 
	 * @type {[type]}
	 */
	this.draggable = options.draggable || false;

	/**
	 * Fade in/out duration
	 * 
	 * @type {[type]}
	 */
	this.fadeSpeed = options.fadeSpeed || 'fast';

	/**
	 * Popup element
	 * @type {[type]}
	 */
	this.popupElement = null;
}

/**
 * Open popup window
 * 
 * @return {[type]} [description]
 */
gmwPopupInfoWindow.prototype.open = function() {

	var self = this;

	// generate the popup element
	self.popupElement = '<div style="display:none" id="gmw-popup-info-window" class="' + this.class + '">';

	var buttons = self.getButtons();

	if ( buttons != '' ) {
		self.popupElement += buttons;
	}

	self.popupElement += this.content;

	// loader
	if ( this.loader ) {
		self.popupElement += '<i class="iw-loader gmw-icon-spin-light animate-spin"></i>';
	}

	self.popupElement += '</div>';

	self.popupElement = jQuery( self.popupElement );

	// fade in popup window
	self.popupElement.appendTo( 'body' ).fadeIn( this.fadeSpeed, function() {
		jQuery( this ).find( '.iw-close-button' ).click( function() {
			self.close();
		});
	});
};

/**
 * Generate popup buttons
 * 
 * @return {[type]} [description]
 */
gmwPopupInfoWindow.prototype.getButtons = function() {

	var buttons = '';

	// generate close button
	if ( this.draggable ) {
		buttons += '<span class="gmw-draggable ' + this.draggableButtonIcon + '" data-draggable="gmw-popup-info-window" data-containment="window"></span>';
	}

	// generate close button
	if ( this.closeButton ) {
		buttons += '<span class="iw-close-button ' + this.closeButtonIcon + '"></span>';
	}

	if ( buttons != '' ) {
		buttons ='<div class="buttons-wrapper">' + buttons + '</div>';
	}

	return buttons;
};

/**
 * Set popup window content 
 * 
 * @param {[type]} content [description]
 */
gmwPopupInfoWindow.prototype.setContent = function( content ) {
	jQuery( content ).hide().appendTo( '#gmw-popup-info-window' ).show();
};

/**
 * Close window
 * 
 * @return {[type]} [description]
 */
gmwPopupInfoWindow.prototype.close = function() {
	this.popupElement.fadeOut( this.fadeSpeed, function() {
		jQuery( this ).remove();
	} );
};

/**
 * Markers Spiderfier 
 * 
 * @type {Boolean}
 */
GMW_Map.prototype.spiderfiers = false;

/**
 * Get info-window class name.
 * 
 * @param  {[type]} mapObject [description]
 * @return {[type]}           [description]
 */
GMW_Map.prototype.getIwClassName = function( mapObject ) {

	var self      = mapObject,
		className = 'gmw-info-window ' + self.infoWindow + ' map-' + self.id + ' ' + self.prefix;
		
	if ( self.infoWindowAjax == 1 ) {
		className += ' ajax template-' + self.getIwTemplateName( self.infoWindowTemplate );
	}

	return className;
};

GMW_Map.prototype.iwContentXhr = [];

/**
 * Get info window content via ajax
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.getIwAjaxContent = function( markerCount, marker ) {

	var self = this;

	//Abort if ajax is processing before starting again.
	if ( GMW_Map.prototype.iwContentXhr[self.id] ) {
		GMW_Map.prototype.iwContentXhr[self.id].abort();
	}

	var location_data = self.locations[ markerCount ];

	// remove the locations data from the form object.
	// We do not need to return all the data to the info-window.
	if ( ! GMW.apply_filters( 'gmw_ps_map_ajax_form_data_include_results', false, self ) ) {
		self.gmw_form.results = null;
	}

	// ajax call
	GMW_Map.prototype.iwContentXhr[self.id] = jQuery.ajax( {
		type    : 'post',
		data  	: {
			action   : 'gmw_info_window_init', 
			location : location_data, 
			form_id  : self.id,
			prefix   : self.prefix,
			// form doesn't pass at the moment.
			// instead of passing back the form we pass the form_id
			// and retrive the form using a function.
			form     : self.gmw_form
		},		
		url     : gmwAjaxUrl,
		success	: function( content ) {
									
			jQuery( '.gmw-info-window.map-' + self.id ).not( '.gmw-info-window.user-marker' ).append( content );

			//self.activeInfoWindow.setContent( response );
			if ( self.infoWindow == 'infobubble' ) {
				
				self.infoWindowTypes.infobubble.resize( self );

				// close window
				jQuery( '.gmw-info-window.infobubble .iw-close-button' ).click( function() {
					self.infoWindowTypes.infobubble.close( self );
				});
			}

			jQuery( '.gmw-info-window .iw-loader' ).hide();
		}

	} ).fail( function ( jqXHR, textStatus, error ) {

        if ( window.console && window.console.log ) {

            console.log( textStatus + ': ' + error );

            if ( jqXHR.responseText ) {
                console.log( jqXHR.responseText );
            }
        }

    } ).done( function ( response ) { } );
};

/**
 * Load Google Maps features only if needed.
 * 
 * @param  {[type]} typeof( GMW_Map_Providers.google_maps ) ! [description]
 * @return {[type]}         [description]
 */
if ( typeof( GMW_Map_Providers.google_maps ) !== 'undefined' ) {

	/**
	 * Google Maps Marker Clusters.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.google_maps.markerGroupingTypes.markers_clusterer = {

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

	/**
	 * Google Maps marker Spiderfier.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.google_maps.markerGroupingTypes.markers_spiderfier = {

		'init' : function( mapObject ) {

			// initialize markers clusterer if needed and if exists
		    if ( typeof OverlappingMarkerSpiderfier === 'function' ) {
		    	
				var options = { 
					legWeight  		  : 3,
					markersWontMove   : true, 
					markersWontHide   : true,
					basicFormatEvents : true,
					keepSpiderfied    : true
				};

				options = GMW.apply_filters( 'gmw_map_spiderfire_options', options, self ); // deprecated
				options = GMW.apply_filters( 'gmw_map_spiderfier_options', options, self );

				// initiate markers spiderfier 
				mapObject.spiderfiers = new OverlappingMarkerSpiderfier( 
					mapObject.map, 
					options
				);
			}

	    	mapObject.spiderfiers.addListener( 'click', function( marker ) {
		        mapObject.markerClick( marker );
		    });
		},

		'clear' : function( mapObject ) {
			// nothing todo. Spiderfiers cleared when marker removed from the map.
		},

		'addMarker' : function( marker, mapObject ) {

			// place marker on the map
			marker.setMap( self.map );	

			// add marker into spiderfier object
			mapObject.spiderfiers.addMarker( marker );				
		},

		'markerClick' : function( marker, mapObject ) {}
	};

	/**
	 * Google Maps Info Windows.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.google_maps.infoWindowTypes = {

		standard : {

			open : function( marker, mapObject ) {

				var self      = mapObject,
					className = self.getIwClassName( mapObject ),
					content = '';

				if ( self.infoWindowAjax == 1 ) {
					content = '<i class="iw-loader gmw-icon-spin-light animate-spin"></i>';
				} else {
					content = marker.gmwData.iwContent;
				}

			    // generate new window
				var options = GMW.apply_filters( 'gmw_info_window_options', {
					content  : '<div class="' + className + '">' + content + '</div>',
					maxWidth : 200
				}, self, 'standard' );

				// generate new window
				self.activeInfoWindow = new google.maps.InfoWindow( options );

				// open window
				self.activeInfoWindow.open( 
					self.map, 
					marker 
				);

			    // get content via ajax
			    if ( self.infoWindowAjax == 1 ) {
					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				}
			},

			close : function( mapObject ) {

				if ( mapObject.activeInfoWindow ) {
					mapObject.activeInfoWindow.close();
					mapObject.activeInfoWindow = null;
				}
			}
		},

		infobubble : {

			'open' : function( marker, mapObject ) {

				var infoBubble = this,	
					self 	   = mapObject,
					className  = self.getIwClassName( mapObject ),
			 		content  = '';
				
				// check for ajax
				if ( self.infoWindowAjax == 1 ) {
					content = '<i class="iw-loader gmw-icon-spin-light animate-spin"></i>';
				} else {
					content = marker.gmwData.iwContent;
				}

				var options = {
					content : '<a class="iw-close-button gmw-icon-cancel-circled"></a>' + content,
					shadowStyle : 0,
					padding : 12,
					borderRadius : 5,
					borderWidth  : 0,
					borderColor  : '#ffffff',
					backgroundColor : '#ffffff',
			      	minWidth  : '230',
			      	maxWidth  : '230',
			      	minHeight : 130,
			      	//maxHeight : '150px',
			      	arrowSize : 20,
			      	arrowPosition : 50,
			     	arrowStyle : 0,
			      	disableAutoPan : false,
			      	hideCloseButton : false, 
			      	backgroundClassName : className
			    };

			    // modify the options
			    options = GMW.apply_filters( 'gmw_info_window_options', options, self, 'infobubble' );

			    // init new bubble
				self.activeInfoWindow = new InfoBubble( options );

				// open bubble
			    self.activeInfoWindow.open( self.map, marker );

			    // get content via ajax
			    if ( self.infoWindowAjax == 1 ) {

					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				
				} else {
				
					infoBubble.resize( mapObject );
				}
			},

			'close' : function( mapObject ) {

				if ( mapObject.activeInfoWindow ) {
			
					jQuery( '.gmw-info-window.map-' + mapObject.id ).remove();

					mapObject.activeInfoWindow.close();
					mapObject.activeInfoWindow = null;
				}
			},

			'resize' : function( mapObject ) {

				var infoBubble = this;
				var self = mapObject;

				setTimeout( function() {
					// enable close button
					jQuery( '.gmw-info-window.infobubble .iw-close-button' ).on( 'click', function() {
						infoBubble.close( mapObject );
					} );
				}, 100 );
					
				setTimeout( function() {
							
					var contentHeight = jQuery( '.gmw-info-window-inner.infobubble' ).height();

					// make sure the infobubble is no higher than 200px
					if ( contentHeight > 250 ) {
						contentHeight = 250;
					}

					jQuery( '.gmw-info-window.infobubble' ).parent().height( contentHeight );

					self.activeInfoWindow.setPosition( self.activeMarker.getPosition() );

					jQuery( '.gmw-info-window .iw-loader' ).hide();

				}, 100 );
			}
		},

		infobox : {

			'open' : function( marker, mapObject ) {

				var self 	  = mapObject,
					className = self.getIwClassName( mapObject ),
			 		content   = marker.gmwData.iwContent;

				// if doing ajax
				if ( self.infoWindowAjax == 1 ) {

					// class to support previous versions
					var tname = self.getIwTemplateName( self.infoWindowTemplate );
					className += ' gmw-' + self.prefix + '-ib-' + tname + '-template-holder';

					content = '<i class="iw-loader gmw-icon-spin-light animate-spin"></i>';
				} 

				var options = {
					boxClass		 : className,
					content 		 : '<a class="iw-close-button gmw-icon-cancel-circled"></a>' + content,
					alignBottom 	 : true,
					position    	 : self.map.getCenter(),
					disableAutoPan 	 : false,	
					maxWidth		 : 150,
					pixelOffset		 : new google.maps.Size( -128, -52 ),
					zIndex			 : null,
					closeBoxMargin	 : "12px 4px 2px 2px",
					closeBoxURL 	 : '',
					infoBoxClearance : new google.maps.Size( -70, 60 )
				};

				options = GMW.apply_filters( 'gmw_info_window_options', options, self, 'infobox' );

				// generate new infobox element
				self.activeInfoWindow = new InfoBox( options );
						
				// open infobox	
				self.activeInfoWindow.open( self.map, marker );

				setTimeout( function() {
					jQuery( '.gmw-info-window.infobox .iw-close-button' ).click( function() {
				    	self.closeInfoWindow();
				    } );
				}, 500 );	

				if ( self.infoWindowAjax == 1 ) {
					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				}
			},

			'close' : function( mapObject ) {

				// close info window if open
				if ( mapObject.activeInfoWindow ) {
					mapObject.activeInfoWindow.close();
					mapObject.activeInfoWindow = null;
				}
			},
		},

		popup : {

			'open' : function( marker, mapObject ) {

				var self 	  = mapObject,
					className = self.getIwClassName( mapObject ),
			 		content   = '',
			 		iwLoader  = false,
			 		closeBtn  = false,
			 		draggable = false;

				// if doing ajax
				if ( self.infoWindowAjax == 1 ) {

					// class to support previous versions
					var tname = self.getIwTemplateName( self.infoWindowTemplate );
					className += ' gmw-' + self.prefix + '-iw-' + tname + '-template-holder';
					
					iwLoader  = true;

				} else {

					content   = marker.gmwData.iwContent;
			 		closeBtn  = true;
			 		draggable = true;
				}

				var infoWindowOptions = GMW.apply_filters( 'gmw_info_window_options', {
					class    	 : className,
					content  	 : content,
					loader       : iwLoader,
					closeButton  : closeBtn,
					draggable    : draggable
				}, self, 'popup' );

				// popup init
				self.activeInfoWindow = new gmwPopupInfoWindow( infoWindowOptions );

				// if iw not open already, open it right away.
				// Otherwise, we add a short delay to allow the an open window to first closed.
				if ( ! jQuery( '.gmw-info-window.popup' ).length ) {
					self.activeInfoWindow.open();
				} else {
					setTimeout( function() {
						self.activeInfoWindow.open();
					}, 300 );
				}

				// need a short delay for the close button to appear so we could 
				// trigger the 'close' event on it.
				setTimeout( function() {
					jQuery( document ).on( 'click', '.gmw-info-window.popup .iw-close-button', function() {
				    	self.closeInfoWindow();
				    } );
				}, 500 );	

				// get content.
				if ( self.infoWindowAjax == 1 ) {
					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				}
			},

			'close' : function( mapObject ) {

				if ( mapObject.activeInfoWindow ) {

					// fadeout and remove the popup window
					jQuery( '.gmw-info-window.popup' ).fadeOut( 'fast', function(){ 
						jQuery( '.gmw-info-window.popup' ).remove();
					});

					mapObject.activeInfoWindow = null;
				}
			},
		}
	};
}

/**
 * Load LeafLet features only when needed.
 * 
 * @param  {[type]} typeof( GMW_Map_Providers.leaflet ) ! [description]
 * @return {[type]}         [description]
 */
if ( typeof( GMW_Map_Providers.leaflet ) !== 'undefined' ) {

	/**
	 * LeafLet Marker Clusters.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.leaflet.markerGroupingTypes.markers_clusterer = {

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

	/**
	 * LeafLet Marker Spiderfier.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.leaflet.markerGroupingTypes.markers_spiderfier = {

		// Initiate the spiderfiers.
		'init' : function( mapObject ) {

			// initialize markers clusterer if needed and if exists
		    if ( typeof OverlappingMarkerSpiderfier === 'function' ) {
		    		
		    	mapObject.spiderfiers = new OverlappingMarkerSpiderfier( mapObject.map, mapObject.options.markerSpiderfierOptions );

		    	// Global marker click option for all markers in a spiderfier.
		    	mapObject.spiderfiers.addListener( 'click', function( marker ) {
			        mapObject.openInfoWindow( marker );
			    });
			} 
		},

		// Nothing to clear. Spiderfiers cleared when markers are removed from the map.
		'clear' : function( mapObject ) {},

		// Add marker to spiderfier.
		'addMarker' : function( marker, mapObject ) {
			marker.addTo( mapObject.map ); 
			mapObject.spiderfiers.addMarker( marker );	
		},

		// Click action is inside the init() function above.
		'markerClick' : function( marker, mapObject ) {}
	};

	/**
	 * LeafLet Info Windows.
	 * 
	 * @type {Object}
	 */
	GMW_Map_Providers.leaflet.infoWindowTypes = {

		standard : {

			open : function( marker, mapObject ) {

				var self 	  = mapObject,
					className = self.getIwClassName( mapObject ),
					content;

				if ( self.infoWindowAjax == 1 ) {
					content = '<i class="iw-loader gmw-icon-spin-light animate-spin"></i>';
				} else {
					content = marker.gmwData.iwContent;
				}

				// info window opsions. Can be modified with the filter.
				var infoWindowOptions = GMW.apply_filters( 'gmw_info_window_options', {
					content  : '<div class="' + className + '">' + content + '</div>',
					maxWidth : 200,
					minWidth : 200
				}, self, 'standard' );

				// generate new window
				self.activeInfoWindow = L.popup( infoWindowOptions ).setContent( infoWindowOptions.content );
							
				// Bind popup to marker. We also unbind previous info-window before binding a new one.
				marker.unbindPopup().bindPopup( self.activeInfoWindow ).openPopup();

			    // get content via ajax
			    if ( self.infoWindowAjax == 1 ) {
					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				}
			},

			close : function( mapObject ) {

				if ( mapObject.activeInfoWindow ) {
					mapObject.activeInfoWindow = null;
				}
			}
		},

		popup : {

			'open' : function( marker, mapObject ) {

				var self 	  = mapObject,
					className = self.getIwClassName( mapObject ),
			 		content = '',
			 		iwLoader  = false,
			 		closeBtn  = false,
			 		draggable = false;

				// if doing ajax
				if ( self.infoWindowAjax == 1 ) {

					// class to support previous versions
					var tname = self.getIwTemplateName( self.infoWindowTemplate );
					className += ' gmw-' + self.prefix + '-iw-' + tname + '-template-holder';
					
					iwLoader  = true;

				} else {

					content   = marker.gmwData.iwContent;
			 		closeBtn  = true;
			 		draggable = true;
				}

				var infoWindowOptions = GMW.apply_filters( 'gmw_info_window_options', {
					class    	 : className,
					content  	 : content,
					loader       : iwLoader,
					closeButton  : closeBtn,
					draggable    : draggable
				}, self, 'popup' );

				// popup init
				self.activeInfoWindow = new gmwPopupInfoWindow( infoWindowOptions );

				// if iw not open already, open it right away.
				// Otherwise, we add a short delay to allow the an open window to first closed.
				if ( ! jQuery( '.gmw-info-window.popup' ).length ) {
					self.activeInfoWindow.open();
				} else {
					setTimeout( function() {
						self.activeInfoWindow.open();
					}, 300 );
				}

				// need a short delay for the close button to appear so we could 
				// trigger the 'close' event on it.
				setTimeout( function() {
					jQuery( document ).on( 'click', '.gmw-info-window.popup .iw-close-button', function() {
				    	self.closeInfoWindow();
				    } );
				}, 500 );	

				// get content.
				if ( self.infoWindowAjax == 1 ) {
					self.getIwAjaxContent( marker.gmwData.markerCount, marker );
				}
			},

			'close' : function( mapObject ) {

				if ( mapObject.activeInfoWindow ) {

					// fadeout and remove the popup window
					jQuery( '.gmw-info-window.popup' ).fadeOut( 'fast', function(){ 
						jQuery( '.gmw-info-window.popup' ).remove();
					});

					mapObject.activeInfoWindow = null;
				}
			},
		}
	};
}
