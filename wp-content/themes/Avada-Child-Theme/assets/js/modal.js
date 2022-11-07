(function($){

    $("[open-modal]").on("click", function(e){
        e.preventDefault();
        let modal = $(this).attr('open-modal');
        $('[modal="'+modal+'"]').modal({
            fadeDuration: 100
        });
    })

})(jQuery); 