(function ($, window, document) {
    "use strict";

    $.fn.yit_infinitescroll = function (options) {

        var opts = $.extend({

                nextSelector   : false,
                navSelector    : false,
                itemSelector   : false,
                contentSelector: false,
                maxPage        : false,
                loader         : false,
                is_shop        : false

            }, options),

            loading  = false,
            finished = false,
            desturl  = $( opts.nextSelector ).attr( 'href' ); // init next url

        // validate options and hide std navigation
        if( $( opts.nextSelector ).length && $( opts.navSelector ).length && $( opts.itemSelector ).length && $( opts.contentSelector ).length ) {
            $( opts.navSelector ).hide();
        }
        else {
            // set finished true
            finished = true;
        }

        // set elem columns ( in shop page )
        var first_elem  = $( opts.contentSelector ).find( opts.itemSelector ).first(),
            columns = first_elem.nextUntil( '.first', opts.itemSelector ).length + 1;

        var main_ajax = function () {

            var last_elem   = $( opts.contentSelector ).find( opts.itemSelector ).last();
            // set loader and loading
            if( opts.loader )
                    $( opts.navSelector ).after( '<div class="yith-infs-loader">' + opts.loader + '</div>' );
            loading = true;
            // decode url to prevent error
            desturl = decodeURIComponent(desturl);
            desturl = desturl.replace(/^(?:\/\/|[^\/]+)*\//, "/");

            // ajax call
            $.ajax({
                // params
                url         : desturl,
                dataType    : 'html',
                cache       : false,
                success     : function (data) {

                    var obj  = $( data),
                        elem = obj.find( opts.itemSelector ),
                        next = obj.find( opts.nextSelector ),
                        current_url = desturl;

                    if( next.length ) {
                        desturl = next.attr( 'href' );
                    }
                    else {
                        // set finished var true
                        finished = true;
                        $( document ).trigger( 'yith-infs-scroll-finished' );
                    }

                    // recalculate element position in shop
                    if( ! last_elem.hasClass( 'last' ) && opts.is_shop ) {
                        position_elem( last_elem, columns, elem );
                    }

                    last_elem.after( elem );

                    $( '.yith-infs-loader' ).remove();

                    $(document).trigger( 'yith_infs_adding_elem', [elem, current_url] );

                    elem.addClass( 'yith-infs-animated' );

                    setTimeout( function(){
                        loading = false;
                        // remove animation class
                        elem.removeClass( 'yith-infs-animated' );
                        
                        $(document).trigger( 'yith_infs_added_elem', [elem, current_url] );
                        
                    }, 1000 );

                }
            });
        };

        // recalculate element position
        var position_elem = function( last, columns, elem ) {


            var offset  = ( columns - last.prevUntil( '.last', opts.itemSelector ).length ),
                loop    = 0;

            elem.each(function () {

                var t = $(this);
                loop++;

                t.removeClass('first');
                t.removeClass('last');

                if ( ( ( loop - offset ) % columns ) === 0 ) {
                    t.addClass('first');
                }
                else if ( ( ( loop - ( offset - 1 ) ) % columns ) === 0 ) {
                    t.addClass('last');
                }
            });
        };

        // set event
        $( window ).on( 'scroll touchstart', function (){
            $(this).trigger('yith_infs_start');
        });

        $( window ).on( 'yith_infs_start', function(){
            var t       = $(this),
                elem  = $( opts.itemSelector ).last();

            if( typeof elem == 'undefined' ) {
                return;
            }

            if ( ! loading && ! finished && ( t.scrollTop() + t.height() ) >= ( elem.offset().top - ( 2 * elem.height() ) ) ) {
                main_ajax();
            }
        })
    }

})( jQuery, window, document );