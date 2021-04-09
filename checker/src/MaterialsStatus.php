<?php

declare(strict_types=1);

namespace App;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Sdk\V2\Material\Material;

class MaterialsStatus extends MainStatus implements Invoker
{

    private array $materials;

    public function __construct(APIClients $client, Profiler $profiler)
    {
        parent::__construct($client, $profiler);
        foreach ($client->clients as $clientName => $clientData) {
            $this->materials[$clientName] = new Material($clientData);
        }
    }

    public function __invoke(array $arguments = [])
    {
        $this->profiler->profile("Check material statistics...");
        $this->materialStatistics();

        $statusData = $arguments['prev'] ?? [];
        $statusData['materials'] = [
            'statusMethods' => $this->statusMethods,
            'exceptionMethods' => $this->exceptionMethods,
        ];

        return $statusData;
    }

    private function materialStatistics(): void
    {
        $methodName = 'statistics';

        foreach ($this->materials as $envName => $materialData) {
            $this->check($methodName, $envName, function () use ($materialData) {
                $materialData->statistic([]);
            });
        }
    }

    public function name(): string
    {
        return 'Check materials methods...';
    }
}