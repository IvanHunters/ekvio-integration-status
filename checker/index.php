<?php

declare(strict_types=1);

use App\APIClients;
use App\EventsStatus;
use App\KPIStatus;
use App\LearningProgramStatus;
use App\MaterialsStatus;
use App\PersonalsStatus;
use App\RedisProcessing;
use App\TasksStatus;
use App\TrainingsStatus;
use App\UsersStatus;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\EqueoClient;
use Ekvio\Integration\Sdk\V2\Integration\HttpIntegrationResult;
use Ekvio\Integration\Skeleton\Adapter;
use Ekvio\Integration\Skeleton\EnvironmentConfiguration;
use Ekvio\Integration\Skeleton\Invoker\Composite;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

$appRoot = __DIR__;
$redisPassword = getenv('REDIS_PASSWORD');
$titles = explode(",", getenv('INTEGRATION_ENV_TITLES'));
$hosts = explode(",", getenv('INTEGRATION_HOSTS'));
$tokens = explode(",", getenv('INTEGRATION_TOKENS'));

$config = array_merge_recursive(EnvironmentConfiguration::create(), [
    'services' => [
        Client::class => function() {
            return new Client([
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'http_errors' => false,
                'debug' => (bool)getenv('HTTP_CLIENT_DEBUG'),
                'verify' => false
            ]);
        },
        APIClients::class => function(ContainerInterface $dic) use ($titles, $hosts, $tokens){
            $apiClients = new APIClients();
            foreach ($titles as $id => $title) {
                $client = new EqueoClient(
                    $dic->get(Client::class),
                    new HttpIntegrationResult(),
                    $hosts[$id],
                    $tokens[$id],
                    [
                    'request_interval_timeout' => getenv('INTEGRATION_UPDATE_STATUS_RATE_TIME'),
                    'debug' => getenv('APPLICATION_DEBUG'),
                    'debug_request_body' => getenv('APPLICATION_DEBUG_REQUEST_BODY')
                    ]
                );

                $apiClients->addClient($title, $client);
            }
            return $apiClients;
        },
        RedisProcessing::class => function () use ($redisPassword) {
            $redis = new Redis();
            $redis->connect(
                'redis',
                6379
            );
            $redis->auth($redisPassword);
            return new RedisProcessing($redis);
        },
        UsersStatus::class => function(ContainerInterface $dic) {
            return new UsersStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        LearningProgramStatus::class => function(ContainerInterface $dic) {
            return new LearningProgramStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        MaterialsStatus::class => function(ContainerInterface $dic) {
            return new MaterialsStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        TrainingsStatus::class => function(ContainerInterface $dic) {
            return new TrainingsStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        TasksStatus::class => function(ContainerInterface $dic) {
            return new TasksStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        PersonalsStatus::class => function(ContainerInterface $dic) {
            return new PersonalsStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        KPIStatus::class => function(ContainerInterface $dic) {
            return new KPIStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
        EventsStatus::class => function(ContainerInterface $dic) {
            return new EventsStatus(
                $dic->get(APIClients::class),
                $dic->get(Profiler::class)
            );
        },
    ]
]);

(new Adapter($config))->run(Composite::class, [
    UsersStatus::class => [],
    MaterialsStatus::class => [],
    TrainingsStatus::class => [],
    EventsStatus::class => [],
    LearningProgramStatus::class => [],
    TasksStatus::class => [],
    PersonalsStatus::class => [],
    KPIStatus::class => [],
    RedisProcessing::class
]);