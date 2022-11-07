/* global FusionPageBuilderEvents, fusionBuilderText, FusionPageBuilderApp, fusionBuilderConfig, ajaxurl */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Studio Library View
		FusionPageBuilder.StudioLibraryView = FusionPageBuilder.BaseLibraryView.extend( {

			/**
			 * Attach events to elements which are added dynamically (needed because we dont set view.el).
			 */
			attachDynamicEvents: function() {
				jQuery( '#fusion-builder-layouts' ).on( 'mouseenter', '.fusion-studio-load', this.maybeFlipPopup );
				jQuery( '#fusion-builder-layouts' ).on( 'mouseleave', '.fusion-studio-load', this.maybeUnFlipPopup );
			},

			/**
			 * Remove events to elements which are added dynamically.
			 */
			removeDynamicEvents: function() {
				jQuery( '#fusion-builder-layouts' ).off( 'mouseenter', '.fusion-studio-load', this.maybeFlipPopup );
				jQuery( '#fusion-builder-layouts' ).off( 'mouseleave', '.fusion-studio-load', this.maybeUnFlipPopup );
			},

			/**
			 * Maybe adds CSS class to expand popup below instead.
			 *
			 * @param {Object} event
			 */
			maybeFlipPopup: function( event ) {
				var $target           = jQuery( event.currentTarget ),
					targetTop         = $target.offset().top,
					$popUp            = $target.find( '.fusion-template-options' ),
					popuHeight        = $target.find( '.fusion-template-options' ).outerHeight(),
					$topBar           = jQuery( '.fusion-tabs-menu' ),
					studioContainer   = $target.closest( '.studio-imports' ),
					wrapperPaddingTop = parseInt( studioContainer.css( 'padding-top' ), 10 );

					if ( popuHeight > targetTop - ( $topBar.offset().top + $topBar.outerHeight() + wrapperPaddingTop ) ) {
						$popUp.addClass( 'fusion-template-options-flip' );
					}
			},

			/**
			 * Maybe remove added CSS class.
			 *
			 * @param {Object} event
			 */
			maybeUnFlipPopup: function( event ) {
				if ( jQuery( event.currentTarget ).find( '.fusion-template-options' ).hasClass( 'fusion-template-options-flip' ) ) {
					jQuery( event.currentTarget ).find( '.fusion-template-options' ).removeClass( 'fusion-template-options-flip' );
				}
			}
		} );
	} );
}( jQuery ) );
