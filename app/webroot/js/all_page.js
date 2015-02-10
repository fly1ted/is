(function($) {
    var $window = $(window),
        $header = $('.header > nav'),
        $up_button = $('#up-button');
        
        function resize() {
            if ($window.width() < 768) {
                return $header.removeClass('navbar-fixed-top');
            }
            
            $header.addClass('navbar-fixed-top');
        }
        
        $window
            .resize(resize)
            .trigger('resize');
        
        $window.scroll(function() {
            if ($(this).scrollTop() > 220) {
                $up_button.fadeIn('fast');
            } else {
                $up_button.fadeOut('fast');
            }
        })
        
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
        
        $up_button.click(scrollUp);
})(jQuery);

function scrollUp() {
	$("html, body").animate({scrollTop: 0}, 250); 
	return false;
}