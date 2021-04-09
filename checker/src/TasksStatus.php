<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\Task\Task;

class TasksStatus extends MainStatus implements Invoker
{

    private array $tasks;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->tasks[$clientName] = new Task($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check task statistics...");
        $this->tasksStatistics();

        $statusData = $arguments['prev'] ?? [];
        $statusData['tasks'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function tasksStatistics(): void
    {
        $methodName = 'statistics';

        foreach ($this->tasks as $envName => $tasksData) {
            $this->check($methodName, $envName, function () use ($tasksData) {
                $tasksData->statistic([]);
            });
        }
    }

    public function name(): string
    {
        return 'Check tasks methods...';
    }
}