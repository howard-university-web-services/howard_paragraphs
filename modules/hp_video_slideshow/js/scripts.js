jQuery(document).ready(function($) {
    $('.hp_video_slideshow--slideshow').cycle({
        fx: 'none',
        timeout: 0,
        pager: '.hp_video_slideshow--slideshow-nav',
        before: function() {
            $(this).css('visibility', 'visible');

            // Load the Vimeo player.
            let iframe = $(this).find('iframe');
            let player = new Vimeo.Player(iframe);

            // When the video loads, show caption text if applicable.
            let caption = $(this).find('.hp_video_slideshow--video-text');
            player.on('play', function(data) {
                if (caption != null)
                {
                    caption.fadeIn(3000);
                }
            });

            // Hide until the video loads.
            player.on('loaded', function(data) {
                iframe.fadeIn();
            });

            // Get the slide show reference.
            let slideshow = $(this).parent();

            // When the video ends:
            // 1) Reset the video.
            // 2) Remove the callback listener for the current intance.
            // 3) Hide caption if applicable.
            // 4) Go to the next video.
            player.on('ended', function(data) {
                player.unload().then(function() {});
                player.off('ended');

                if (caption != null)
                {
                    caption.fadeOut();
                }

                slideshow.cycle('next');
            });

            // Mute the video then play.
            player.setVolume(0);
            player.play().catch(function(error) {
                switch (error.name) {
                    case 'PasswordError':
                        console.log('The video is password-protected.');
                        break;

                    case 'PrivacyError':
                        console.log('The video is private');
                        break;

                    // Hide the iframe and show the fallback image.
                    console.log('Hiding video.');
                    iframe.fadeOut();
                }
            });
        }
    });
});
