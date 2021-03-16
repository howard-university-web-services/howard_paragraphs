$(document).ready(function() {
    $('#slideshow').cycle({
        fx: 'fade',
        prevNextEvent: 'ended',
        next: '.video-container video',
        timeout: 0,
        pager:  '#slideshow-nav',
        before: function() {
            this.style.visibility = "visible";
            this.firstElementChild.play();
        }
    });
});