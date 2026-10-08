<?php

/**
 * @file
 * Focused regression checks using an existing Drupal project's dependencies.
 *
 * Usage: php tests/run.php /absolute/path/to/drupal/vendor/autoload.php
 * Does not boot or change that Drupal installation or require a database.
 */

use Drupal\Component\Datetime\Time;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\hp_events_feed\EventNormalizer;
use Drupal\hp_events_feed\EventsQuery;
use Drupal\hp_events_feed\EventSubscriber\FeedResponseSubscriber;
use Drupal\hp_events_feed\Plugin\Field\FieldWidget\EventsFiltersWidget;
use Drupal\hp_events_feed\Service\LocalistClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;

if (empty($argv[1]) || !is_file($argv[1])) {
  fwrite(STDERR, "Supply a Drupal project's vendor/autoload.php path.\n");
  exit(1);
}
$loader = require $argv[1];
$module = dirname(__DIR__);
$root = dirname(dirname($argv[1])) . '/docroot';
if (!is_dir($root . '/core')) {
  $root = dirname(dirname($argv[1])) . '/web';
}
$loader->addPsr4('Drupal\\Core\\', $root . '/core/lib/Drupal/Core');
$loader->addPsr4('Drupal\\Component\\', $root . '/core/lib/Drupal/Component');
$loader->addPsr4('Drupal\\hp_events_feed\\', $module . '/src');

$checks = 0;
function check(bool $condition, string $label): void {
  global $checks;
  if (!$condition) {
    throw new RuntimeException('FAILED: ' . $label);
  }
  $checks++;
}
function rejects(callable $callback, string $label): void {
  try {
    $callback();
  }
  catch (InvalidArgumentException | JsonException $exception) {
    check(TRUE, $label);
    return;
  }
  check(FALSE, $label);
}

// Query construction: limits, exclusion, multiple IDs, false, and unknown keys.
$query = EventsQuery::build(EventsQuery::defaults(), 6, 1, '2026-10-08');
check($query['start'] === '2026-10-08' && $query['days'] === 30, 'Rolling date window');
check($query['publish_status'] === 'published', 'Public publication status');
$query = EventsQuery::build(['type' => ['43784211670150', '43784241431085'], 'exclude_type' => ['43658612866313'], 'keyword' => ['Arts & Culture', '0'], 'recurring' => FALSE, 'distinct' => FALSE, 'access_token' => 'never', 'publish_status' => 'all', 'url' => 'http://localhost'], 500, -2, '2026-10-08');
check($query['pp'] === 100 && $query['page'] === 1, 'Paging clamped');
check($query['recurring'] === 'false' && $query['distinct'] === 'false', 'False flags preserved');
check(!isset($query['access_token'], $query['url']) && $query['publish_status'] === 'published', 'Unknown/private parameters discarded');
$encoded = EventsQuery::encode($query);
check(str_contains($encoded, 'type%5B%5D=43784211670150&type%5B%5D=43784241431085') && !str_contains($encoded, '%5B0%5D'), 'Repeated array parameters');
check(str_contains($encoded, 'keyword%5B%5D=Arts%20%26%20Culture'), 'Query values encoded');
check(EventsQuery::build(['start' => '2026-10-08', 'end' => '2026-10-09'])['end'] === '2026-10-09', 'Fixed exclusive range');
check(EventsQuery::build(['start' => '2026-01-01', 'days' => 370])['days'] === 370, 'Maximum date window');
foreach ([['days'=>371], ['days'=>0], ['start'=>'2026-02-30'], ['start'=>'2026-10-08','end'=>'2026-10-08'], ['start'=>'2026-10-08','end'=>'2026-10-09','days'=>30], ['sort'=>'invalid'], ['group_id'=>'id=1'], ['experience'=>'invalid'], ['type'=>'12'], ['recurring'=>'invalid']] as $invalid) {
  rejects(static fn() => EventsQuery::build($invalid), 'Invalid filter combination rejected');
}
$retired = ['match'=>'or','for'=>'channel','near'=>'38.9227,-77.0194','within'=>1.5,'units'=>'km','bounds'=>'38,-78,40,-76','sort'=>'distance'];
$retiredQuery = EventsQuery::build($retired);
check($retiredQuery['match'] === 'all' && $retiredQuery['for'] === 'widget', 'Matching and context remain fixed despite saved retired controls');
check(!array_intersect(['near','within','units','bounds'], array_keys($retiredQuery)), 'Retired geographic filters ignored');
check($retiredQuery['sort'] === 'date', 'Retired distance sorting falls back to event date');
rejects(static fn() => EventsQuery::decode('{bad'), 'Damaged JSON rejected');
rejects(static fn() => EventsQuery::decode('[1]'), 'Non-object filters rejected');

