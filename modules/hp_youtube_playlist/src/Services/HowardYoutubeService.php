<?php

namespace Drupal\hp_youtube_playlist\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HowardProfilesService.
 */
class HowardYoutubeService {

  /**
   * @var GuzzleHttp\Client
   */
  protected $client;
  public $playlistItemsEndpoint;
  public $playlistEndpoint;
  public $api_key;

  /**
   * Constructs a new HowardProfilesService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->playlistItemsEndpoint = 'https://youtube.googleapis.com/youtube/v3/playlistItems';
    $this->playlistEndpoint = 'https://youtube.googleapis.com/youtube/v3/playlists';
    $settings = \Drupal::config('hp_youtube_playlist.settings');
    $this->api_key = $settings->get('api_key');
  }

  /**
   * Public method to return Playlist Item Info.
   */
  public function getPlaylist( $id = 'default') {
    $url = $this->playlistEndpoint . '?id=' . $id;
    $url .= '&key=' . $this->api_key;
    $url .= '&part=snippet,contentDetails';
    $json = $this->getData($id . '-playlist', $url);
    return $json[0];
  }

  /**
   * Public method to return Playlist Item Info.
   */
  public function getPlaylistItems( $id = 'default') {
    $url = $this->playlistItemsEndpoint . '?playlistId=' . $id;
    $url .= '&key=' . $this->api_key;
    $url .= '&part=snippet,contentDetails';
    $json = $this->getData($id . '-playlist-items', $url);
    $truncated_json = array_slice($json, 0, 3);
    return $truncated_json;
  }

  /**
   * Public method to get content from Howard Profiles API.
   */
  public function getData($cache_id, $url) {
   // if ($cache = \Drupal::cache()->get($cache_id)) {
   //   return $cache->data;
   // } else {
      try {
        $request = $this->client->get($url, ['verify' => FALSE]);
        $result = json_decode($request->getBody()->__toString(), TRUE);
      }
      catch (RequestException $e) {
        $message = 'Error connecting to YouTube API via URL:' . $url;
        \Drupal::logger('Howard Youtube API')->error($message);
        return;
      }
      if ($result['items']) {
       // \Drupal::cache()->set($cache_id, $result, time() + 7200);
        return $result['items'];
      }
      else {
        return;
      }
   // }

  }

}
