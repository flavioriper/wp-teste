/**
 * yith-scroll.js
 *
 * @author Your Inspiration Themes
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

( function($){
    "use strict";

    if( typeof yith_infs == 'undefined' ) {
        return;
    }

    // set options
    var infinite_scroll = {
        'nextSelector'      : yith_infs.nextSelector,
        'navSelector'       : yith_infs.navSelector,
        'itemSelector'      : yith_infs.itemSelector,
        'contentSelector'   : yith_infs.contentSelector,
        'loader'            : '<img src="' + yith_infs.loader + '">',
        'is_shop'           : yith_infs.shop
    };

    $( yith_infs.contentSelector ).yit_infinitescroll( infinite_scroll );

    $(document).on( 'yith-wcan-ajax-loading', function(){
        // reset
        $( window ).unbind( 'yith_infs_start' );
    });

    $(document).on( 'yith-wcan-ajax-filtered', function(){
        // restart
        $( yith_infs.contentSelector ).yit_infinitescroll( infinite_scroll );
    });

})(jQuery);

