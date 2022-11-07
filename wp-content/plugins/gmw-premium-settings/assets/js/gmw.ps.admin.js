jQuery( document ).ready( function($) {

	if ( $( '.gmw-edit-form .include-exclude-taxonomy-terms-wrapper' ).length ) {

		/****** Include Exclude taxonomies ******/
		function gmw_toggle_tax_terms() {

			$( '.gmw-edit-form .include-exclude-taxonomy-terms-wrapper' ).each( function() {

				var mainWrap = $( this );

				mainWrap.find( '.gmw-taxonomies-picker-wrapper' ).slideUp( 'fast', function() {
					$( this ).find( '.single-taxonomy-picker' ).hide();
					mainWrap.closest( 'tbody' ).find( '.setting-post_types option' ).each( function(e) {
						if ( $( this ).is( ':selected' ) ) {
							mainWrap.find( '.gmw-taxonomies-picker-wrapper.' + $( this ).val() ).slideDown( 'fast' );
						}
					});
				})
			});
		}

		gmw_toggle_tax_terms();

		$( '.gmw-edit-form tr#page_load_results-post_types-tr, .gmw-edit-form tr#search_form-post_types-tr' ).find( '.setting-post_types' ).change( function() {
			gmw_toggle_tax_terms();
		});
		
		$( '.gmw-edit-form .include-exclude-taxonomy-terms-wrapper .header' ).on( 'click', function() {
			
			var clickedElement = $( this ).closest( '.gmw-taxonomies-picker-wrapper' ).find( '.single-taxonomy-picker' );

			$( '.gmw-edit-form .include-exclude-taxonomy-terms-wrapper' ).find( '.single-taxonomy-picker' ).slideUp( 'fast' );

			if ( ! clickedElement.is( ':visible') ) {
				clickedElement.slideDown( 'fast' );
			}
		});

	}

	// This part now exists in GEO my WP plugin and should be removed from here in the future.
	// It is still here in case that a previous version of GEO my WP which doesn't incude this script is being used.
	jQuery( '#search_form-taxonomies-tr .taxonomy-wrapper .taxonomy-header' ).off( 'click' ).on( 'click', function() {

		var taxonomy = jQuery( this ).closest( '.taxonomy-wrapper' );
		var taxId    = taxonomy.closest( 'div' ).attr( 'id' );

		// hide all taxonomies
		jQuery( '#search_form-taxonomies-tr .taxonomy-wrapper .taxonomy-settings:not( #' + taxId + ')' ).slideUp( 'fast' );
		// show selected taxonomies group
		taxonomy.find( '.taxonomy-settings:hidden' ).slideDown();
	});

	jQuery( '#search_form-taxonomies-tr' ).find( 'select.taxonomy-usage' ).change( function() {
		jQuery( this ).closest( '.taxonomy-settings-table-wrapper' ).attr( 'data-type', jQuery( this ).val() );
	});

	if ( jQuery().sortable ) {
		// sortable taxonomies
		jQuery( "#taxonomies-wrapper" ).sortable({
			items:'.taxonomy-wrapper',
	        opacity: 0.5,
	        cursor: 'move',
	        axis: 'y',
	        handle:'.gmw-taxonomy-sort-handle'
	    });

	    // sortable custom fields
		jQuery( "#custom-fields-holder" ).sortable({
			items:'.single-custom-field-wrapper',
	        opacity: 0.5,
	        cursor: 'move',
	        axis: 'y',
	        handle:'.custom-field-handle'
	    });
	}

	/****** Include Exclude taxonomies ******/

	// info-window templates switcher
	var savedType = jQuery( 'select.setting-iw_type' ).val();

	jQuery( '.gmw-info-window-template.' + savedType ).slideDown();
		
	jQuery( 'select.setting-iw_type' ).on( 'change', function() {		
		jQuery( '.gmw-info-window-template' ).slideUp( 'fast' );
		jQuery( '.gmw-info-window-template.' + jQuery( this ).val() ).slideDown();
	}); 

	if ( jQuery( '.setting-ajax_enabled' ).is( ':checked' ) ) {
		jQuery( '#info-window-templates-wrapper' ).show();
	} else {
		jQuery( '#info-window-no-templates-message' ).show();
	}

	jQuery( '.setting-ajax_enabled' ).change( function() {
		jQuery( '#info-window-templates-wrapper' ).slideToggle()
		jQuery( '#info-window-no-templates-message' ).slideToggle();
	});

	$( '.new-field-button' ).click( function(e) {

		// get the selected field name
		var fieldName = $( '#custom-field-picker' ).val();

		// get all existing field. 
		var exsitingFields = $( '.single-custom-field-wrapper:not( .original-field )' ).map( function() {
		    return $( this ).data( 'key' );
		} ).get();

		// make sure field not already added to the form.
		if ( jQuery.inArray( fieldName, exsitingFields ) == -1 ) {

			// clone new field element
			var newField = $( '.single-custom-field-wrapper.original-field' ).clone();

			// append some data to the new field
			newField.attr( 'data-key', fieldName ).find( 'div.name input' ).val( fieldName );
			
			// modify the name attribute of the new field based on the field name
			newField.appendTo( '#custom-fields-holder' ).find( 'input[type="text"]:not(.chosen-search-input), select, input[type="checkbox"]' ).each( function() {

				//if ( jQuery( this ).is( 'select' ) ) {
				//	$( this ).chosen( { width: '100%' });
				//}
				var newName = $( this ).attr( 'name' ).replace( '%%field_name%%', fieldName );
				$( this ).attr( 'name', newName );
				$( this ).prop( 'disabled', false )
			});

			// show new field
			newField.slideDown().removeClass( 'original-field' );

		} else {

			alert( 'This field already exist in the form.' );
		}
	});

	$( document ).on( 'change', '.single-custom-field-wrapper .type-select', function() {
		
		var dateField = $( this ).closest( 'div' ).find( '.date-type-select' );

		if ( $( this ).val() == 'DATE' ) {
			dateField.slideDown( 'fast' );
		} else {
			dateField.slideUp( 'fast' )
		}
	});

	$( document ).on( 'change', '.single-custom-field-wrapper .compration-select', function() {
		
		var value = $( this ).val();
		var labelField = $( this ).closest( '.single-custom-field-wrapper' ).find( 'div.label span:last-child' );

		if ( value == 'BETWEEN' || value == 'NOT BETWEEN' ) {
			$( this ).closest( '.single-custom-field-wrapper' ).find( 'div.label span:last-child' ).slideDown( 'fast' );
			$( this ).closest( '.single-custom-field-wrapper' ).find( 'div.placeholder span:last-child' ).slideDown( 'fast' );
		} else {
			$( this ).closest( '.single-custom-field-wrapper' ).find( 'div.label span:last-child' ).slideUp( 'fast' );
			$( this ).closest( '.single-custom-field-wrapper' ).find( 'div.placeholder span:last-child' ).slideUp( 'fast' );
		}
	})
	
	$( document ).on( 'click', '.custom-field-delete', function() {
		$( this ).closest( '.single-custom-field-wrapper' ).slideUp( '500', function() {
			$( this ).remove();
		});
	});

	// set address field usage on page load
	if ( $( '#gmw-af-wrapper input[type="radio"]:checked' ).val() == 'multiple' ) {
		
		$( '.gmw-af-field-settings-wrapper.single' ).slideUp( 'fast', function() {
			$( '.gmw-af-field-settings-wrapper.multiple' ).slideDown();
		} );
	}
	
	// toggle address fields usage
	$( '#address-fields-usage' ).change( function() {
			
		if ( $( this ).val() == 'single' ) {

			$( '.gmw-address-fields-settings.multiple' ).slideUp( 'fast', function() {
				$( '.gmw-address-fields-settings.single' ).slideDown();
			} );

		} else {
			$( '.gmw-address-fields-settings.single' ).slideUp( 'fast', function() {
				$( '.gmw-address-fields-settings.multiple' ).slideDown();
			} );
		}
	});

	$( '.gmw-address-fields-settings.multiple' ).find( '.single-option.usage select' ).change( function() {

		var value = $( this ).val();
		var row   = $( this ).closest( '.single-address-field-raw' );
	
		if ( $( this ).val() == 'disabled' ) {
			
			row.find( '.single-option.usage' ).addClass( 'disabled' );
			row.find( '.address-fields-settings' ).slideUp( 'fast' );
		
		} else {

			row.find( '.single-option.usage' ).removeClass( 'disabled' );
			row.find( '.address-fields-settings' ).slideUp( 'fast', function() {
				row.find( '.address-fields-settings.' + value ).slideDown();
			});
		}
	});

	$( '.gmw-st-btns' ).change( function() {

		var thisHide = $(this).closest('.gmw-single-taxonomy').find('.gmw-st-settings');
		if ( $(this).val() == 'na') {
			if ( thisHide.is(':visible') ) thisHide.slideToggle();
		} else if ( thisHide.is(':visible') ) { } else { thisHide.slideToggle(); }

		$( this ).closest( '.taxonomy-settings-table-wrapper' ).attr( 'data-type', $( this ).val() );
	});
		
	$( '.gmw-keywords-field-usage' ).change( function() {
		
		var clicked = $( this ).val();
		
		if ( clicked == '' ) {
			$( '.keywords-options-wrapper' ).slideUp( 'fast' );
		} else {
			$( '.keywords-options-wrapper' ).slideDown( 'fast' );
		}
	});

	$( '#search_form-post_types-tr .post-types-tax' ).change( function() {
		$( '#search_form-taxonomies-tr .taxonomy-wrapper .taxonomy-settings' ).slideUp( 'fast' );
	});

	/****** bp groups *****/

	var bpGroupsWrap = jQuery( '#gmw-edit-form-page .bp-groups-settings-wrapper' );

    if ( jQuery( '#gmw-edit-form-page #bp-groups-usage' ).val() != 'pre_defined' ) {
        bpGroupsWrap.find( '.single-option.label, .single-option.options-all' ).slideDown( 'fase' );
    }

   	jQuery( '#gmw-edit-form-page #bp-groups-usage' ).change( function() {

   		if ( jQuery( this ).val() == 'pre_defined' ) {
   			bpGroupsWrap.find( '.single-option.label, .single-option.options-all' ).slideUp( 'fase' );
   		} else {
        	bpGroupsWrap.find( '.single-option.label, .single-option.options-all' ).slideDown( 'fase' );
       	}                
    } );
    /***** bp groups *****/
});
