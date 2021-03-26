jQuery(document).ready(function($) {
    $('.hp_video_slideshow--slideshow').cycle({
        fx: 'none',
        timeout: 0,
        pager: '.hp_video_slideshow--slideshow-nav',
        before: function() {
            this.style.visibility = "visible";

            // Load the Vimeo player.
            let iframe = this.querySelector('iframe');
            let player = new Vimeo.Player(iframe);

            // When the video ends
            // 1) Reset the iframe.
            // 2) Remove the callback listener for the current intance.
            // 3) Go to the next video.
            player.on('ended', function(data) {
                player.unload().then(function() {});
                player.off('ended');
                $('.hp_video_slideshow--slideshow').cycle('next');
            });

            // Mute the video then play.
            player.setVolume(0);
            player.play();
        }
    });
});