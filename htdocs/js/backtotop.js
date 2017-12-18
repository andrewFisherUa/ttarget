// http://davidwalsh.name/jquery-top-link
jQuery.fn.topLink = function (settings) {
    settings = jQuery.extend({
        min: 1,
        fadeSpeed: 200
    }, settings);
    return this.each(function () {
        //listen for scroll
        var el = $(this);
        el.hide(); //in case the user forgot
        $(window).scroll(function () {
            if ($(window).scrollTop() >= settings.min) {
                el.fadeIn(settings.fadeSpeed);
            }
            else {
                el.fadeOut(settings.fadeSpeed);
            }
        });
    });
};

//usage
$(document).ready(function () {
    //set the link
    $('#top-link').topLink({
        min: 400,
        fadeSpeed: 500
    }).click(function(){
    	$(window).scrollTop(0);
    	return false;
    });
});
