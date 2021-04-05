var videoPlayers = {};

jQuery(document).ready(function($)
{
    // Initialize each video player.
    $('.hp_video_slideshow--video-container').each(function()
    {
        // Load the Vimeo player.
        let iframe = $(this).find('iframe');
        let player = new Vimeo.Player(iframe);

        // When the video plays:
        // 1) Hide player until the video starts playing.
        // 2) Show caption text if applicable.
        let caption = $(this).find('.hp_video_slideshow--video-text');
        player.on('play', function(data)
        {
            setTimeout(function()
            {
                iframe.fadeIn(1000);

                if (caption != null)
                {
                    caption.fadeIn(2500);
                    caption.fadeOut(2500);
                }
            },

            // Account for flash right
            // before the video plays.
            150);
        });

        // Get the slide show reference.
        let slideshow = $(this).parent();

        // When the video ends:
        // 1) Go to the next video.
        // 2) Hide the video player for fade effect.
        player.on('ended', function(data)
        {
            slideshow.cycle('next');
            iframe.fadeOut();
        });

        // Mute the video.
        player.setVolume(0);

        // Store the player instance in the video players
        // dictionary to avoid reinstantiation during transitions
        // and improve performance.
        let videoPlayerId = Math.random().toString();
        $(this).attr('videoPlayerId', videoPlayerId);
        videoPlayers[videoPlayerId] = player;
    });

    // Start slideshow.
    $('.hp_video_slideshow--slideshow').cycle({
        fx: 'none',
        timeout: 0,
        pager: '.hp_video_slideshow--slideshow-nav',
        before: function()
        {
            // Ensure the current video container is visible.
            $(this).css('visibility', 'visible');

            // Load the Vimeo player.
            let iframe = $(this).find('iframe');
            let player = videoPlayers[$(this).attr('videoPlayerId')];

            // Play the video.
            player.play().catch(function(error)
            {
                switch (error.name)
                {
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
        },
        onPagerEvent: function(zeroBasedSlideIndex, slideElement)
        {
            let currentVideoPlayerId = $(slideElement).attr('videoPlayerId');
            let iframe = $(slideElement).find('iframe');
            let caption = $(slideElement).find('.hp_video_slideshow--video-text');

            // Reset all slides except the current one.
            for (let videoPlayerId in videoPlayers)
            {
                if (videoPlayerId != currentVideoPlayerId)
                {
                    videoPlayers[videoPlayerId].unload();
                    iframe.hide();

                    if (caption != null)
                    {
                        caption.hide();
                    }
                }
            }
        }
    });
});
