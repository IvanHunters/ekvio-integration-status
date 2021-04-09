<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\Event\EventApi;
use Ekvio\Integration\Sdk\V2\Event\EventSearchCriteria;
use Ekvio\Integration\Sdk\V2\Event\EventStatisticCriteria;

class EventsStatus extends MainStatus implements Invoker
{

    private array $events;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->events[$clientName] = new EventApi($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check event search...");
        $this->eventsSearch();
        $this->profiler->profile("Check event statistics...");
        $this->eventsStatistics();

        $statusData = $arguments['prev'] ?? [];
        $statusData['events'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function eventsSearch(): void
    {
        $methodName = 'search';
        foreach ($this->events as $envName => $event) {
            $this->check($methodName, $envName, function () use ($event) {
                $criteria = EventSearchCriteria::build();
                $event->search($criteria);
            });
        }
    }

    private function eventsStatistics(): void
    {
        $methodName = 'statistics';
        foreach ($this->events as $envName => $event) {
            $this->check($methodName, $envName, function () use ($event) {
                $criteria = EventStatisticCriteria::build();
                $event->statistic($criteria);
            });
        }
    }

    public function name(): string
    {
        return 'Check trainings methods...';
    }
}