$submitted = EventsFiltersWidget::submittedFilters(['filters'=>['dates'=>['days'=>10], 'taxonomy'=>['type'=>['43784211670150'],'keyword'=>" Science\r\n0\n\n"], 'refinements'=>['recurring'=>'0','distinct'=>0]]]);
check($submitted['keyword'] === ['Science','0'], 'Widget keyword lines preserved');
check($submitted['recurring'] === FALSE && $submitted['distinct'] === FALSE, 'Widget false selections preserved');
check(EventsFiltersWidget::submittedFilters(['filters'=>['taxonomy'=>['type'=>['43784211670150'=>'43784211670150']]]])['type'] === ['43784211670150'], 'Form API keyed selections stored as a JSON list');
check(!isset(EventsFiltersWidget::submittedFilters(['filters'=>['refinements'=>['recurring'=>'']]])['recurring']), 'Any recurrence omits filter');
$retiredSubmission = EventsFiltersWidget::submittedFilters(['filters'=>['taxonomy'=>['match'=>'any'],'refinements'=>['for'=>'main','sort'=>'distance'],'geography'=>['near'=>'38,-77','within'=>1,'units'=>'km','bounds'=>'38,-78,40,-76']]]);
check(!array_intersect(['match','for','near','within','units','bounds'], array_keys($retiredSubmission)) && $retiredSubmission['sort'] === 'date', 'Retired form controls discarded on save');

// Safe output and first relevant occurrence of a repeating event.
$event = [
  'id'=>123, 'title'=>'Research <b>&amp; Arts</b>', 'localist_url'=>'https://events.howard.edu/event/research',
  'description'=>'<p>Hello</p><script>alert(1)</script>', 'photo_url'=>'javascript:alert(1)',
  'ticket_url'=>'https://example.com/tickets?a=1&amp;b=2', 'experience'=>'virtual',
  'event_instances'=>[
    ['event_instance'=>['start'=>'2026-09-01T10:00:00-04:00']],
    ['event_instance'=>['start'=>'2026-10-09T14:00:00Z','end'=>'2026-10-09T15:00:00Z','all_day'=>FALSE]],
    ['event_instance'=>['start'=>'2026-10-08T18:00:00-04:00']],
  ],
];
$range = EventsQuery::build(['start'=>'2026-10-08','end'=>'2026-10-10']);
$normalized = EventNormalizer::normalize($event, $range);
check($normalized['start'] === '2026-10-08T18:00:00-04:00', 'First matching occurrence, not past occurrence');
check($normalized['title'] === 'Research & Arts' && $normalized['image'] === '', 'Plain text and unsafe URL removal');
check($normalized['ticket_url'] === 'https://example.com/tickets?a=1&b=2', 'Entity-encoded URL normalized');
check(EventNormalizer::normalize($event + ['private'=>TRUE], $range) === NULL, 'Private events omitted');
check(EventNormalizer::normalize($event + ['publish_status'=>'pending'], $range) === NULL, 'Unpublished events omitted');
check(EventNormalizer::normalize($event, EventsQuery::build(['start'=>'2026-10-08','end'=>'2026-10-09']))['date'] === 'October 08, 2026', 'Exclusive end date');
$utc = $event;
$utc['event_instances'] = [['event_instance'=>['start'=>'2026-10-09T14:00:00Z']]];
check(EventNormalizer::normalize($utc, $range)['time'] === '10:00 am EDT', 'Calendar timezone conversion');
$late = $utc;
$late['event_instances'][0]['event_instance']['start'] = '2026-10-09T00:30:00Z';
check(EventNormalizer::normalize($late, EventsQuery::build(['start'=>'2026-10-09','end'=>'2026-10-10'])) === NULL, 'Date filtering uses calendar timezone across UTC midnight');
$utc['event_instances'][0]['event_instance']['all_day'] = TRUE;
check(EventNormalizer::normalize($utc, $range)['time'] === '', 'All-day time omitted');
check(EventNormalizer::url('data:text/html,evil') === '' && EventNormalizer::url('https://user:pass@example.com') === '', 'Unsafe URL schemes and credentials rejected');

