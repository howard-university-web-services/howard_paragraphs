<?php

namespace Drupal\hp_deadlines_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxies Dates & Deadlines JSON:API requests through Drupal.
 *
 * The frontend widget can't call deadlines.howard.edu directly from the
 * browser: stg requires basic auth and currently has an expired/mismatched
 * SSL cert (neither of which JS can work around), and there's no prod site
 * yet to fall back to. Drupal makes the request server-side instead, where
 * we can bypass those staging-only issues, and hands back plain JSON.
 */
class DeadlinesProxyController extends ControllerBase {

  /**
   * Proxies a request to the configured Deadlines environment.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function proxy(Request $request) {
    $env = $request->query->get('env', 'prod');

    // TODO: remove the stg/dev auth + verify overrides once
    // deadlines.howard.edu has a real prod site with a valid cert.
    $host = 'https://deadlines.howard.edu';
    $options = [
      'timeout' => 30,
      'connect_timeout' => 10,
      'headers' => [
        'Accept' => 'application/json',
        'User-Agent' => 'Howard Paragraphs Module/1.0',
      ],
    ];
    if ($env === 'stg') {
      $host = 'https://stg.deadlines.howard.edu';
      $options['auth'] = ['huweb', 'huweb'];
      $options['verify'] = FALSE;
    }
    elseif ($env === 'dev') {
      $host = 'https://dev.deadlines.howard.edu';
      $options['auth'] = ['huweb', 'huweb'];
      $options['verify'] = FALSE;
    }

    // Forward every query param except our own "env" through to the
    // upstream JSON:API request (filters, sort, page, include, etc.).
    $query = $request->query->all();
    unset($query['env']);
    $options['query'] = $query;

    // Short server-side cache, keyed by the full outgoing request, so
    // repeat page views/widgets don't re-hit the upstream site on every
    // load. Deadlines data doesn't need to be second-fresh.
    $cid = 'hp_deadlines_feed_proxy_' . md5($host . serialize($query));
    if ($cache = \Drupal::cache()->get($cid)) {
      return new Response($cache->data, 200, $this->responseHeaders());
    }

    $client = new Client();
    try {
      $response = $client->get($host . '/jsonapi/node/hc_deadline', $options);
      $body = $response->getBody()->getContents();
      \Drupal::cache()->set($cid, $body, time() + 300);
      return new Response($body, 200, $this->responseHeaders());
    }
    catch (\Throwable $e) {
      \Drupal::logger('hp_deadlines_feed')->error('Deadlines proxy request failed: @message', ['@message' => $e->getMessage()]);
      return new JsonResponse(['data' => []], 502, $this->responseHeaders());
    }
  }

  /**
   * Headers that keep this dynamic, per-filter response out of any
   * Varnish/CDN edge cache (Drupal's internal "no_cache" route option only
   * covers Drupal's own render cache, not edge caching).
   *
   * @return string[]
   */
  protected function responseHeaders() {
    return [
      'Content-Type' => 'application/vnd.api+json',
      'Cache-Control' => 'no-store, must-revalidate, max-age=0',
    ];
  }

}
