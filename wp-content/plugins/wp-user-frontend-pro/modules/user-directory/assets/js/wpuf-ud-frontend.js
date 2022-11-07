;(function($) {
    $('a#btn-view-more').on('click', function(e) {
        e.preventDefault();
        $(this).text( $(this).text() === "View More" ? "Show Less" : "View More" );
        $(this).closest('.biography').find('span.desc-part-two').slideToggle();
    });

    $('#btn-reset-search').on('click', function(e) {
        e.preventDefault();
        let url = new URL(window.location);
        url = url.href;
        url = getPathFromUrl(url);
        window.history.pushState({}, '', url);
        location.reload();
    });

    function getPathFromUrl(url) {
        return url.split(/[?#]/)[0];
    }
}(jQuery));