// Service integration with real Guzzle mock transport and Drupal memory cache.
$clock = new class extends Time {
  public int $now;
  public function __construct() { $this->now = time(); }
  public function getCurrentTime() { return $this->now; }
  public function getRequestTime() { return $this->now; }
};
$factory = new class extends ConfigFactory {
  public string $token = 'test-secret';
  public function __construct() {}
  public function get($name) {
    return new class($this->token) extends ImmutableConfig {
      public function __construct(private string $token) {}
      public function get($key = '') { return $key === 'access_token' ? $this->token : NULL; }
    };
  }
};
$history = [];
$today = (new DateTimeImmutable('now', new DateTimeZone('America/New_York')))->format('Y-m-d');
$fixture = $event;
$fixture['event_instances'] = [['event_instance'=>['start'=>$today.'T12:00:00-04:00']]];
$jsonResponse = static fn(array $data): Response => new Response(200, ['Content-Type'=>'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
$mock = new MockHandler([
  $jsonResponse(['events'=>[['event'=>$fixture]],'page'=>['current'=>1,'total'=>3]]),
  $jsonResponse(['events'=>[],'page'=>['current'=>2,'total'=>3]]),
  new Response(503, [], 'Unavailable'),
  $jsonResponse(['events'=>[],'page'=>['current'=>1,'total'=>1]]),
  new Response(200, [], '{invalid'),
]);
$stack = HandlerStack::create($mock);
$stack->push(Middleware::history($history));
$cache = new MemoryBackend($clock);
$client = new LocalistClient(new Client(['handler'=>$stack]), $cache, $factory, new LoggerChannelFactory(new RequestStack(), new AnonymousUserSession()), $clock);
$feed = $client->getFeed(['days'=>30], 6, 1, ['paragraph:12']);
check(!$feed['error'] && count($feed['events']) === 1 && $feed['page']['total'] === 3, 'Feed normalization and pagination');
check($history[0]['request']->getHeaderLine('Authorization') === 'Bearer test-secret', 'Token stays in server header');
check(!str_contains((string) $history[0]['request']->getUri(), 'test-secret'), 'No token in query URL');
check($history[0]['options']['allow_redirects'] === FALSE, 'Redirects disabled');
$clock->now += 120;
check($client->getFeed(['days'=>30], 6, 1, ['paragraph:12'])['max_age'] === 180 && count($history) === 1, 'Cache hit uses remaining TTL');
check($client->getFeed(['days'=>30], 6, 2, ['paragraph:12'])['events'] === [] && count($history) === 2, 'Different page cache key');
$cache->invalidateTags(['paragraph:12']);
check($client->getFeed(['days'=>30], 6, 1, ['paragraph:12'])['error'], 'Manual paragraph invalidation reloads API');
check($client->getFeed(['days'=>30], 6, 1, ['paragraph:12'])['error'] && count($history) === 3, 'Failures cached briefly');
$clock->now += 61;
check(!$client->getFeed(['days'=>30], 6, 1, ['paragraph:12'])['error'], 'Failure retry after expiration');
$factory->token = 'changed-secret';
check($client->getFeed(['days'=>30], 6, 1, ['paragraph:12'])['error'] && count($history) === 5, 'Token change separates cache; invalid JSON fails gracefully');

// Catalogue pagination, category labels, child labels, and department IDs.
$catalogueResponses = [
  $jsonResponse(['event_types'=>[['id'=>11,'name'=>'Parent'],['id'=>12,'name'=>'Child','parent_id'=>11]]]),
  $jsonResponse(['filters'=>['event_types'=>'Event Types']]),
  $jsonResponse(['places'=>[['place'=>['id'=>21,'name'=>'Hall A']]],'page'=>['total'=>2]]),
  $jsonResponse(['places'=>[['place'=>['id'=>22,'name'=>'Hall B']]],'page'=>['total'=>2]]),
  $jsonResponse(['groups'=>[['group'=>['id'=>31,'name'=>'Club']]],'page'=>['total'=>1]]),
  $jsonResponse(['departments'=>[['department'=>['id'=>41,'name'=>'College']]],'page'=>['total'=>1]]),
  $jsonResponse(['organizations'=>[['organization'=>['id'=>51,'name'=>'Howard']]],'page'=>['total'=>1]]),
  $jsonResponse(['communities'=>[],'page'=>['total'=>1]]),
];
$catalogueMock = new MockHandler($catalogueResponses);
$catalogueClient = new LocalistClient(new Client(['handler'=>HandlerStack::create($catalogueMock)]), new MemoryBackend($clock), $factory, new LoggerChannelFactory(new RequestStack(), new AnonymousUserSession()), $clock);
$catalogue = $catalogueClient->getCatalogue();
check($catalogue['types']['Event Types'][12] === 'Parent / Child', 'Live category labels and hierarchy');
check(count($catalogue['venues']) === 2 && isset($catalogue['groups']['Departments'][41]), 'All catalogue pages and departments');
check(!$catalogue['unavailable'] && $catalogueClient->getCatalogue() === $catalogue, 'Catalogue cache');

// Anonymous page-cache expiration must be explicit; preserve private responses.
$kernel = new class implements HttpKernelInterface {
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): \Symfony\Component\HttpFoundation\Response { return new \Symfony\Component\HttpFoundation\Response(); }
};
$subscriber = new FeedResponseSubscriber($clock);
$response = new CacheableResponse('feed');
$response->addCacheableDependency((new CacheableMetadata())->setCacheTags([LocalistClient::CACHE_TAG])->setCacheMaxAge(180));
$response->headers->set('Cache-Control', 'public, max-age=900, s-maxage=600');
$subscriber->onResponse(new ResponseEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $response));
check($response->getExpires()->getTimestamp() === $clock->now + 180 && $response->getMaxAge() === 180, 'Internal Page Cache expires');
check((int) $response->headers->getCacheControlDirective('s-maxage') === 180, 'Proxy cache lifetime capped');
$response->headers->set('X-Howard-Events-Expires', [(string) ($clock->now + 40), (string) ($clock->now + 90)]);
$subscriber->onResponse(new ResponseEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $response));
check($response->getExpires()->getTimestamp() === $clock->now + 40 && !$response->headers->has('X-Howard-Events-Expires'), 'Earliest absolute deadline prevents extending render cache hits and is removed');
$response->headers->set('Cache-Control','private, no-store');
$subscriber->onResponse(new ResponseEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $response));
check($response->headers->hasCacheControlDirective('private') && $response->headers->hasCacheControlDirective('no-store'), 'Private caching policy preserved');

