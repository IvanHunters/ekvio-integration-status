<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\Personal\Personal;

class PersonalsStatus extends MainStatus implements Invoker
{

    private array $personals;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->personals[$clientName] = new Personal($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check personal update statuses...");
        $this->personalUpdateStatuses();

        $statusData = $arguments['prev'] ?? [];
        $statusData['personals'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function personalUpdateStatuses(): void
    {
        $methodName = 'updateStatuses';

        foreach ($this->personals as $envName => $personalsData) {
            $this->check($methodName, $envName, function () use ($personalsData) {
                $personalsData->updateStatuses([]);
            });
        }
    }

    public function name(): string
    {
        return 'Check personals methods...';
    }
}