// Parse all YAML and verify installed field/display references agree.
$files = glob($module.'/config/install/*.yml');
$configs = [];
foreach ($files as $file) {
  $configs[basename($file,'.yml')] = Yaml::parseFile($file);
}
foreach ($configs as $name=>$config) {
  foreach ($config['dependencies']['config'] ?? [] as $dependency) {
    check(isset($configs[$dependency]), 'Configuration dependency exists: '.$dependency);
  }
}
check($configs['core.entity_form_display.paragraph.hp_events_feed.default']['content']['field_hp_ef_filters']['type'] === 'hp_events_filters', 'Structured filter widget configured');
check($configs['core.entity_view_display.paragraph.hp_events_feed.default']['hidden']['field_hp_ef_filters'], 'Filter JSON never displayed');
EventsQuery::build(EventsQuery::decode($configs['field.field.paragraph.hp_events_feed.field_hp_ef_filters']['default_value'][0]['value']));

// Render the rows with Twig autoescaping and exercise success/empty/error states.
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader($module.'/templates'), ['autoescape'=>'html','strict_variables'=>TRUE]);
$twig->addFilter(new \Twig\TwigFilter('t', static fn($text, array $args=[]): string => strtr($text,$args)));
$variables = ['events'=>[$normalized],'error'=>FALSE,'layout'=>'cards','previous'=>NULL,'next'=>NULL,'page'=>1,'total_pages'=>1];
$variables['events'][0]['title'] = '<script>alert(1)</script>';
$html = $twig->render('hp-events-feed-events.html.twig',$variables);
check(str_contains($html,'&lt;script&gt;') && !str_contains($html,'<script>'), 'Twig escapes API text');
check(str_contains($html,'datetime="2026-10-08T18:00:00-04:00"') && !str_contains($html,'javascript:'), 'Semantic dates and safe links');
$withImage = $event;
$withImage['photo_url'] = 'https://example.com/event-photo.jpg';
$variables['events'] = [EventNormalizer::normalize($withImage, $range)];
foreach (['cards', 'list'] as $layout) {
  $variables['layout'] = $layout;
  $html = $twig->render('hp-events-feed-events.html.twig', $variables);
  check(str_contains($html, '<img src="https://example.com/event-photo.jpg" alt="" loading="lazy">'), 'API image rendered in ' . $layout . ' layout');
  $variables['events'][0]['image'] = '';
  check(!str_contains($twig->render('hp-events-feed-events.html.twig', $variables), '<img'), 'Missing image omitted in ' . $layout . ' layout');
  $variables['events'][0]['image'] = 'https://example.com/event-photo.jpg';
}
$textEvent = $withImage;
$textEvent['event_instances'] = [['event_instance'=>['start'=>'2026-10-08T09:00:00-04:00','end'=>'2026-10-08T12:00:00-04:00']]];
$variables['events'] = [EventNormalizer::normalize($textEvent, $range)];
$variables['layout'] = 'text_cards';
$html = $twig->render('hp-events-feed-events.html.twig', $variables);
check(!str_contains($html, '<img') && str_contains(strip_tags($html), '9:00am - 12:00pm'), 'Text cards hide available images and display the time range');
$textEvent['event_instances'][0]['event_instance']['end'] = NULL;
$variables['events'] = [EventNormalizer::normalize($textEvent, $range)];
check(!str_contains($twig->render('hp-events-feed-events.html.twig', $variables), '9:00am -'), 'Missing end time leaves no dangling range separator');
$textEvent['event_instances'][0]['event_instance']['all_day'] = TRUE;
$variables['events'] = [EventNormalizer::normalize($textEvent, $range)];
$html = $twig->render('hp-events-feed-events.html.twig', $variables);
check(str_contains($html, 'All day') && !str_contains($html, '9:00am'), 'Text-card all-day events omit clock times');
$variables['events']=[];
check(str_contains($twig->render('hp-events-feed-events.html.twig',$variables),'No upcoming events'), 'Empty feed state');
$variables['error']=TRUE;
check(str_contains($twig->render('hp-events-feed-events.html.twig',$variables),'temporarily unavailable'), 'API failure state');
echo "Passed $checks regression checks.\n";
